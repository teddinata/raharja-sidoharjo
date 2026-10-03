<?php

namespace App\Models;

use App\Support\WilayahRtRw;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['rukun_warga_id', 'nomor', 'nama_ketua'])]
class RukunTetangga extends Model
{
    protected $table = 'rukun_tetangga';

    protected static function booted(): void
    {
        static::saved(fn () => WilayahRtRw::lupakan());
        static::deleted(fn () => WilayahRtRw::lupakan());
    }

    public function rukunWarga()
    {
        return $this->belongsTo(RukunWarga::class);
    }
}
