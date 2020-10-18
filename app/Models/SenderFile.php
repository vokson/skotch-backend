<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SenderFile extends Model
{
    use HasFactory;

    protected $dateFormat = 'U';
    protected $table = 'sender_files';
}
