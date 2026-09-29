<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Scan Dokumen</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<div class="max-w-xl mx-auto py-10">
    <h1 class="text-xl font-bold mb-4">Scan Dokumen (Foto / PDF)</h1>

    @if ($errors->any())
        <div class="text-red-600 mb-4">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required class="mb-4 block">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
            Scan Sekarang
        </button>
    </form>
</div>
</body>
</html>