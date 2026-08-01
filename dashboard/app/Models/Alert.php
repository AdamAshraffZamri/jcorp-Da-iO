<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'kpi_metric_id',
        'title',
        'risk_score',
        'root_cause',
        'recommended_action',
    ];

    /**
     * An alert belongs to a single KPI metric.
     */
    public function kpiMetric(): BelongsTo
    {
        return $this->belongsTo(KpiMetric::class);
    }
}
