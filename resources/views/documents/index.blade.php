<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Dokumen</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<div class="max-w-2xl mx-auto py-10">
    <div class="flex justify-between mb-4">
        <h1 class="text-xl font-bold">Daftar Dokumen</h1>
        <a href="{{ route('documents.create') }}" class="bg-blue-600 text-white px-3 py-1 rounded">+ Scan Baru</a>
    </div>

    @if (session('success'))
        <div class="text-green-600 mb-4">{{ session('success') }}</div>
    @endif

    <ul class="divide-y">
        @foreach ($documents as $doc)
            <li class="py-2">
                <a href="{{ route('documents.show', $doc->id) }}" class="text-blue-600 underline">
                    {{ $doc->original_name }} ({{ $doc->type }})
                </a>
            </li>
        @endforeach
    </ul>
</div>
</body>
</html>