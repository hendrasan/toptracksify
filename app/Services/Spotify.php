<?php

namespace App\Services;

use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPIException;

use Log;
use Carbon\Carbon;

use App\Models\Chart;
use App\Models\Track;

class Spotify {

  protected $spotify;

  public function __construct(SpotifyWebAPI $spotify)
  {
    $this->spotify = $spotify;
  }

  public function generateChart($chart)
  {
    try {
      if (!$chart instanceof Chart) {
        Log::info('The parameter passed is not an instance of Chart model');
        return;
      }

      if (empty($chart)) {
        // can't generate a chart if you have no chart
        return;
      }

      $user = $chart->user;

      $now = Carbon::now();
      $now_timestamp = $now->toDateTimeString();

      if (!empty($chart->last_updated)) {
        $chart_last_updated = Carbon::createFromFormat('Y-m-d H:i:s', $chart->last_updated);

        // don't generate new chart if the latest chart is under 7 days old
        if ($now->diffInDays($chart_last_updated) < 7) {
          return;
        }
      }

      $this->spotify->setAccessToken($user->spotify_access_token);

      $top20_tracks = $this->spotify->getMyTop('tracks', [
          'limit' => $chart->number_of_tracks,
          'time_range' => 'short_term' // long_term | medium_term | short_term
      ]);


      // get latest period
      $latest_chart = $chart->tracks()->latest()->first();

      if (!empty($latest_chart)) {
        // generate chart
        $new_tracks = [];
        $current_chart = Track::where('chart_id', $chart->id)
                              ->where('period', $latest_chart->period)
                              ->get();

        foreach ($top20_tracks->items as $key => $item) {
          $track_current_chart = $current_chart->firstWhere('track_spotify_id', $item->id);
          $track_current_position = $key + 1;
          $track_peak_position = Track::where('chart_id', $chart->id)->where('track_spotify_id', $item->id)->min('position');
          if (empty($track_peak_position) || $track_current_position < $track_peak_position) {
            $track_peak_position = $track_current_position;
          }
          $track_periods_on_chart = Track::where('chart_id', $chart->id)->where('track_spotify_id', $item->id)->count() + 1;

          $pushed_item = array(
            'chart_id'         => $chart->id,
            'period'           => $latest_chart->period + 1,
            'track_spotify_id' => $item->id,
            'track_name'       => $item->name,
            'track_artist'     => $item->artists[0]->name,
            'track_data'       => json_encode($item),
            'position'         => $track_current_position,
            'last_position'    => $track_current_chart ? $track_current_chart->position : null,
            'periods_on_chart' => $track_periods_on_chart,
            'peak_position'    => $track_peak_position,
            'is_reentry'       => empty($track_current_chart) && Track::where('chart_id', $chart->id)->where('track_spotify_id', $item->id)->count() > 0 ? 1 : null,
            'created_at'       => $now_timestamp,
            'updated_at'       => $now_timestamp
          );

          array_push($new_tracks, $pushed_item);
        }

        $track = Track::insert($new_tracks);

        $chart->last_updated = $now_timestamp;
        $chart->save();

      } else {
        // otherwise, create a chart
        $new_tracks = [];
        foreach ($top20_tracks->items as $key => $item) {
          $pushed_item = array(
            'chart_id'         => $chart->id,
            'period'           => 1,
            'track_spotify_id' => $item->id,
            'track_name'       => $item->name,
            'track_artist'     => $item->artists[0]->name,
            'track_data'       => json_encode($item),
            'position'         => $key + 1,
            'last_position'    => null,
            'periods_on_chart' => 1,
            'peak_position'    => $key + 1,
            'is_reentry'       => null,
            'created_at'       => $now_timestamp,
            'updated_at'       => $now_timestamp
          );

          array_push($new_tracks, $pushed_item);
        }

        $track = Track::insert($new_tracks);
        $chart->last_updated = $now_timestamp;
        $chart->save();
      }

      return true;
    }
    catch(Exception $e) {
      Log::info($e);
    }
    catch(SpotifyWebAPIException $e) {
      if ($e->getMessage() == "The access token expired") {
        Log::info("Access token expired. Refreshing token...");

        $session = new Session(
          config('services.spotify.client_id'),
          config('services.spotify.client_secret'),
          config('services.spotify.redirect')
        );

        $session->refreshAccessToken($user->spotify_refresh_token);

        $accessToken = $session->getAccessToken();

        $user->spotify_access_token = $accessToken;
        $user->save();

        $this->generateChart($chart);
      }

      Log::info($e);
    }
  }

  // public function generateChart($chart)
  // {
  //   try {
  //     if (!$chart instanceof Chart) {
  //       Log::info('The parameter passed is not an instance of Chart model');
  //       return;
  //     }

  //     // $chart = Chart::where('user_id', $user->id)
  //     //                 ->latest()
  //     //                 ->first();

  //     if (empty($chart)) {
  //       // can't generate a chart if you have no chart
  //       return;
  //     }

  //     $now = Carbon::now();
  //     $now_timestamp = $now->toDateTimeString();

