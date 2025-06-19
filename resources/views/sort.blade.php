
<h2>Sorted Documents</h2>
<ul>
    @foreach($documents as $doc)
        <li>{{ $doc->title }} - {{ $doc->category }}</li>
    @endforeach
</ul>
<a href="/">Back</a>
