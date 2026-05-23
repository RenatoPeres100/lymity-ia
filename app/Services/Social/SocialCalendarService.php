<?php

namespace App\Services\Social;

use App\Models\ActivityLog;
use App\Models\SocialCalendar;
use App\Models\SocialPost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class SocialCalendarService
{
    public function getPostsForMonth(int $month, int $year, ?int $clientId = null, ?int $companyId = null): Collection
    {
        $query = SocialPost::whereMonth('scheduled_at', $month)
            ->whereYear('scheduled_at', $year)
            ->whereNotNull('scheduled_at')
            ->with(['client', 'aiEmployee']);

        if ($clientId) {
            $query->where('client_id', $clientId);
        } elseif ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->orderBy('scheduled_at')->get();
    }

    public function buildCalendarGrid(int $month, int $year, Collection $posts): array
    {
        $firstDay  = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $firstDay->daysInMonth;
        $startDay  = $firstDay->dayOfWeek;

        $grid = [];
        $postsByDay = $posts->groupBy(fn ($p) => $p->scheduled_at->day);

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $grid[$day] = [
                'day'   => $day,
                'posts' => $postsByDay->get($day, collect()),
            ];
        }

        return [
            'grid'       => $grid,
            'startDay'   => $startDay,
            'daysInMonth'=> $daysInMonth,
            'month'      => $month,
            'year'       => $year,
        ];
    }

    public function createCalendar(array $data): SocialCalendar
    {
        $calendar = SocialCalendar::create([
            'client_id'        => $data['client_id'] ?? null,
            'company_id'       => $data['company_id'] ?? null,
            'month'            => $data['month'],
            'year'             => $data['year'],
            'strategy_summary' => $data['strategy_summary'] ?? null,
            'status'           => $data['status'] ?? 'draft',
        ]);

        if (class_exists(ActivityLog::class)) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'client_id'   => $calendar->client_id,
                'action'      => 'social_calendar_created',
                'module'      => 'social_media',
                'description' => "Calendário {$calendar->month_name}/{$calendar->year}",
            ]);
        }

        return $calendar;
    }
}
