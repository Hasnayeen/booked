<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['name'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
