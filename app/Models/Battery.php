<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Battery extends Model
{
    protected $table = 'batteries';
    protected $fillable = [
        'level',
        'battery_id',
        'area',
        'line',
        'op_number',
        'machine_no',
        'machine_name',
        'maker',
        'equipment_type',
        'device',
        'battery_model',
        'battery_type',
        'exchange_type',
        'std_volt',
        'install_date',
        'replacement_cycle_month',
        'next_replace_date',
        'trend_pengganti',
        'status_aktif',
        'how_many',
        'number_of',
        'aggregate',
        'created_by',
    ];

    protected $casts = [
        'install_date' => 'date:Y-m-d',
        'next_replace_date' => 'date:Y-m-d',
        'replacement_cycle_month' => 'integer',
    ];

    /**
     * Relasi ke Input Battery (Data Operasional Sheet 2)
     */
    public function inputBatteries()
    {
        return $this->hasMany(InputBattery::class, 'battery_id');
    }
}
