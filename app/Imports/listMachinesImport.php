<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class listMachinesImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'List MC' => new ListMcSheetImport(),
        ];
    }
}