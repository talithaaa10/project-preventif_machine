<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InputBattery extends Model
{
    protected $table = 'input_batteries';

    protected $guarded = [];

    protected $casts = [
        'last_day' => 'date:Y-m-d',
        'trend_pengganti' => 'date:Y-m-d',
    ];

    /**
     * Relasi ke Master Battery (Sheet 1)
     */
    public function battery()
    {
        return $this->belongsTo(Battery::class, 'battery_id');
    }
}
