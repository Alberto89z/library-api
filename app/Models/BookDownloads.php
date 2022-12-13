<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookDownloads extends Model
{
    use HasFactory;

    protected $table = 'book_downloads';
    protected $fillable = [
        'id',
        'total_downloads',
        'book_id'
    ];

    public $timestamps = false;
    
    public function book(){
        return $this->belongsTo(Book::class);
    }
}
