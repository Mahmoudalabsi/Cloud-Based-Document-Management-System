<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser; //

class DocumentController extends Controller
{
    // هذه الوظيفة يمكن أن تخدم كـ API لجميع المستندات أو للبحث
    public function index(Request $request)
    {
        $query = Document::query(); //

        if ($request->has('keyword')) { //
            $keyword = $request->input('keyword'); //
            $query->where('title', 'like', "%$keyword%") //
                ->orWhere('category', 'like', "%$keyword%"); //
            // يمكنك أيضًا إضافة بحث في عمود المحتوى هنا إذا قمت بملئه
            // ->orWhere('content', 'like', "%$keyword%");
        }

        $documents = $query->get(); //

        // **لـ API: أعد JSON بدلاً من View**
        return response()->json($documents);
    }


    public function upload(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:20480', // 20MB max
            'category' => 'nullable|string|max:255', //
        ]);

        $file = $request->file('document'); //
        $size = $file->getSize(); //
        $category = $request->input('category', 'Uncategorized'); //

        $title = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); //
        $extension = $file->getClientOriginalExtension(); //
        $titleWithExt = $title . '.' . $extension; //

        $uploadedFileUrl = null;
        try {
            // رفع الملف إلى Cloudinary:
            $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [ //
                'folder' => 'documents' //
            ])->getSecurePath(); //
        } catch (\Exception $e) {
            // معالجة خطأ رفع Cloudinary
            return response()->json(['error' => 'Failed to upload to Cloudinary: ' . $e->getMessage()], 500);
        }

        $documentContent = null;
        // حاول استخراج المحتوى إذا كان PDF أو DOCX (يتطلب مكتبات إضافية لـ DOCX)
        if ($extension === 'pdf') {
            try {
                $parser = new Parser(); //
                $pdf = $parser->parseFile($file->getRealPath());
                $documentContent = $pdf->getText();
            } catch (\Exception $e) {
                \Log::error("PDF parsing failed for " . $titleWithExt . ": " . $e->getMessage());
                // يمكنك اختيار إرجاع خطأ أو الاستمرار مع محتوى فارغ
            }
        }
        // يمكن إضافة منطق لملفات .doc/.docx باستخدام مكتبات مثل PhpWord أو PhpSpreadsheet

        // حفظ في قاعدة البيانات:
        $document = Document::create([ //
            'title' => $titleWithExt, //
            'path' => $uploadedFileUrl, //
            'size' => $size, //
            'category' => $category, //
            'content' => $documentContent, // **أضف هذا**
        ]);

        // **لـ API: أعد JSON بدلاً من Redirection**
        return response()->json([
            'message' => 'File uploaded to Cloudinary successfully!',
            'document' => $document
        ], 201); // 201 Created
    }

    // هذه الوظيفة يمكن دمجها في Index أو تكون نقطة نهاية API منفصلة للبحث
    public function search(Request $request)
    {
        $keyword = $request->input('keyword'); //
        if (empty($keyword)) {
            return response()->json(['message' => 'Keyword is required for search.'], 400);
        }

        $documents = Document::where('title', 'like', "%$keyword%") //
            ->orWhere('category', 'like', "%$keyword%") //
            ->orWhere('content', 'like', "%$keyword%") // **أضف البحث في المحتوى**
            ->get(); //

        // **لـ API: أعد JSON بدلاً من View**
        return response()->json($documents);
    }

    // نقطة نهاية API للترتيب
    public function sort()
    {
        $documents = Document::orderBy('title', 'asc')->get(); //

        // **لـ API: أعد JSON بدلاً من View**
        return response()->json($documents);
    }

    public function classify(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:documents,id',
        ]);

        $id = $request->input('id'); //
        $document = Document::find($id); //

        if ($document) { //
            $title = strtolower($document->title); //
            $content = strtolower($document->content ?? ''); // استخدم المحتوى أيضًا

            $category = 'Uncategorized'; //

            if (Str::contains($title, ['php', 'laravel', 'programming', 'code']) || Str::contains($content, ['php', 'laravel', 'programming', 'code'])) {
                $category = 'Technical';
            } elseif (Str::contains($title, ['business', 'marketing', 'sales']) || Str::contains($content, ['business', 'marketing', 'sales'])) {
                $category = 'Business';
            } elseif (Str::contains($title, ['health', 'medical', 'medicine']) || Str::contains($content, ['health', 'medical', 'medicine'])) {
                $category = 'Health';
            } elseif (Str::contains($title, ['education', 'learning', 'school']) || Str::contains($content, ['education', 'learning', 'school'])) {
                $category = 'Education';
            }

            $document->category = $category; //
            $document->save(); //

            // **لـ API: أعد JSON بدلاً من Redirection**
            return response()->json([
                'message' => 'Document classified successfully!',
                'document' => $document
            ]);
        }

        // **لـ API: أعد JSON بدلاً من Redirection**
        return response()->json(['error' => 'Document not found'], 404); //
    }


    public function stats()
    {
        $count = Document::count(); //
        $size = Document::sum('size'); //

        // **لـ API: أعد JSON بدلاً من View**
        return response()->json([
            'total_files' => $count,
            'total_size_bytes' => $size
        ]);
    }
}
