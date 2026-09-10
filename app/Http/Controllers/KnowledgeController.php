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
}
