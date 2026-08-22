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
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072', // Maksimal 3MB
        ]);

        $data = [
            'title' => $request->title,
            'slug' => \Illuminate\Support\Str::slug($request->title),
            'content' => $request->content,
        ];

        // Proses Upload Gambar
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/articles'), $imageName);
            $data['image_path'] = '/uploads/articles/' . $imageName;
        }

        \App\Models\Article::create($data);

        return response()->json(['message' => 'Artikel berhasil dibuat']);
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
    public function show($idOrSlug)
    {
        $article = Article::where('slug', $idOrSlug)->orWhere('id', $idOrSlug)->first();

        if (!$article) {
            return response()->json(['message' => 'Artikel tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail artikel berhasil diambil',
            'data' => $article
        ]);
    }

    public function showBySlug($slug)
    {
        return $this->show($slug);
    }
}