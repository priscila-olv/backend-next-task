<?php

namespace App\Models;

use App\Models\InviteUserProject;
use App\Models\User;
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
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_projects', 'project_id', 'user_id');
    }
    public function inviteUserProjects()
    {
        return $this->hasMany(InviteUserProject::class, 'project_id');
    }
}