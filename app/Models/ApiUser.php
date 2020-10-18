<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\ActionController;

class ApiUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'surname', 'email', 'password', 'updated_at'
    ];

    protected $hidden = [
        'password', 'access_token',
    ];

    public $timestamps = true;
    protected $dateFormat = 'U';

    public function mayDo(string $nameOfAction)
    {
        return ActionController::take($this->role, $nameOfAction);
    }
}
