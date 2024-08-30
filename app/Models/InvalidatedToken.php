<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvalidatedToken extends Model
{
    use HasFactory;

    protected $fillable = ['jti', 'expired_time'];
}
