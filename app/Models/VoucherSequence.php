<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherSequence extends Model
{
    protected $fillable = [
        'module',
        'voucher_type',
        'year_code',
        'next_number',
    ];
}
