<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $table = 'authors';

    protected $fillable = [
        'id',
        'name',
        'first_surname',
        'second_surname',
    ];

    public $timestamps = false;

    public function books(){
        //return $this->BelongsToMany(Book::class,'id','author_id');
        return $this->belongsToMany(
            Book::class, //tabla de relacion
            'authors_books', //tala pivote o interseccion
            'authors_id', //from
            'books_id');///to
    }
}