  //     if (!empty($chart->last_updated)) {
  //       $chart_last_updated = Carbon::createFromFormat('Y-m-d H:i:s', $chart->last_updated);

  //       // don't generate new chart if the latest chart is under 7 days old
  //       if ($now->diffInDays($chart_last_updated) < 7) {
  //         return;
  //       }
  //     }

  //     $this->spotify->setAccessToken($user->spotify_access_token);

  //     $top20_tracks = $this->spotify->getMyTop('tracks', [
  //         'limit' => $chart->number_of_tracks,
  //         'time_range' => 'short_term' // long_term | medium_term | short_term
  //     ]);


  //     // get latest period
  //     $latest_chart = $chart->tracks()->latest()->first();

  //     if (!empty($latest_chart)) {
  //       // generate chart
  //       $new_tracks = [];
  //       $current_chart = Track::where('chart_id', $chart->id)
  //                             ->where('period', $latest_chart->period)
  //                             ->get();

  //       foreach ($top20_tracks->items as $key => $item) {
  //         $track_current_chart = $current_chart->firstWhere('track_spotify_id', $item->id);
  //         $track_current_position = $key + 1;
  //         $track_peak_position = Track::where('chart_id', $chart->id)->where('track_spotify_id', $item->id)->min('position');
  //         if (empty($track_peak_position) || $track_current_position < $track_peak_position) {
  //           $track_peak_position = $track_current_position;
  //         }

  //         $pushed_item = array(
  //           'chart_id'         => $chart->id,
  //           'period'           => $latest_chart->period + 1,
  //           'track_spotify_id' => $item->id,
  //           'track_name'       => $item->name,
  //           'track_artist'     => $item->artists[0]->name,
  //           'track_data'       => json_encode($item),
  //           'position'         => $track_current_position,
  //           'last_position'    => $track_current_chart ? $track_current_chart->position : null,
  //           'periods_on_chart' => Track::where('chart_id', $chart->id)->where('track_spotify_id', $item->id)->count() + 1,
  //           'peak_position'    => $track_peak_position,
  //           'is_reentry'       => empty($track_current_chart) && Track::where('chart_id', $chart->id)->where('track_spotify_id', $item->id)->count() > 0 ? 1 : null,
  //           'created_at'       => $now_timestamp,
  //           'updated_at'       => $now_timestamp
  //         );

  //         array_push($new_tracks, $pushed_item);
  //       }

  //       $track = Track::insert($new_tracks);

  //       $chart->last_updated = $now_timestamp;
  //       $chart->save();

  //     } else {
  //       // otherwise, create a chart
  //       $new_tracks = [];
  //       foreach ($top20_tracks->items as $key => $item) {
  //         $pushed_item = array(
  //           'chart_id'         => $chart->id,
  //           'period'           => 1,
  //           'track_spotify_id' => $item->id,
  //           'track_name'       => $item->name,
  //           'track_artist'     => $item->artists[0]->name,
  //           'track_data'       => json_encode($item),
  //           'position'         => $key + 1,
  //           'last_position'    => null,
  //           'periods_on_chart' => 1,
  //           'peak_position'    => $key + 1,
  //           'is_reentry'       => null,
  //           'created_at'       => $now_timestamp,
  //           'updated_at'       => $now_timestamp
  //         );

  //         array_push($new_tracks, $pushed_item);
  //       }

  //       $track = Track::insert($new_tracks);
  //       $chart->last_updated = $now_timestamp;
  //       $chart->save();
  //     }

  //     return true;
  //   }
  //   catch(Exception $e) {
  //     Log::info($e);
  //   }
  //   catch(SpotifyWebAPIException $e) {
  //     if ($e->getMessage() == "The access token expired") {
  //       Log::info("Access token expired. Refreshing token...");

  //       $session = new Session(
  //         config('services.spotify.client_id'),
  //         config('services.spotify.client_secret'),
  //         config('services.spotify.redirect')
  //       );

  //       $session->refreshAccessToken($user->spotify_refresh_token);

  //       $accessToken = $session->getAccessToken();

  //       $user->spotify_access_token = $accessToken;
  //       $user->save();

  //       $this->generateChart($user->charts[0]);
  //     }

  //     Log::info($e);
  //   }

  // }

  public function createPlaylist($user, $payload)
  {
    try {
        $playlist_name = $payload['title'] ?? 'Your Top Songs 2018';
        $tracks = $payload['tracks'] ?? [];

        if (empty($tracks)) {
          return false;
        }

        $new_playlist = $this->spotify->createUserPlaylist($user->spotify_id, [
            'name' => $playlist_name
        ]);

        $this->spotify->addUserPlaylistTracks($user->spotify_id, $new_playlist->id, $tracks);

        return $new_playlist;
    }
    catch(SpotifyWebAPIException $e) {
      Log::info($e);
      // if ($request->ajax()) {
      //     return response()->json([
      //         'status' => $e->getCode(),
      //         'message' => $e->getMessage()
      //     ]);
      // } else {
      //     return redirect('/');
      // }
    }
  }
}
