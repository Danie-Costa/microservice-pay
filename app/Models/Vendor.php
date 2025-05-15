<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'name',
        'email',
        'mp_user_id',
        'mp_access_token',
        'mp_refresh_token',
        'mp_public_key',
        'mp_expires_in',
        'mp_token_created_at',
        'external_reference',
        'fee',
        'project_id'
    ];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function payment()
    {
        return $this->hasMany(Payment::class);
    }


}
