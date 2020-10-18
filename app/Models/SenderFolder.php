<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SenderFolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'owner'
    ];

    protected $dateFormat = 'U';

    protected $table = 'sender_folders';
}
