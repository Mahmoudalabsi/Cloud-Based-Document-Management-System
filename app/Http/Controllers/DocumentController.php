<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Smalot\PdfParser\Parser;
use Str;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::query();

        if ($request->has('keyword')) {
            $keyword = $request->input('keyword');
            $query->where('title', 'like', "%$keyword%")
                ->orWhere('category', 'like', "%$keyword%");
        }

        $documents = $query->get();

        return view('welcome', compact('documents'));
    }


    public function upload(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:20480', // 20MB max
            'category' => 'nullable|string|max:255',
        ]);

        $file = $request->file('document');
        $size = $file->getSize();
        $category = $request->input('category', 'Uncategorized');

        // رفع الملف إلى Cloudinary:
        $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
            'folder' => 'documents'
        ])->getSecurePath();

        // اسم الملف مع الامتداد:
        $title = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $titleWithExt = $title . '.' . $extension;

        // حفظ في قاعدة البيانات:
        Document::create([
            'title' => $titleWithExt,
            'path' => $uploadedFileUrl,
            'size' => $size,
            'category' => $category,
        ]);

        return redirect('/')->with('success', 'File uploaded to Cloudinary successfully!');
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        $documents = Document::where('title', 'like', "%$keyword%")
            ->orWhere('category', 'like', "%$keyword%")
            ->get();
        return view('search', compact('documents'));
    }

    public function sort()
    {
        $documents = Document::orderBy('title', 'asc')->get();
        return view('sort', compact('documents'));
    }

    public function classify(Request $request)
    {
        $id = $request->input('id');
        $document = Document::find($id);

        if ($document) {
            // مثال على شجرة تصنيف بسيطة حسب العنوان
            $title = strtolower($document->title);

            if (str::contains($title, ['php', 'laravel', 'programming', 'code'])) {
                $document->category = 'Technical';
            } elseif (Str::contains($title, ['business', 'marketing', 'sales'])) {
                $document->category = 'Business';
            } elseif (Str::contains($title, ['health', 'medical', 'medicine'])) {
                $document->category = 'Health';
            } elseif (Str::contains($title, ['education', 'learning', 'school'])) {
                $document->category = 'Education';
            } else {
                $document->category = 'Uncategorized';
            }

            $document->save();

            return redirect('/')->with('success', 'Document classified successfully as: ' . $document->category);
        }

        return redirect('/')->with('error', 'Document not found');
    }


    public function stats()
    {
        $count = Document::count();
        $size = Document::sum('size');

        return view('stats', compact('count', 'size'));
    }
}