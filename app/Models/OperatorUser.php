<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class OperatorUser extends Pivot
{
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
