<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::with(['category', 'author'])
            ->published()
            ->agency()
            ->latest('published_at')
            ->paginate(9);

        return view('site.blog', compact('posts'));
    }

    public function show(string $slug): View|RedirectResponse
    {
        $post = BlogPost::with(['category', 'author'])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        $relatedPosts = BlogPost::with('category')
            ->published()
            ->agency()
            ->where('id', '!=', $post->id)
            ->when($post->category_id, fn($q) => $q->where('category_id', $post->category_id))
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('site.blog-post', compact('post', 'relatedPosts'));
    }
}
