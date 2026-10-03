<?php

namespace App\Models;

use App\Support\WilayahRtRw;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nomor', 'pedukuhan', 'nama_ketua'])]
class RukunWarga extends Model
{
    protected $table = 'rukun_warga';

    protected static function booted(): void
    {
        static::saved(fn () => WilayahRtRw::lupakan());
        static::deleted(fn () => WilayahRtRw::lupakan());
    }

    public function rukunTetangga()
    {
        return $this->hasMany(RukunTetangga::class);
    }
}
