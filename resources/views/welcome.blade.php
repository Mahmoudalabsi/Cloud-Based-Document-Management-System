@php
    use Illuminate\Support\Str;
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Document Management - Cloudinary Version</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">Uploaded Documents (Cloudinary)</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Upload Form -->
        <form method="POST" action="{{ route('documents.upload') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="document" class="form-label">Choose Document (pdf, doc):</label>
                <input type="file" name="document" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category:</label>
                <input type="text" name="category" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Upload to Cloudinary</button>
        </form>

        <form method="GET" action="{{ route('documents.search') }}">
            <div class="input-group">
                <input type="text" name="keyword" class="form-control" placeholder="Search by title or category"
                    value="{{ request('keyword') }}">
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
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
                        <tr>
                            <td>
                                {!! isset($keyword) ? Str::of($doc->title)->replace($keyword, '<mark>' . $keyword . '</mark>') : $doc->title !!}
                            </td>
                            <td>
                                @php
                                    $ext = strtolower(pathinfo($doc->title, PATHINFO_EXTENSION));
                                @endphp
                                @if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
                                    <img src="{{ $doc->path }}" alt="Image" width="100">
                                @elseif($ext === 'pdf')
                                    <img src="https://cdn-icons-png.flaticon.com/512/337/337946.png" width="50"
                                        alt="PDF">
                                @elseif(in_array($ext, ['doc', 'docx']))
                                    <img src="https://cdn-icons-png.flaticon.com/512/337/337932.png" width="50"
                                        alt="DOC">
                                @else
                                    <img src="https://cdn-icons-png.flaticon.com/512/565/565547.png" width="50"
                                        alt="File">
                                @endif
                            </td>
                            <td>{{ round($doc->size / 1024, 2) }} KB</td>
                            <td>
                                {!! isset($keyword)
                                    ? Str::of($doc->category)->replace($keyword, '<mark>' . $keyword . '</mark>')
                                    : $doc->category !!}
                            </td>
                            <td><a href="{{ $doc->path }}" target="_blank" class="btn btn-sm btn-info">View /
                                    Download</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No documents uploaded yet.</p>
        @endif
    </div>
</body>

</html>
