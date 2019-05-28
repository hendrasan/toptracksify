@extends('layout')

@section('content')
  @include('_partials.header')

  <div class="section">
    <div class="container">
      <div class="columns is-variable is-6">
        <div class="column">
          <div class="level">
            <div class="level-left">
              <div>
                <h2 class="title is-3">{{ $user->name }}'s Top 20</h2>
                <h3 class="subtitle is-6 has-text-grey">Chart date: {{ $chart[0]->created_at->format('d M Y') }}</h3>
                {{-- <h3 class="subtitle is-6 has-text-grey">(Last update at {{ $chart[0]->created_at->format('d M Y H:i:s') }})</h3> --}}
                @if ($current_period > 1)
                  <a href="{{ route('chart', [$user->spotify_id, $current_period - 1] ) }}">< Prev Week</a>
                @endif
                @if ($current_period < $latest_period)
                  <a href="{{ route('chart', [$user->spotify_id, $current_period + 1] ) }}">Next Week ></a>
                @endif
              </div>
            </div>
          </div>

          <div class="box">
            <div class="columns is-mobile">
              <div class="column is-1">Last Week</div>
              <div class="column is-1">This Week</div>
              <div class="column is-1"></div>
              <div class="column is-narrow"></div>
              <div class="column is-narrow"></div>
              <div class="column"></div>
              <div class="column is-1">PEAK</div>
              <div class="column is-1">Weeks on Chart</div>
            </div>
          </div>

          @foreach ($chart as $index => $item)
            <?php $position = $index + 1; ?>
            <div class="box">
              <div class="columns is-mobile">
                <div class="column is-1 has-text-grey has-text-centered" style="min-width: 70px;">
                  <div class="is-size-5" style="color: #aaa">
                    <span>{{ $item->last_position ?: '-' }}</span>
                  </div>
                </div>
                <div class="column is-1 has-text-grey has-text-centered" style="min-width: 70px;">
                  <div class="is-size-5" style="color: #000">
                    <span>{{ $position }}</span>
                  </div>
                  {{-- <div class="is-size-7">
                    <span><i class="fas {{ getArrowIcon($position, $item->last_position) }}"></i></span>
                    {{ $item->last_position ? '(' . $item->last_position . ')' : ($item->is_reentry ? 'RE' : 'NEW') }}
                  </div> --}}
                </div>
                <div class="column is-1">
                  <div class="is-size-5">
                    <span>
                      <i class="fas {{ getArrowIcon($position, $item->last_position) }}"></i>
                      {{-- @if (empty($item->last_position))
                        {{ $item->is_reentry ? 'RE' : 'NEW' }}
                      @else
                        @if ($item->last_position > $position)
                          {{ $item->last_position - $position }}
                        @elseif ($item->last_position < $position)
                          {{ $position - $item->last_position }}
                        @endif
                      @endif --}}
                    </span>
                  </div>
                </div>
                <div class="column is-narrow">
                  <figure class="image is-48x48">
                    <img src="{{ json_decode($item->track_data)->album->images[2]->url }}" alt="{{ $item->track_name }}">
                  </figure>
                </div>
                <div class="column is-narrow">
                  <a href="{{ json_decode($item->track_data)->external_urls->spotify }}" target="_blank"><i class="fas fa-play"></i></a>
                </div>
                <div class="column">
                  <div class="title is-5 has-text-weight-normal">
                    <a href="{{ json_decode($item->track_data)->external_urls->spotify }}" target="_blank">{{ $item->track_name }}</a>
                  </div>
                  <div class="subtitle is-6 has-text-grey">
                    {{ $item->track_artist }}
                  </div>
                </div>
                <div class="column is-1 has-text-centered">
                  {{-- <span class="is-size-7">PEAK</span> --}}
                  <div class="is-size-5">
                    <span>{{ $item->peak_position }}</span>
                  </div>
                </div>
                <div class="column is-1 has-text-centered">
                  {{-- <span class="is-size-7">WEEKS ON CHART</span> --}}
                  <div class="is-size-5">
                    <span>{{ $item->periods_on_chart }}</span>
                  </div>
                </div>

                {{-- <div class="column is-5 has-text-centered">
                  <span class="is-size-7">Chart Runs</span>
                  <div class="is-size-5">
                    @foreach ($item->chart_runs as $cr)
                      <span>{{ $cr->position }}</span>
                    @endforeach
                  </div>
                </div> --}}
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
@endsection
