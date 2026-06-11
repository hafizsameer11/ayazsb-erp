<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeavingLoom extends Model
{
    protected $fillable = ['loom_no', 'name', 'loom_type', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
