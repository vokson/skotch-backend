<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'uin', 'log', 'filename'
    ];

    protected $dateFormat = 'U';
    protected $table = 'uploaded_files';
}
