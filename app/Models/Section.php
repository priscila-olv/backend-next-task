<?php

namespace App\Models;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['description', 'projects_id'];
    public function tasks()
    {
        return $this->hasMany(Task::class, 'sections_id');
    }
    public function project()
    {
        return $this->belongsTo(Project::class, 'projects_id');
    }
}