<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CaseStudyController extends Controller
{
    public function index(): View
    {
        $cases = CaseStudy::orderByDesc('created_at')->paginate(20);

        return view('admin.cases.index', compact('cases'));
    }

    public function create(): View
    {
        return view('admin.cases.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'              => ['required', 'string', 'max:255'],
            'client_name'        => ['nullable', 'string', 'max:255'],
            'industry'           => ['nullable', 'string', 'max:255'],
            'challenge'          => ['required', 'string'],
            'solution'           => ['nullable', 'string'],
            'testimonial'        => ['nullable', 'string'],
            'testimonial_author' => ['nullable', 'string', 'max:255'],
            'tags'               => ['nullable', 'string'],
            'results_raw'        => ['nullable', 'string'],
            'published_at'       => ['nullable', 'date'],
        ]);

        $data['slug']    = Str::slug($data['title']) . '-' . Str::random(4);
        $data['results'] = $this->parseResults($data['results_raw'] ?? null);
        $data['tags']    = $this->parseTags($data['tags'] ?? null);
        unset($data['results_raw']);

        CaseStudy::create($data);

        return redirect()->route('admin.cases.index')
            ->with('success', 'Case criado com sucesso!');
    }

    public function edit(CaseStudy $caseStudy): View
    {
        return view('admin.cases.edit', compact('caseStudy'));
    }

    public function update(Request $request, CaseStudy $caseStudy): RedirectResponse
    {
        $data = $request->validate([
            'title'              => ['required', 'string', 'max:255'],
            'client_name'        => ['nullable', 'string', 'max:255'],
            'industry'           => ['nullable', 'string', 'max:255'],
            'challenge'          => ['required', 'string'],
            'solution'           => ['nullable', 'string'],
            'testimonial'        => ['nullable', 'string'],
            'testimonial_author' => ['nullable', 'string', 'max:255'],
            'tags'               => ['nullable', 'string'],
            'results_raw'        => ['nullable', 'string'],
            'published_at'       => ['nullable', 'date'],
        ]);

        $data['results'] = $this->parseResults($data['results_raw'] ?? null);
        $data['tags']    = $this->parseTags($data['tags'] ?? null);
        unset($data['results_raw']);

        $caseStudy->update($data);

        return redirect()->route('admin.cases.index')
            ->with('success', 'Case atualizado com sucesso!');
    }

    public function destroy(CaseStudy $caseStudy): RedirectResponse
    {
        $caseStudy->delete();

        return redirect()->route('admin.cases.index')
            ->with('success', 'Case excluído com sucesso!');
    }

    private function parseResults(?string $raw): ?array
    {
        if (empty($raw)) return null;
        $results = [];
        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);
            if (str_contains($line, ':')) {
                [$key, $val] = array_map('trim', explode(':', $line, 2));
                if ($key) $results[$key] = $val;
            }
        }
        return $results ?: null;
    }

    private function parseTags(?string $raw): ?array
    {
        if (empty($raw)) return null;
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
}
