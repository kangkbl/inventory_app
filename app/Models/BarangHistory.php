<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'barang_id',
        'updated_by',
        'action',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}