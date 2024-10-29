<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\MovieFavorite;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $apiKey = '37b0555d';
        $client = new Client();

        // Pagination setup
        $page = $request->input('page', 1);
        $moviesPerPage = 10; // Number of movies per page

        // Get search query from request
        $searchQuery = $request->input('search', 'movie'); // Default search term

        // Fetching movies from OMDb API with pagination
        $response = $client->get("http://www.omdbapi.com/", [
            'query' => [
                's' => $searchQuery, // Search for movies based on input
                'page' => $page, // Use requested page
                'apikey' => $apiKey,
            ]
        ]);
        
        $data = json_decode($response->getBody()->getContents(), true);
        $movies = isset($data['Search']) ? $data['Search'] : [];

        // If no movies found
        if (empty($movies)) {
            return view('movies.index', ['movies' => [], 'totalPages' => 0, 'page' => 1, 'search' => $searchQuery]);
        }

        // Total results for pagination
        $totalResults = (int) $data['totalResults'] ?? 0;
        $totalPages = ceil($totalResults / $moviesPerPage); // Calculate total pages

        return view('movies.index', compact('movies', 'totalPages', 'page', 'searchQuery'));
    }

    public function show($id)
    {
        $apiKey = '37b0555d';
        $client = new Client();

        // Fetching movie details from OMDb API
        $response = $client->get("http://www.omdbapi.com/", [
            'query' => [
                'i' => $id, // Use the IMDB ID to get movie details
                'apikey' => $apiKey,
            ]
        ]);

        $movieDetails = json_decode($response->getBody()->getContents(), true);

        // Return the view for movie details
        return view('movies.show', ['movie' => $movieDetails]);
    }

    public function favorites()
    {
        $user = auth()->user();
        $favorites = MovieFavorite::where('user_id', $user->id)->get();
        
        // Ambil detail film berdasarkan imdbID
        $movies = []; // Array untuk menyimpan detail film

        foreach ($favorites as $favorite) {
            $movieData = $this->getMovieDataByImdbID($favorite->imdbID); // Call the updated method
            if ($movieData) {
                $movies[] = $movieData; // Save movie data to the array
            }
        }

        return view('movies.favorites', compact('movies')); // Pass movie data to the view
    }

    private function getMovieDataByImdbID($imdbID)
    {
        $apiKey = '37b0555d'; // Replace with your actual OMDb API key
        $client = new Client(); // Create a new Guzzle client

        try {
            $response = $client->get("http://www.omdbapi.com/", [
                'query' => [
                    'i' => $imdbID, // Use 'i' for IMDb ID
                    'apikey' => $apiKey, // Include your API key
                ]
            ]);

            $movie = json_decode($response->getBody()->getContents(), true); // Decode the response body

            return $movie; // Return the movie data
        } catch (\Exception $e) {
            // Handle any errors here
            return null; // Return null if there's an error
        }
    }

    public function addToFavorites(Request $request)
    {
        $request->validate([
            'imdbID' => 'required|string',
        ]);

        $user = auth()->user(); // Dapatkan pengguna yang terautentikasi
        $movieId = $request->input('imdbID');

        // Misalkan Anda memiliki model MovieFavorite untuk menyimpan favorit
        $favorite = new MovieFavorite();
        $favorite->user_id = $user->id; // Simpan ID pengguna
        $favorite->imdbID = $movieId; // Simpan ID film
        $favorite->save();

        return response()->json(['success' => true]);
    }

    public function removeFromFavorites(Request $request, $id)
    {
        $user = auth()->user();
        // Assuming MovieFavorite is the model for favorite movies
        MovieFavorite::where('user_id', $user->id)->where('imdbID', $id)->delete();

        return response()->json(['success' => 'Movie removed from favorites!']);
    }
}
