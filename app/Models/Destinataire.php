<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Destinataire extends Model
{
    protected $fillable = ['adhesion_id', 'email'];

    public function adhesion(): BelongsTo
    {
        return $this->belongsTo(Adhesion::class);
    }
}
