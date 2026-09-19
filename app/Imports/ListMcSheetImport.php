<?php

namespace App\Imports;

use App\Models\Machine;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ListMcSheetImport implements ToModel, WithStartRow
{
    // Mulai baca dari baris ke-2 (melewati judul header di baris 1)
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        if (!isset($row[0]) && !isset($row[1])) {
            return null;
        }

        return new Machine([
        'hmmi'         => isset($row[0])  ? (string)$row[0]  : null,
        'op_no'        => isset($row[1])  ? (string)$row[1]  : null,
        'plant'        => isset($row[2])  ? (string)$row[2]  : null,
        'line'         => isset($row[3])  ? (string)$row[3]  : null,
        'product'      => isset($row[4])  ? (string)$row[4]  : null,
        'fungtion'     => isset($row[5])  ? (string)$row[5]  : null,
        'category'     => isset($row[6])  ? (string)$row[6]  : null,
        'mc_category'  => isset($row[7])  ? (string)$row[7]  : null,
        'maker'        => isset($row[8])  ? (string)$row[8]  : null,
        'model_type'   => isset($row[9])  ? (string)$row[9]  : null,
        'serial_no'    => isset($row[10]) ? (string)$row[10] : null,
        'year'         => isset($row[11]) ? (string)$row[11] : null,
        'contact'      => isset($row[12]) ? (string)$row[12] : null,
        'machine_made' => isset($row[13]) ? (string)$row[13] : null,
        ]);
    }
}
