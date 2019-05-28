<div class="hero is-primary is-bold">
    <div class="hero-body">
        <div class="container has-text-centered">
            <h1 class="title is-2"><a href="{{ route('home') }}">Top Tracksify <span class="icon is-size-3"><i class="fas fa-music"></i></span></a></h1>

            @if (currentRouteName() == 'home')
                @if (!Auth::check())
                    <a class="button is-primary is-inverted is-outlined" href="{{ route('login.spotify') }}">
                        <span class="icon is-small">
                            <i class="fab fa-spotify"></i>
                        </span>
                        <span>
                            Login with your Spotify
                        </span>
                    </a>
                @else
                    <a class="button is-primary is-inverted is-outlined" href="{{ route('dashboard') }}">
                        Your Dashboard
                    </a>
                @endif
            @endif
        </div>
    </div>
</div>
