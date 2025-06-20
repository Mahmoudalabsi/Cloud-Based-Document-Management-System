@php
    use Illuminate\Support\Str;
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Document Management - Cloudinary Version</title>
    <link rel="stylesheet" href="{{ asset('bootstrap/css/bootstrap.min.css') }}">
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">Uploaded Documents (Cloudinary)</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (isset($document))
            <div class="alert alert-info">
                <h4>Classified Document Details:</h4>
                <p><strong>Title:</strong> {{ $document->title }}</p>
                <p><strong>Category:</strong> {{ $document->category }}</p>
                <p><strong>Size:</strong> {{ number_format($document->size / 1024, 2) }} KB</p>
                <p><a href="{{ $document->path }}" target="_blank" class="btn btn-sm btn-primary">View / Download</a></p>
            </div>
        @endif
        @if (isset($count) && isset($size))
            <div class="alert alert-secondary">
                <strong>Stats:</strong> Total Documents: {{ $count }}, Total Size:
                {{ number_format($size / 1024, 2) }} KB
            </div>
        @endif


        <!-- Upload Form -->
        <form method="POST" action="{{ route('documents.upload') }}" enctype="multipart/form-data" class="mb-4">
            @csrf
            <div class="mb-3 form-group">
                <label for="document" class="form-label">Choose Document (pdf, doc):</label>
                <input type="file" name="document" class="form-control" required>
            </div>
            <div class="mb-3 form-group">
                <label for="category" class="form-label">Category:</label>
                <input type="text" name="category" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Upload to Cloudinary</button>
        </form>

        <form method="GET" action="{{ route('documents.stats') }}" class="mb-4">
            <button type="submit" class="btn btn-info">View Stats</button>
        </form>

        <form method="GET" action="{{ route('documents.search') }}" class="mb-4 d-flex">
            <input type="text" name="keyword" class="form-control me-2" placeholder="Search by title or category"
                value="{{ request('keyword') }}">
            <button class="btn btn-primary" type="submit">Search</button>
        </form>

        <form method="GET" action="{{ route('documents.sort') }}" class="mb-4">
            <button type="submit" class="btn btn-secondary">Sort by Title</button>
        </form>


        <!-- Documents Table -->
        @if ($documents->count())
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Preview / Icon</th>
                        <th>Size (KB)</th>
                        <th>Category</th>
                        <th>Cloudinary URL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($documents as $doc)
                        @php $ext = strtolower(pathinfo($doc->title, PATHINFO_EXTENSION)); @endphp
                        <tr>
                            <td>
                                {!! isset($keyword)
                                    ? Str::of($doc->title)->replace($keyword, '<mark>' . e($keyword) . '</mark>')
                                    : e($doc->title) !!}
                            </td>
                            <td>
                                @if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
                                    <img src="{{ $doc->path }}" alt="Image" title="Image File" width="100">
                                @elseif($ext === 'pdf')
                                    <img src="https://cdn-icons-png.flaticon.com/512/337/337946.png" alt="PDF"
                                        title="PDF File" width="50">
                                @elseif(in_array($ext, ['doc', 'docx']))
                                    <img src="https://cdn-icons-png.flaticon.com/512/337/337932.png" alt="DOC"
                                        title="Word Document" width="50">
                                @else
                                    <img src="https://cdn-icons-png.flaticon.com/512/565/565547.png" alt="File"
                                        title="File" width="50">
                                @endif
                            </td>
                            <td>{{ number_format($doc->size / 1024, 2) }} KB</td>
                            <td>
                                {!! isset($keyword)
                                    ? Str::of($doc->category)->replace($keyword, '<mark>' . e($keyword) . '</mark>')
                                    : e($doc->category) !!}
                            </td>
                            <td>
                                <a href="{{ $doc->path }}" target="_blank" class="btn btn-sm btn-info">View /
                                    Download</a>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('documents.classify') }}">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $doc->id }}">
                                    <button type="submit" class="btn btn-warning btn-sm">Classify</button>
                                </form>
                            </td>

                        </tr>
                    @endforeach
                </tbody>

            </table>
        @else
            <p>No documents uploaded yet.</p>
        @endif
    </div>
    <script src="{{ asset(path: 'bo otstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
