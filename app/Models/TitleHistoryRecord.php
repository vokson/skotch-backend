<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TitleHistoryRecord extends Model
{
    use HasFactory;

    protected $table = 'titles_history';
    protected $dateFormat = 'U';
}
