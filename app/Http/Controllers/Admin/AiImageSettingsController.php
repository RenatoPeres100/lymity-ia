<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiImageGeneration;
use App\Services\AiImages\GeminiAiImageService;
use Illuminate\Http\Request;

class AiImageSettingsController extends Controller
{
    public function __construct(
        private readonly GeminiAiImageService $imageService,
    ) {}

    public function index()
    {
        $geminiConfigured = !empty(config('ai.gemini_api_key'));
        $textModel        = config('ai.gemini_text_model');
        $imageModel       = config('ai.gemini_image_model');
        $imageEnabled     = config('ai-images.enabled', false);
        $dailyLimit       = config('ai-images.gemini.daily_limit');
        $monthlyLimit     = config('ai-images.gemini.monthly_limit');

        $todayCount   = AiImageGeneration::whereDate('created_at', today())->count();
        $monthCount   = AiImageGeneration::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count();

        $lastError = AiImageGeneration::where('status', 'failed')
            ->whereNotNull('error_message')
            ->latest()
            ->value('error_message');

        return view('admin.ai-images.settings', compact(
            'geminiConfigured', 'textModel', 'imageModel', 'imageEnabled',
            'dailyLimit', 'monthlyLimit', 'todayCount', 'monthCount', 'lastError'
        ));
    }

    public function testConnection()
    {
        $result = $this->imageService->testConnection();

        if ($result['ok']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
