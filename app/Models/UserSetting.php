<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner', 'name', 'value', 'is_switchable'
    ];

    protected $dateFormat = 'U';

    protected $table = 'user_settings';
}
