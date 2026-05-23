<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSocialCalendarRequest;
use App\Http\Requests\UpdateSocialCalendarRequest;
use App\Models\Client;
use App\Models\SocialCalendar;
use App\Services\Social\SocialCalendarService;
use Illuminate\Http\Request;

class SocialCalendarController extends Controller
{
    public function __construct(private SocialCalendarService $calendarService) {}

    public function index(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);
        $clientId = $request->filled('client_id') ? (int) $request->client_id : null;

        $posts    = $this->calendarService->getPostsForMonth($month, $year, $clientId);
        $calendar = $this->calendarService->buildCalendarGrid($month, $year, $posts);
        $clients  = Client::orderBy('name')->get();

        $savedCalendars = SocialCalendar::where('month', $month)
            ->where('year', $year)
            ->with('client')
            ->get();

        return view('admin.social.calendar.index', compact('calendar', 'clients', 'savedCalendars', 'month', 'year', 'clientId'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.social.calendar.create', compact('clients'));
    }

    public function store(StoreSocialCalendarRequest $request)
    {
        $calendar = $this->calendarService->createCalendar($request->validated());
        return redirect()->route('admin.social.calendar.show', $calendar)->with('success', 'Calendário criado.');
    }

    public function show(SocialCalendar $calendar)
    {
        $posts    = $this->calendarService->getPostsForMonth($calendar->month, $calendar->year, $calendar->client_id);
        $grid     = $this->calendarService->buildCalendarGrid($calendar->month, $calendar->year, $posts);
        return view('admin.social.calendar.show', compact('calendar', 'grid', 'posts'));
    }

    public function update(UpdateSocialCalendarRequest $request, SocialCalendar $calendar)
    {
        $calendar->update($request->validated());
        return redirect()->route('admin.social.calendar.show', $calendar)->with('success', 'Calendário atualizado.');
    }

    public function destroy(SocialCalendar $calendar)
    {
        $calendar->delete();
        return redirect()->route('admin.social.calendar.index')->with('success', 'Calendário excluído.');
    }
}
