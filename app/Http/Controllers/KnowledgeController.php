<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\KnowledgeArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $user = auth()->user();
        if (! $user->hasAnyRole(['Super Admin', 'Admin Persandian']) && $knowledge->author_id !== $user->id) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengedit artikel ini.');
        }

        return view('knowledge.edit', compact('knowledge'));
    }

    public function update(Request $request, KnowledgeArticle $knowledge)
    {
        $user = auth()->user();
        if (! $user->hasAnyRole(['Super Admin', 'Admin Persandian']) && $knowledge->author_id !== $user->id) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengubah artikel ini.');
        }

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
        $user = auth()->user();
        if (! $user->hasAnyRole(['Super Admin', 'Admin Persandian']) && $knowledge->author_id !== $user->id) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk menghapus artikel ini.');
        }

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
        $user = auth()->user();
        if ($document->is_confidential) {
            $isAuthorized = $user->hasAnyRole(['Super Admin', 'Admin Persandian'])
                || ($user->organization_id && $user->organization_id === $document->organization_id)
                || ($user->id === $document->uploaded_by);

            if (! $isAuthorized) {
                abort(403, 'Akses ditolak: Dokumen ini bersifat rahasia dan hanya dapat diakses oleh instansi terkait atau admin.');
            }
        }

        $fullPath = storage_path('app/public/'.$document->file_path);
        if (! file_exists($fullPath)) {
            abort(404, 'Berkas dokumen tidak ditemukan di server.');
        }

        return response()->download($fullPath, $document->title.'.'.pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    public function destroyDocument(Document $document)
    {
        $user = auth()->user();
        $isAuthorized = $user->hasAnyRole(['Super Admin', 'Admin Persandian'])
            || ($user->id === $document->uploaded_by);

        if (! $isAuthorized) {
            abort(403, 'Akses ditolak: Anda tidak memiliki izin untuk menghapus dokumen ini.');
        }

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
