<?php
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Rute untuk halaman login
Route::get('login', 'AuthController@showLoginForm')->name('login')->middleware('guest');
Route::post('login', 'AuthController@login')->middleware('guest');
Route::get('logout', 'AuthController@logout')->name('logout');

// Routes for the movies page with auth middleware
Route::get('/movies', 'MovieController@index')->name('movies.index')->middleware('auth');
Route::get('/movies/favorites', 'MovieController@favorites')->name('movies.favorites')->middleware('auth');
Route::get('/movies/{id}', 'MovieController@show')->name('movies.show')->middleware('auth');
Route::post('/movies/{id}/favorite', 'MovieController@addToFavorites')->name('movies.addFavorite')->middleware('auth');
Route::post('/movies/{id}/remove-favorite', 'MovieController@removeFromFavorites')->name('movies.removeFavorite')->middleware('auth');

// Rute untuk halaman utama yang mengarahkan ke halaman login
Route::get('/', function () {
    return redirect()->route('login');
});