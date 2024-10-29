<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MovieFavorite extends Model
{
    protected $table = 'movie_favorites';

    protected $fillable = [
        'user_id',
        'imdbID'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
