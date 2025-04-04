<?php

namespace App\Http\Controllers\Backend\Stats;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DailyStatsController extends Controller
{
    public static function getStatusesOnDate(User $user, Carbon $date): Collection {
        $start = $date->clone()->startOfDay()->tz('UTC');
        $end   = $date->clone()->endOfDay()->tz('UTC');

        return Status::with(['checkin', 'tags'])
                     ->join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
                     ->where('statuses.user_id', $user->id)
                     ->where('train_checkins.departure', '>=', $start)
                     ->where('train_checkins.departure', '<=', $end)
                     ->select('statuses.*')
                     ->get()
                     ->sortBy('checkin.departure');
    }

    public static function getPreviousTravelDate(User $user, Carbon $date): ?Carbon {
        $start = $date->clone()->startOfDay()->tz('UTC');
        $statuses = Status::with(['checkin', 'tags'])
                    ->join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
                    ->where('statuses.user_id', $user->id)
                    ->where('train_checkins.departure', '<', $start)
                    ->orderByDesc('train_checkins.departure')
                    ->limit(1)
                    ->get();
                    // TODO: only select one column (departure) from DB
        return $statuses->first()?->checkin()->first()->departure;
    }

    public static function getNextTravelDate(User $user, Carbon $date): ?Carbon {
        $start = $date->clone()->endOfDay()->tz('UTC');
        $statuses = Status::with(['checkin', 'tags'])
                    ->join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
                    ->where('statuses.user_id', $user->id)
                    ->where('train_checkins.departure', '>=', $start)
                    ->orderBy('train_checkins.departure')
                    ->limit(1)
                    ->get();
                    // TODO: only select one column (departure) from DB
        return $statuses->first()?->checkin()->first()->departure;
    }
}
