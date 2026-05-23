<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Social\SocialCalendarService;
use Illuminate\Http\Request;

class SocialCalendarController extends Controller
{
    public function __construct(private SocialCalendarService $calendarService) {}

    public function index(Request $request)
    {
        $user     = $request->user();
        $clientId = $user->client_id;
        $month    = (int) $request->get('month', now()->month);
        $year     = (int) $request->get('year', now()->year);

        $posts    = $this->calendarService->getPostsForMonth($month, $year, $clientId);
        $calendar = $this->calendarService->buildCalendarGrid($month, $year, $posts);

        return view('client.social.calendar.index', compact('calendar', 'month', 'year'));
    }
}
