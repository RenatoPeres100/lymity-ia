<?php

namespace App\Http\Controllers;

use App\Models\AiEmployee;
use App\Models\CaseStudy;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function home(): View
    {
        $aiEmployees = AiEmployee::where('status', 'active')->take(4)->get();

        return view('site.home', compact('aiEmployees'));
    }

    public function sobre(): View
    {
        return view('site.sobre');
    }

    public function servicos(): View
    {
        return view('site.servicos');
    }

    public function plataforma(): View
    {
        return view('site.plataforma');
    }

    public function funcionariosIa(): View
    {
        $aiEmployees = AiEmployee::with('skills')->where('status', 'active')->get();

        return view('site.funcionarios-ia', compact('aiEmployees'));
    }

    public function cases(): View
    {
        $cases = CaseStudy::published()->latest()->get();

        return view('site.cases', compact('cases'));
    }

    public function politicaPrivacidade(): View
    {
        return view('site.politica-de-privacidade');
    }

    public function termosDeServico(): View
    {
        return view('site.termos-de-servico');
    }
}
