<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailInviteProject extends Model
{
    use HasFactory;
    protected $table = 'project_tokens';
    public $timestamps = false;
    protected $fillable = ['project_id', 'user_email', 'token'];
}