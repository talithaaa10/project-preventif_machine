<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    protected $table = 'machines';

    // WAJIB ADA: Agar Laravel mengizinkan semua kolom diisi secara otomatis
    protected $guarded = [];
}
