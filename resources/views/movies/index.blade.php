@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Movie List</h1>
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <!-- Form Search and filter -->
        <form method="GET" action="{{ route('movies.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search movies..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <input type="number" name="year" class="form-control" placeholder="Year" value="{{ request('year') }}">
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="movie" {{ request('type') == 'movie' ? 'selected' : '' }}>Movie</option>
                        <option value="series" {{ request('type') == 'series' ? 'selected' : '' }}>Series</option>
                        <option value="episode" {{ request('type') == 'episode' ? 'selected' : '' }}>Episode</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </div>
        </form>

        <!-- List Movie -->
        <div class="row" id="movie-list">
            @foreach ($movies as $movie)
                <div class="col-md-3 mb-4 movie-card">
                    <div class="card">
                        <a href="{{ route('movies.show', ['id' => $movie['imdbID']]) }}">
                            <img src="{{ $movie['Poster'] }}" class="card-img-top" alt="{{ $movie['Title'] }} Poster" loading="lazy">
                        </a>
                        <div class="card-body">
                            <h5 class="card-title">{{ $movie['Title'] }}</h5>
                            <p class="card-text"><strong>Year:</strong> {{ $movie['Year'] ?? 'N/A' }}</p>
                            <button class="btn btn-primary add-favorite" data-id="{{ $movie['imdbID'] }}">Add to Favorites</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div id="loading" style="display: none;">Loading...</div>
    </div>

<script>
    $(document).ready(function() {
        $('.add-favorite').click(function() {
            const movieId = $(this).data('id');

            $.ajax({
                url: `/movies/${movieId}/favorite`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    imdbID: movieId
                },
                success: function(response) {
                    alert('Movie added to favorites!');
                    const button = $(`.add-favorite[data-id='${movieId}']`);
                    button.removeClass('btn-primary add-favorite').addClass('btn-danger remove-favorite').text('Remove from Favorites');

                    button.off('click').on('click', function() {
                        removeFavorite(movieId);
                    });
                },
                error: function(xhr) {
                    alert('Error adding movie to favorites: ' + xhr.responseText);
                }
            });
        });

        function removeFavorite(movieId) {
            $.ajax({
                url: `/movies/${movieId}/remove-favorite`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    imdbID: movieId
                },
                success: function(response) {
                    alert('Movie removed from favorites!');
                    const button = $(`.remove-favorite[data-id='${movieId}']`);
                    button.removeClass('btn-danger remove-favorite').addClass('btn-primary add-favorite').text('Add to Favorites');

                    button.off('click').on('click', function() {
                        addFavorite(movieId);
                    });
                },
                error: function(xhr) {
                    alert('Error removing movie from favorites: ' + xhr.responseText);
                }
            });
        }

        // Logika infinite scroll
        let page = 1;
        let loading = false;

        $(window).scroll(function() {
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100 && !loading) {
                loading = true;
                $('#loading').show();
                page++;

                $.ajax({
                    url: '/movies',
                    method: 'GET',
                    data: {
                        page: page,
                        search: '{{ request()->input('search', 'movie') }}'
                    },
                    success: function(data) {
                        if (data.length) {
                            data.forEach(function(movie) {
                                $('#movie-list').append(`
                                    <div class="col-md-3 mb-4 movie-card">
                                        <div class="card">
                                            <a href="/movies/${movie.imdbID}">
                                                <img src="${movie.Poster}" class="card-img-top" alt="${movie.Title} Poster" loading="lazy">
                                            </a>
                                            <div class="card-body">
                                                <h5 class="card-title">${movie.Title}</h5>
                                                <p class="card-text"><strong>Year:</strong> ${movie.Year || 'N/A'}</p>
                                                <button class="btn btn-primary add-favorite" data-id="${movie.imdbID}">Add to Favorites</button>
                                            </div>
                                        </div>
                                    </div>
                                `);
                            });
                        } else {
                            $(window).off('scroll');
                        }
                    },
                    error: function() {
                        alert('Error loading movies');
                    },
                    complete: function() {
                        $('#loading').hide();
                        loading = false;
                    }
                });
            }
        });
    });
</script>
@endsection
