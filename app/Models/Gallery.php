<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $fillable = [
        'name',
        'description',
        'project_id',
        'external_reference',
    ];

    // Relação com Project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Relação com Image
    public function images()
    {
        return $this->hasMany(Image::class);
    }
}
