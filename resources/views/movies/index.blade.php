@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Movie List</h1>
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
                },
                error: function(xhr) {
                    alert('Error adding movie to favorites: ' + xhr.responseText);
                }
            });
        });

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
                                                <img src="${movie.Poster}" class="card-img-top" alt="${movie.Title} Poster">
                                            </a>
                                            <div class="card-body">
                                                <h5 class="card-title">${movie.Title}</h5>
                                                <p class="card-text"><strong>Year:</strong> ${movie.Year || 'N/A'}</p>
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
