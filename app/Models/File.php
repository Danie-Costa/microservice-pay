<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'title',
        'path',
        'project_id',
        'external_reference',
    ];

    // Relação com Project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
