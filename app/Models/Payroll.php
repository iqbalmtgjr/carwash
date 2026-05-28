<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $table = 'payroll';

    protected $fillable = [
        'employee_id',
        'week_start',
        'week_end',
        'total_share',
        'base_salary',
        'attendance_deduction',
        'kasbon_deduction',
        'bonus',
        'total',
        'catatan',
    ];

    protected $casts = [
        'week_start' => 'date',
        'week_end'   => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
