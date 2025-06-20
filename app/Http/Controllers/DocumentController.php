<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;

class DocumentController extends Controller
{
    public function __construct()
    {
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
    }

    public function index(Request $request)
    {
        $query = Document::query();

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where('title', 'like', "%$keyword%")
                ->orWhere('category', 'like', "%$keyword%");
        }

        $documents = $query->get();

        $count = Document::count();
        $size = Document::sum('size');

        return view('welcome', compact('documents', 'count', 'size'));
    }


    public function upload(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:20480',
            'category' => 'nullable|string|max:255',
        ]);

        $file = $request->file('document');
        $size = $file->getSize();
        $category = $request->input('category', 'Uncategorized');
        $title = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $titleWithExt = $title . '.' . $extension;

        try {
            $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
                'folder' => 'documents'
            ])->getSecurePath();
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to upload to Cloudinary: ' . $e->getMessage()]);
        }

        if (!$uploadedFileUrl) {
            return redirect()->back()->withErrors(['error' => 'Cloudinary upload failed.']);
        }

        $documentContent = null;
        if ($extension === 'pdf' && class_exists(Parser::class)) {
            try {
                $parser = new Parser();
                $pdf = $parser->parseFile($file->getRealPath());
                $documentContent = $pdf->getText();
            } catch (Exception $e) {
                Log::error("PDF parsing failed for " . $titleWithExt . ": " . $e->getMessage());
            }
        }

        $document = Document::create([
            'title' => $titleWithExt,
            'path' => $uploadedFileUrl,
            'size' => $size,
            'category' => $category,
            'content' => $documentContent,
        ]);

        return redirect()->route('documents.index')
            ->with('success', 'File uploaded to Cloudinary successfully!');
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword');

        if (empty($keyword)) {
            return redirect()->back()->withErrors(['error' => 'Keyword is required.']);
        }

        $documents = Document::where('title', 'like', "%$keyword%")
            ->orWhere('category', 'like', "%$keyword%")
            ->orWhere('content', 'like', "%$keyword%")
            ->get();

        return view('search', compact('documents', 'keyword'));
    }



    public function classify(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:documents,id',
        ]);

        $document = Document::find($request->input('id'));

        if (!$document) {
            return redirect()->back()->withErrors(['error' => 'Document not found']);
        }

        $title = strtolower($document->title);
        $content = strtolower($document->content ?? '');

        $category = 'Uncategorized';

        if (
            Str::contains($title, ['php', 'laravel', 'programming', 'code']) ||
            Str::contains($content, ['php', 'laravel', 'programming', 'code'])
        ) {
            $category = 'Technical';
        } elseif (
            Str::contains($title, ['business', 'marketing', 'sales']) ||
            Str::contains($content, ['business', 'marketing', 'sales'])
        ) {
            $category = 'Business';
        } elseif (
            Str::contains($title, ['health', 'medical', 'medicine']) ||
            Str::contains($content, ['health', 'medical', 'medicine'])
        ) {
            $category = 'Health';
        } elseif (
            Str::contains($title, ['education', 'learning', 'school']) ||
            Str::contains($content, ['education', 'learning', 'school'])
        ) {
            $category = 'Education';
        }

        $document->category = $category;
        $document->save();

        $documents = Document::all(); // لجلب كل المستندات للجدول
        return view('welcome', compact('documents', 'document'))
            ->with('success', 'Document classified successfully!');
    }


    public function sort()
    {
        $documents = Document::orderBy('title')->get();
        $count = Document::count();
        $size = Document::sum('size');
        return view('welcome', compact('documents', 'count', 'size'));
    }

    public function stats()
    {
        $documents = Document::all();
        $count = Document::count();
        $size = Document::sum('size');
        return view('welcome', compact('documents', 'count', 'size'));
    }
}
