<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relation\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
       'owner_id',
    'name',
    'email',
    'website',
    'description',
    'logo',
    'location',
];

    public function owner()
{
    return $this->belongsTo(User::class, 'owner_id');
}
}
