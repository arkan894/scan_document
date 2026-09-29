<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::latest()->get();
        return view('documents.index', compact('documents'));
    }

    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->store('documents', 'public');
        $fullPath = storage_path('app/public/' . $path);

        $type = $extension === 'pdf' ? 'pdf' : 'image';
        $text = $type === 'pdf'
            ? $this->extractFromPdf($fullPath)
            : $this->extractFromImageOcrSpace($fullPath);

        $document = Document::create([
            'original_name'  => $file->getClientOriginalName(),
            'file_path'      => $path,
            'type'           => $type,
            'extracted_text' => $text,
        ]);

        return redirect()
            ->route('documents.show', $document->id)
            ->with('success', 'Dokumen berhasil di-scan.');
    }

    public function show(Document $document)
    {
        return view('documents.show', compact('document'));
    }

    protected function extractFromPdf(string $path): string
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();
            return trim($text) !== '' ? $text : '(Teks tidak terbaca, kemungkinan PDF hasil scan/gambar)';
        } catch (\Throwable $e) {
            return 'Gagal membaca PDF: ' . $e->getMessage();
        }
    }

    /**
     * OCR menggunakan OCR.space API (cloud, tidak perlu install Tesseract lokal)
     */
    protected function extractFromImageOcrSpace(string $path): string
    {
        try {
            $processedPath = $this->preprocessImage($path);

            $response = Http::asMultipart()->post('https://api.ocr.space/parse/image', [
                [
                    'name'     => 'file',
                    'contents' => fopen($processedPath, 'r'),
                    'filename' => basename($processedPath),
                ],
                [
                    'name'     => 'apikey',
                    'contents' => env('OCRSPACE_API_KEY'),
                ],
                [
                    'name'     => 'language',
                    'contents' => 'eng',
                ],
                [
                    'name'     => 'OCREngine',
                    'contents' => '2',
                ],
                [
                    'name'     => 'scale',
                    'contents' => 'true',
                ],
                [
                    'name'     => 'isTable',
                    'contents' => 'false',
                ],
            ]);

            if (file_exists($processedPath)) {
                unlink($processedPath);
            }

            $result = $response->json();

            if (isset($result['ParsedResults'][0]['ParsedText'])) {
                return trim($result['ParsedResults'][0]['ParsedText']);
            }

            return 'Gagal OCR: ' . ($result['ErrorMessage'][0] ?? 'Tidak ada teks terdeteksi');
        } catch (\Throwable $e) {
            return 'Gagal melakukan OCR via OCR.space: ' . $e->getMessage();
        }
    }

    /**
     * OCR menggunakan Tesseract lokal (disimpan sebagai alternatif/fallback)
     */
    protected function extractFromImage(string $path): string
    {
        try {
            $processedPath = $this->preprocessImage($path);

            $text = (new TesseractOCR($processedPath))
                ->executable('C:\Program Files\Tesseract-OCR\tesseract.exe')
                ->lang('ind', 'eng')
                ->psm(6)
                ->run();

            if (file_exists($processedPath)) {
                unlink($processedPath);
            }

            return $text;
        } catch (\Throwable $e) {
            return 'Gagal melakukan OCR: ' . $e->getMessage();
        }
    }

    /**
     * Preprocessing gambar supaya hasil OCR lebih akurat:
     * grayscale, naikkan kontras, perbesar resolusi, sharpen
     */
    protected function preprocessImage(string $path): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($path);

        if ($image->width() < 1500) {
            $ratio = 1500 / $image->width();
            $image->resize(1500, (int) ($image->height() * $ratio));
        }

        $image->greyscale();
        $image->contrast(15);
        $image->sharpen(10);

        $tempDir = storage_path('app/temp_ocr');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $processedPath = $tempDir . '/' . Str::random(10) . '_processed.jpg';
        $image->save($processedPath, quality: 95);

        return $processedPath;
    }
}