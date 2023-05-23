<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['description', 'color', 'users_id'];
    public function sections()
    {
        return $this->hasMany(Section::class, 'projects_id');
    }
}