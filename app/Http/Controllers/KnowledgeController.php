<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\KnowledgeArticle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KnowledgeController extends Controller
{
    public function index(Request $request): View
    {
        $query = KnowledgeArticle::with('author')->where('is_published', true);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                    ->orWhere('content', 'like', "%{$s}%");
            });
        }

        $articles = $query->latest()->paginate(10)->withQueryString();
        $documents = Document::with(['organization', 'uploader'])->latest()->take(8)->get();

        return view('knowledge.index', compact('articles', 'documents'));
    }

    public function create(): View
    {
        return view('knowledge.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'content' => 'required|string',
            'is_published' => 'boolean',
        ]);

        $validated['author_id'] = auth()->id();
        $validated['is_published'] = $request->has('is_published');

        KnowledgeArticle::create($validated);

        return redirect()->route('knowledge.index')->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function edit(KnowledgeArticle $knowledge): View
    {
        return view('knowledge.edit', compact('knowledge'));
    }

    public function update(Request $request, KnowledgeArticle $knowledge)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'content' => 'required|string',
            'is_published' => 'boolean',
        ]);

        $validated['is_published'] = $request->has('is_published');

        $knowledge->update($validated);

        return redirect()->route('knowledge.index')->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(KnowledgeArticle $knowledge)
    {
        $knowledge->delete();
        return redirect()->route('knowledge.index')->with('success', 'Artikel berhasil dihapus.');
    }

    // Document Management
    public function storeDocument(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240', // 10MB max
            'is_confidential' => 'boolean',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        Document::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'is_confidential' => $request->has('is_confidential'),
            'uploaded_by' => auth()->id(),
            'organization_id' => auth()->user()->organization_id, // Link to current user's org
        ]);

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function downloadDocument(Document $document)
    {
        // Add authorization check if confidential later
        return response()->download(storage_path('app/public/' . $document->file_path), $document->title . '.' . pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    public function destroyDocument(Document $document)
    {
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($document->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();
        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
