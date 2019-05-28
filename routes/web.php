<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@getIndex')->name('home');

Route::get('/login/spotify', 'AuthSpotifyController@spotifyLogin')->name('login.spotify');
Route::get('/auth/spotify', 'AuthSpotifyController@spotifyCallback');

Route::get('/logout', 'AuthSpotifyController@getLogout')->name('logout');

Route::group(['middleware' => ['auth']], function () {
  Route::get('chart/create', 'AuthSpotifyController@getCreateChart')->name('chart.create');
  Route::post('chart/create', 'AuthSpotifyController@postCreateChart')->name('chart.create.submit');
  Route::get('dashboard', 'HomeController@getDashboard')->name('dashboard');
});

Route::get('chart/{user}/{chartId?}', 'HomeController@getUserChart')->name('chart');

