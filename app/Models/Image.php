<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'path',
        'project_id',
        'gallery_id',
        'external_reference',

    ];

    // Relação com Project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    // Relação com Gallery
    public function gallery()
    {
        return $this->belongsTo(Gallery::class);
    }
}
