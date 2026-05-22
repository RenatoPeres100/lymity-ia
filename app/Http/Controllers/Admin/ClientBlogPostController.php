<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientBlogPostRequest;
use App\Models\ActivityLog;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClientBlogPostController extends Controller
{
    public function index(Client $client): View
    {
        $posts = BlogPost::with('category')
            ->client($client->id)
            ->latest()
            ->paginate(20);

        return view('admin.clients.blog.index', compact('client', 'posts'));
    }

    public function create(Client $client): View
    {
        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.clients.blog.create', compact('client', 'categories'));
    }

    public function store(StoreClientBlogPostRequest $request, Client $client): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']) . '-' . Str::random(4);
        }
        $data['type']      = 'client';
        $data['client_id'] = $client->id;
        $data['author_id'] = Auth::id();

        if (($data['status'] ?? 'draft') === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post = BlogPost::create($data);

        ActivityLog::record('created', 'client_blog_posts',
            "Post '{$post->title}' criado para {$client->name}", $client->id);

        return redirect()->route('admin.clients.blog.index', $client)
            ->with('success', 'Post criado com sucesso!');
    }

    public function edit(Client $client, BlogPost $blogPost): View
    {
        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.clients.blog.edit', compact('client', 'blogPost', 'categories'));
    }

    public function update(StoreClientBlogPostRequest $request, Client $client, BlogPost $blogPost): RedirectResponse
    {
        $data = $request->validated();

        if (($data['status'] ?? '') === 'published' && !$blogPost->published_at) {
            $data['published_at'] = now();
        }

        $blogPost->update($data);

        ActivityLog::record('updated', 'client_blog_posts',
            "Post '{$blogPost->title}' atualizado para {$client->name}", $client->id);

        return redirect()->route('admin.clients.blog.index', $client)
            ->with('success', 'Post atualizado com sucesso!');
    }

    public function destroy(Client $client, BlogPost $blogPost): RedirectResponse
    {
        $title = $blogPost->title;
        $blogPost->delete();

        ActivityLog::record('deleted', 'client_blog_posts',
            "Post '{$title}' excluído de {$client->name}", $client->id);

        return redirect()->route('admin.clients.blog.index', $client)
            ->with('success', 'Post excluído com sucesso!');
    }

    public function approve(Client $client, BlogPost $blogPost): RedirectResponse
    {
        $blogPost->update(['status' => 'approved']);

        ActivityLog::record('approved', 'client_blog_posts',
            "Post '{$blogPost->title}' aprovado para {$client->name}", $client->id,
            ['approved_by' => Auth::id()]);

        return back()->with('success', 'Post aprovado com sucesso!');
    }

    public function reject(Client $client, BlogPost $blogPost): RedirectResponse
    {
        $blogPost->update(['status' => 'draft']);

        ActivityLog::record('rejected', 'client_blog_posts',
            "Post '{$blogPost->title}' reprovado para {$client->name}", $client->id,
            ['rejected_by' => Auth::id()]);

        return back()->with('success', 'Post reprovado. Status revertido para rascunho.');
    }

    public function publish(Client $client, BlogPost $blogPost): RedirectResponse
    {
        $blogPost->update(['status' => 'published', 'published_at' => now()]);

        ActivityLog::record('published', 'client_blog_posts',
            "Post '{$blogPost->title}' publicado para {$client->name}", $client->id);

        return back()->with('success', 'Post publicado com sucesso!');
    }
}
