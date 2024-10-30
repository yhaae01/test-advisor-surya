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

        $page = $request->input('page', 1);
        $moviesPerPage = 10;
        $searchQuery = $request->input('search', 'movie');
        if (empty($searchQuery)) {
            $searchQuery = 'movie';
        }
        $year = $request->input('year');
        $type = $request->input('type');

        $queryParams = [
            's' => $searchQuery,
            'page' => $page,
            'apikey' => $apiKey,
        ];

        if ($year) {
            $queryParams['y'] = $year;
        }

        if ($type) {
            $queryParams['type'] = $type;
        }

        $response = $client->get("http://www.omdbapi.com/", [
            'query' => $queryParams
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        $movies = isset($data['Search']) ? $data['Search'] : [];

        if ($page == 1) {
            return view('movies.index', compact('movies', 'searchQuery', 'year', 'type'));
        }

        return response()->json($movies);
    }

    public function show($id)
    {
        $apiKey = '37b0555d';
        $client = new Client();

        try {
            $response = $client->get("http://www.omdbapi.com/", [
                'query' => [
                    'i' => $id,
                    'apikey' => $apiKey,
                ]
            ]);

            $movieDetails = json_decode($response->getBody()->getContents(), true);

            // Check if the movie was found
            if (isset($movieDetails['Response']) && $movieDetails['Response'] === 'True') {
                return view('movies.show', ['movie' => $movieDetails]);
            } else {
                return redirect()->route('movies.index')->with('error', 'Movie not found!');
            }
        } catch (\Exception $e) {
            return redirect()->route('movies.index')->with('error', 'An error occurred while fetching movie details. Please try again later.');
        }
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
