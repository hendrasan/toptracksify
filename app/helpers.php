<?php

if ( ! function_exists('assets_url'))
{
  /**
   * Get the URL to an asset
   *
   * @param  string  $path
   * @return string
   */
  function assets_url($asset)
  {
    return asset('assets/' . ltrim($asset, '/'));
  }
}

if ( ! function_exists('uploads_url'))
{
  /**
   * Get the URL to uploads folder
   *
   * @param  string  $path
   * @return string
   */
  function uploads_url($upload)
  {
    return asset('uploads/' . ltrim($upload, '/'));
  }
}

if ( ! function_exists('uploads_path'))
{
  /**
   * Get the URL to uploads_path folder
   *
   * @param  string  $path
   * @return string
   */
  function uploads_path($upload)
  {
    if (!file_exists(public_path('uploads/' . $upload))) {
      return 'file not found';
    }

    return public_path() . '/uploads/' . ltrim($upload, '/');
  }
}


if ( ! function_exists('currentRouteName'))
{
  /**
   * Get the URL to currentRouteName folder
   *
   * @param  string  $path
   * @return string
   */
  function currentRouteName()
  {
    return Route::currentRouteName();
  }
}


if ( ! function_exists('getArrowIcon'))
{
  /**
   * Get the URL to getArrowIcon folder
   *
   * @param  string  $path
   * @return string
   */
  function getArrowIcon($curr, $prev)
  {
    if (empty($prev)) {
      return 'has-text-warning fa-star';
    }

    if ($curr < $prev) {
      return 'has-text-success fa-arrow-up';
    } elseif ($curr > $prev) {
      return 'has-text-danger fa-arrow-down';
    } elseif ($curr == $prev) {
      return 'has-text-info fa-arrows-alt-h';
    }
  }
}