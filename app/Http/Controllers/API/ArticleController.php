<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::latest()->get();
        return response()->json(['data' => $articles]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $baseSlug = Str::slug($request->title);
        $slug = $baseSlug;
        $count = 1;
        while (Article::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        $article = Article::create([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'is_published' => true,
        ]);

        return response()->json(['message' => 'Artikel berhasil dibuat', 'data' => $article], 201);
    }

    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $baseSlug = Str::slug($request->title);
        $slug = $baseSlug;
        $count = 1;
        while (Article::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        $article->update([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
        ]);

        return response()->json(['message' => 'Artikel berhasil diperbarui', 'data' => $article]);
    }

    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return response()->json(['message' => 'Artikel berhasil dihapus']);
    }
}