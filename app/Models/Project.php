<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'token',
    ];

    public function vendor()
    {
        return $this->hasMany(Vendor::class);
    }

    public function payment()
    {
        return $this->hasMany(Payment::class);
    }
}
