<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateBlogPostRequest;
use App\Models\BlogPost;
use App\Models\Client;
use App\Services\Seo\SeoContentService;

class AdminBlogController extends Controller
{
    public function __construct(private SeoContentService $seoService) {}

    public function index()
    {
        $posts = BlogPost::with('author', 'client', 'category')
            ->when(request('type'), fn ($q) => $q->where('type', request('type')))
            ->when(request('status'), fn ($q) => $q->where('status', request('status')))
            ->when(request('client_id'), fn ($q) => $q->where('client_id', request('client_id')))
            ->latest()
            ->paginate(20);

        $clients = Client::orderBy('name')->get();

        return view('admin.blog.index', compact('posts', 'clients'));
    }

    public function generateAiForm()
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.blog.generate', compact('clients'));
    }

    public function generateAi(GenerateBlogPostRequest $request)
    {
        try {
            $post = $this->seoService->generateBlogPost($request->validated(), $request->user());

            return redirect()
                ->route('admin.seo.blog.index')
                ->with('success', "Post IA gerado com sucesso! \"{$post->title}\"");
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Erro ao gerar post: ' . $e->getMessage());
        }
    }

    public function sendToApproval(BlogPost $blogPost)
    {
        try {
            $this->seoService->sendBlogPostToApproval($blogPost, request()->user());

            return redirect()->route('admin.seo.blog.index')
                ->with('success', 'Post enviado para aprovação.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function publish(BlogPost $blogPost)
    {
        try {
            $this->seoService->publishBlogPost($blogPost, request()->user());

            return redirect()->route('admin.seo.blog.index')
                ->with('success', 'Post publicado com sucesso.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
