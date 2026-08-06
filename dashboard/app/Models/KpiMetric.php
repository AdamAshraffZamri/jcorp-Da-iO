<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiMetric extends Model
{
    protected $fillable = [
        'department',
        'metric_name',
        'target_value',
        'actual_value',
        'recorded_date',
    ];

    protected $casts = [
        'target_value'  => 'decimal:2',
        'actual_value'  => 'decimal:2',
        'recorded_date' => 'date',
    ];

    /**
     * A KPI metric can trigger many alerts.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
}
