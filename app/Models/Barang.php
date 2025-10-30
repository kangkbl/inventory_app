<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
class Barang extends Model
{
    //
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_barang',
        'merk',
        'kode_barang_bmn',
        'kategori',
        'lokasi',
        'kondisi',
        'jumlah',
        'tahun_pengadaan',
        'keterangan',
        'photo_path',
        'updated_by',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    public function histories(): HasMany
    {
        return $this->hasMany(BarangHistory::class);
    }
    
    public function getPhotoUrlAttribute(): ?string
    {
        $path = $this->photo_path;

        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}