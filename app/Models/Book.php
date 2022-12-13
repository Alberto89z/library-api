<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $table = 'books';

    protected $fillable = [
        'id',
        'isbn',
        'title',
        'description',
        'published_date',
        'category_id',
        'editorial_id',
    ];

    public $timestamps = false;

    public function category(){
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function editorial(){
        return $this->belongsTo(Editorial::class, 'editorial_id', 'id');
    }
    public function authors(){
        return $this->belongsToMany(
            Author::class,//teake relationchip
            'authors_books', //table pibot o intersection
            'books_id', //from
            'authors_id' //to
        );
    }
    public function bookDownload(){
        return $this->hasOne(BookDownloads::class);
    }
}
/*
async function fetchImagesCats() {
    const response = await fetch('https://api.thecatapi.com/v1/images/search', {
        headers: { Accept: 'application/json' },
    });
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    return await response.json();
}

fetchImagesCats().then(data => {
    Object.entries(data).forEach(([key, value]) => {
        document.write(`<img src="${value.url}" alt="${value.id}"
      width="${value.width}" height="${value.height}">`);
    });
});





composer create-project laravel/laravel:^8.0 MyRestApi
*/
