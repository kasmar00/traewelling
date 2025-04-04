<?php

namespace App\Http\Controllers\Frontend\Stats;

use App\Http\Controllers\Backend\Support\LocationController;
use App\Http\Controllers\Controller;
use App\Models\Status;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use App\Http\Controllers\Backend\Stats\DailyStatsController as DailyStatsBackend;
use Illuminate\View\View;

class DailyStatsController extends Controller
{
    public function renderDailyStats(string $dateString): View {
        $date     = Date::parse($dateString);
        $statuses = DailyStatsBackend::getStatusesOnDate(Auth::user(), $date)
                                     ->map(function(Status $status) {
                                         $status->mapLines = LocationController::forStatus($status)->getMapLines(true);
                                         return $status;
                                     });
        
        $previous_date = DailyStatsBackend::getPreviousTravelDate(Auth::user(), $date);
        $next_date = DailyStatsBackend::getNextTravelDate(Auth::user(), $date);

        return view('stats.daily', [
            'date'     => $date,
            'statuses' => $statuses,
            'previous_date' => $previous_date,
            'next_date' => $next_date
        ]);
    }
}
