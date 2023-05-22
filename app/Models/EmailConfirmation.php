<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailConfirmation extends Model
{
    protected $table = 'tokens_users';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'token'
    ];
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
    public function setTokenAttribute($value)
    {
        $this->attributes['token'] = bcrypt($value);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
