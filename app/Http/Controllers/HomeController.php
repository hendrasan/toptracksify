<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Services\Spotify;

use App\Models\User;
use App\Models\Chart;
use App\Models\Track;

use Socialite;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only('getDashboard');
    }

    public function getIndex(Spotify $spotify)
    {
        $users = User::has('charts')->paginate(20);

        return view('index', compact('users'));
    }

    public function getUserChart($username, $chart_id = null)
    {
        $user = User::where('spotify_id', $username)->first();

        if (empty($user)) {
            return;
        }

        $latest_period = Track::where('chart_id', $user->charts[0]->id)->max('period');

        $current_period = $latest_period;

        if (!empty ($chart_id)) {
            $current_period = $chart_id;
        }

        if ($current_period > $latest_period) {
            return redirect()->route('chart', [$user->spotify_id]);
        }

        $this_week_chart = Track::where('chart_id', $user->charts[0]->id)->where('period', $current_period)->get();

        $chart = $this_week_chart->map(function($c) use($user) {
            $chart_runs = Track::where('chart_id', $user->charts[0]->id)->where('track_spotify_id', $c->track_spotify_id)->select('period', 'position', 'created_at')->get();
            return $c->setAttribute('chart_runs', $chart_runs);
        });

        return view('chart', compact('user', 'chart', 'latest_period', 'current_period'));
    }
    public function getDashboard()
    {
        $user = Auth::user();

        $latest_period = Track::where('chart_id', $user->charts[0]->id)
                                ->max('period');

        $chart = Track::where('chart_id', $user->charts[0]->id)->where('period', $latest_period)->get();

        return view('dashboard', compact('user', 'chart'));
    }
}
