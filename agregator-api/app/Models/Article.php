<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'content',
        'category',
        'published_at',
        'url',
        'source_name',
    ];

    protected $dates = ['published_at'];
}
