<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushedMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'icon',
        'url',
        // Optionally, you might add a status field (e.g., sent, failed) or other metadata.
    ];
}
