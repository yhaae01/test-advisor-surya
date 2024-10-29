@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $movie['Title'] }}</h1>
    <img src="{{ $movie['Poster'] }}" alt="{{ $movie['Title'] }} Poster" loading="lazy" class="img-fluid mb-3">
    
    <p><strong>Year:</strong> {{ $movie['Year'] ?? 'N/A' }}</p>
    <p><strong>Genre:</strong> {{ $movie['Genre'] ?? 'N/A' }}</p>
    <p><strong>Plot:</strong> {{ $movie['Plot'] ?? 'N/A' }}</p>
    <p><strong>Director:</strong> {{ $movie['Director'] ?? 'N/A' }}</p>
    <p><strong>Actors:</strong> {{ $movie['Actors'] ?? 'N/A' }}</p>

    <button class="btn btn-primary add-favorite" data-id="{{ $movie['imdbID'] }}">Add to Favorites</button>
    <a href="{{ route('movies.index') }}" class="btn btn-secondary">Back to Movie List</a>
</div>

<script>
    $(document).ready(function() {
        $('.add-favorite').click(function() {
            const movieId = $(this).data('id'); // Get the movie ID

            $.ajax({
                url: `/movies/${movieId}/favorite`, // This should match your route definition
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content') // Fetch CSRF token from the meta tag
                },
                success: function(response) {
                    alert('Movie added to favorites!');

                    const button = $(this);
                    button.removeClass('btn-primary add-favorite')
                            .addClass('btn-danger remove-favorite')
                            .text('Remove from Favorites');

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
                url: `/movies/${movieId}/remove-favorite`, // Make sure this route exists
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content') // Fetch CSRF token from the meta tag
                },
                success: function(response) {
                    alert('Movie removed from favorites!');

                    const button = $('.remove-favorite[data-id="' + movieId + '"]');
                    button.removeClass('btn-danger remove-favorite')
                            .addClass('btn-primary add-favorite')
                            .text('Add to Favorites');

                    button.off('click').on('click', function() {
                        $('.add-favorite[data-id="' + movieId + '"]').click();
                    });
                },
                error: function(xhr) {
                    alert('Error removing movie from favorites: ' + xhr.responseText);
                }
            });
        }
    });
</script>

@endsection
