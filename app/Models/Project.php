<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'external_reference',
    ];

    // Relação com Gallery
    public function galleries()
    {
        return $this->hasMany(Gallery::class);
    }

    // Relação com Image
    public function images()
    {
        return $this->hasMany(Image::class);
    }
}
