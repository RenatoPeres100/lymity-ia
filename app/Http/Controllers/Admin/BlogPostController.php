<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\Blog\BlogPipelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function __construct(private BlogPipelineService $pipeline) {}

    public function index(): View
    {
        $posts = BlogPost::agency()
            ->with(['category', 'author'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.blog.posts.index', compact('posts'));
    }

    public function create(): View
    {
        $categories = BlogCategory::orderBy('name')->get();
        return view('admin.blog.posts.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'              => 'required|string|max:255',
            'slug'               => 'nullable|string|max:255',
            'subtitle'           => 'nullable|string|max:255',
            'excerpt'            => 'nullable|string',
            'content'            => 'required|string',
            'featured_image'     => 'nullable|string|max:500',
            'category_id'        => 'nullable|integer|exists:blog_categories,id',
            'seo_title'          => 'nullable|string|max:255',
            'seo_description'    => 'nullable|string|max:300',
            'focus_keyword'      => 'nullable|string|max:100',
            'secondary_keywords' => 'nullable|string',
        ]);

        $post = $this->pipeline->createManualDraft($data, $request->user());

        return redirect()->route('admin.blog.pipeline.index')
            ->with('success', "Rascunho '{$post->title}' criado com sucesso.");
    }

    public function show(BlogPost $blogPost): View
    {
        $blogPost->load('category', 'author', 'aiEmployee', 'approver');
        return view('admin.blog.posts.show', compact('blogPost'));
    }

    public function edit(BlogPost $blogPost): View
    {
        $categories = BlogCategory::orderBy('name')->get();
        return view('admin.blog.posts.edit', compact('blogPost', 'categories'));
    }

    public function update(Request $request, BlogPost $blogPost): RedirectResponse
    {
        $data = $request->validate([
            'title'              => 'required|string|max:255',
            'slug'               => 'nullable|string|max:255',
            'subtitle'           => 'nullable|string|max:255',
            'excerpt'            => 'nullable|string',
            'content'            => 'required|string',
            'featured_image'     => 'nullable|string|max:500',
            'category_id'        => 'nullable|integer|exists:blog_categories,id',
            'seo_title'          => 'nullable|string|max:255',
            'seo_description'    => 'nullable|string|max:300',
            'focus_keyword'      => 'nullable|string|max:100',
            'secondary_keywords' => 'nullable|string',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title']);
        }

        $blogPost->update($data);
        $this->pipeline->registerLog($blogPost, 'edited', $request->user());

        return redirect()->route('admin.blog.posts.show', $blogPost)
            ->with('success', 'Post atualizado com sucesso.');
    }

    public function destroy(BlogPost $blogPost): RedirectResponse
    {
        $blogPost->delete();
        return redirect()->route('admin.blog.pipeline.index')
            ->with('success', 'Post excluído.');
    }
}
