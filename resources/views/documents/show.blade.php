<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Dokumen</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<div class="max-w-2xl mx-auto py-10">
    <h1 class="text-xl font-bold mb-2">{{ $document->original_name }}</h1>
    <p class="text-sm text-gray-500 mb-4">Tipe: {{ $document->type }}</p>

    @if ($document->type === 'image')
        <img src="{{ asset('storage/' . $document->file_path) }}" class="max-w-full mb-4 border">
    @else
        <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="text-blue-600 underline mb-4 block">
            Lihat PDF Asli
        </a>
    @endif

    <h2 class="font-semibold mb-2">Hasil Teks:</h2>
    <pre class="bg-gray-100 p-4 rounded whitespace-pre-wrap">{{ $document->extracted_text }}</pre>
</div>
</body>
</html>