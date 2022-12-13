<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookReviews extends Model
{
    use HasFactory;

    protected $table = 'book_reviews';

    protected $fillable = ['id', 'comment', 'edited', 'book_id', 'user_id'];

    public $timestamps = false;

    //faltan autores de belongsToMany
    public function books()
    {
        return $this->belongsTo(
            Book::class,
            'book_id', 'id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
