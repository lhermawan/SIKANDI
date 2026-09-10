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
}
