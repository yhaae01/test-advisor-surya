@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Movie List</h1>
        <div class="row">
            @foreach ($movies as $movie)
                <div class="col-md-3 mb-4">
                    <div class="card">
                        <a href="{{ route('movies.show', ['id' => $movie['imdbID']]) }}">
                            <img src="{{ $movie['Poster'] }}" class="card-img-top" alt="{{ $movie['Title'] }} Poster">
                        </a>
                        <div class="card-body">
                            <h5 class="card-title">{{ $movie['Title'] }}</h5>
                            <p class="card-text"><strong>Year:</strong> {{ $movie['Year'] ?? 'N/A' }}</p>
                            <p class="card-text"><strong>Genre:</strong> {{ $movie['Genre'] ?? 'N/A' }}</p>
                            <p class="card-text"><strong>Plot:</strong> {{ $movie['Plot'] ?? 'N/A' }}</p>
                            <button class="btn btn-primary add-favorite" data-id="{{ $movie['imdbID'] }}">Add to Favorites</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

<script>
    $(document).ready(function() {
        $('.add-favorite').click(function() {
            const movieId = $(this).data('id');

            $.ajax({
                url: `/movies/${movieId}/favorite`, // Sesuaikan URL dengan rute
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}', // CSRF token untuk Laravel
                    imdbID: movieId // Kirimkan imdbID
                },
                success: function(response) {
                    alert('Movie added to favorites!');
                },
                error: function(xhr) {
                    alert('Error adding movie to favorites: ' + xhr.responseText);
                }
            });
        });
    });

</script>
@endsection
