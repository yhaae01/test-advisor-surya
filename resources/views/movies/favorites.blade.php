@extends('layouts.app')

@section('content')
<div class="container">
    <h1>My Favorites</h1>
    <div class="row">
        @if(empty($movies))
            <p>You have no favorite movies.</p>
        @else
            @foreach($movies as $movie)
                <div class="col-md-3 mb-4">
                    <div class="card">
                        <a href="{{ route('movies.show', ['id' => $movie['imdbID']]) }}">
                            <img src="{{ $movie['Poster'] }}" class="card-img-top" alt="{{ $movie['Title'] }} Poster">
                        </a>
                        <div class="card-body">
                            <h5 class="card-title">{{ $movie['Title'] }}</h5>
                            <p class="card-text"><strong>Year:</strong> {{ $movie['Year'] ?? 'N/A' }}</p>
                            <button class="btn btn-danger remove-favorite" data-id="{{ $movie['imdbID'] }}">Remove from Favorites</button>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.remove-favorite').click(function() {
            const movieId = $(this).data('id'); // Get the movie ID

            $.ajax({
                url: `/movies/${movieId}/remove-favorite`, // Adjusted URL to match the route
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}' // CSRF token for Laravel
                },
                success: function(response) {
                    alert(response.success); // Show success message
                    location.reload(); // Reload the page to refresh favorites list
                },
                error: function(xhr) {
                    alert('Error removing movie from favorites: ' + xhr.responseText);
                }
            });
        });
    });
</script>

@endsection
