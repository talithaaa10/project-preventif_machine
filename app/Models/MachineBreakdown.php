<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineBreakdown extends Model
{
    use HasFactory;

    protected $table = 'machine_breakdowns';

    protected $guarded = [];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'duration' => 'float',
        'frequency' => 'float',
        'persen' => 'float',
        'target' => 'float',
    ];
}
