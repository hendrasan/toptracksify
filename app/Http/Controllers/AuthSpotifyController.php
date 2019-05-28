<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;
use Exception;

use Socialite;

use App\Models\User;
use App\Models\Chart;
use App\Models\Track;
use App\Services\Spotify;

class AuthSpotifyController extends Controller
{
    /**
     * Redirect the user to the Spotify authentication page
     *
     */
    public function spotifyLogin()
    {
        return Socialite::driver('spotify')
                ->scopes(['user-top-read', 'user-read-email', 'playlist-modify-public', 'playlist-modify-private'])
                ->redirect();
    }

    public function spotifyCallback(Spotify $spotify)
    {
        $user = Socialite::driver('spotify')->user();

        $auth_user = $this->findOrCreateUser($user);

        Auth::login($auth_user);

        // After creating user or logging in
        // if the user has no charts, ask the user to fill in
        // the chart name and type and generate one
        $user_charts = Chart::where('user_id', $auth_user->id)->exists();

        if (!$user_charts) {
            return redirect()->route('chart.create');
        }

        return redirect()->route('dashboard');
    }

    public function findOrCreateUser($user)
    {
        // check if  email or user spotify id (if email is empty) is in the database
        $auth_user = User::where('spotify_id', $user->id)->first();

        if (!$auth_user) {
            // if it doesn't exist, create a new user
            $new_user = User::create([
                'name' => $user->name,
                'email' => !empty($user->email) ? $user->email : '',
                'avatar' => !empty($user->avatar) ? $user->avatar : '',
                'spotify_id' => $user->id,
                'spotify_access_token' => $user->token,
                'spotify_refresh_token' => $user->refreshToken,
            ]);

            return $new_user;
        } else {
            // otherwise, update the access token and refresh token
            $auth_user->spotify_access_token = $user->token;
            $auth_user->spotify_refresh_token = $user->refreshToken;

            $auth_user->save();

            return $auth_user;
        }
    }

    public function getLogout()
    {
        Auth::logout();
        return redirect()->route('home');
    }

    public function getCreateChart()
    {
        return view('new_chart');
    }

    public function postCreateChart(Request $request, Spotify $spotify)
    {
        // validate the request

        $user = $request->user();

        if ($request->input('email')) {
            $user->email = $request->input('email');
        }

        if ($request->input('receive_notification')) {
            $user->receive_notification = $request->input('receive_notification');
        }

        $user->save();

        $chart = Chart::create([
            'user_id'          => $user->id,
            'name'             => $request->input('name'),
            'type'             => 'weekly',
            'number_of_tracks' => $request->input('number_of_tracks'),
        ]);

        // generate the chart!
        $spotify->generateChart($chart);

        return redirect()->route('dashboard');
    }
}
