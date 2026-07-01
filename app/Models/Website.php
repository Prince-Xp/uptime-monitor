<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;   
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Website extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'client_id',
        'url',
        'is_up',
        'last_checked_at',
        'last_alerted_at',
    ];

    protected $casts = [
        'is_up' => 'boolean',
        'last_checked_at' => 'datetime',
        'last_alerted_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
