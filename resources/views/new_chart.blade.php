@extends('layout')

@section('content')
  @include('_partials.header')

  <div class="section">
    <div class="container">
      <h1>New Chart</h1>

      <form method="POST">
        @csrf
        <div class="field">
          <label class="label">My chart name is:</label>
          <div class="control">
            <input class="input" type="text" name="name" value="{{ auth()->user()->name }}'s Weekly Top Songs">
          </div>
        </div>

        <div class="field">
          <label class="label">It will have these many songs:</label>
          <div class="control">
            <input class="input" type="number" name="number_of_tracks" value="20">
          </div>
        </div>

        <div class="field">
          <div class="control">
            <label class="checkbox">
              <input type="checkbox" name="receive_notification" value="1"> I want an email notification when a new chart exists
            </label>
          </div>
        </div>

        <div class="field">
          <label class="label">My email is:</label>
          <div class="control">
            <input class="input" type="email" name="email">
          </div>
        </div>

        <div class="field is-grouped">
          <div class="control">
            <button class="button is-link">Create</button>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection
