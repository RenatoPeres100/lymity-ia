<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogPostRequest;
use App\Http\Requests\UpdateBlogPostRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::with(['category', 'author'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.blog.posts.index', compact('posts'));
    }

    public function create(): View
    {
        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.blog.posts.create', compact('categories'));
    }

    public function store(StoreBlogPostRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['author_id'] = Auth::id();
        $data['type']      = 'agency';

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $data['tags']        = $this->parseTags($data['tags'] ?? null);
        $data['is_featured'] = $request->boolean('is_featured');

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        BlogPost::create($data);

        return redirect()->route('admin.blog-posts.index')
            ->with('success', 'Post criado com sucesso!');
    }

    public function edit(BlogPost $blogPost): View
    {
        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.blog.posts.edit', compact('blogPost', 'categories'));
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $blogPost): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $data['tags']        = $this->parseTags($data['tags'] ?? null);
        $data['is_featured'] = $request->boolean('is_featured');

        if ($data['status'] === 'published' && !$blogPost->published_at) {
            $data['published_at'] = now();
        }

        $blogPost->update($data);

        return redirect()->route('admin.blog-posts.index')
            ->with('success', 'Post atualizado com sucesso!');
    }

    public function destroy(BlogPost $blogPost): RedirectResponse
    {
        $blogPost->delete();

        return redirect()->route('admin.blog-posts.index')
            ->with('success', 'Post excluído com sucesso!');
    }

    private function parseTags(?string $raw): ?array
    {
        if (empty($raw)) return null;
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
}
