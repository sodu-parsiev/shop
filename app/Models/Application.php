<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['customer_name', 'company', 'email', 'phone', 'message', 'status', 'internal_notes', 'assigned_to'])]
class Application extends Model
{
    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
        ];
    }

    public function assignedManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
