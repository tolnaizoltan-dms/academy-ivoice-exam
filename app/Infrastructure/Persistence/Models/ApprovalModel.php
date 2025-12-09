<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent Model for Approval persistence.
 *
 * This is a "dumb" persistence model - no business logic here.
 * All business logic stays in the Domain Aggregate.
 *
 * @property string $id
 * @property string $invoice_id
 * @property string $approver_id
 * @property string $status
 * @property string|null $rejection_reason
 * @property \Carbon\Carbon $started_at
 * @property \Carbon\Carbon|null $completed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ApprovalModel extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'approvals';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'invoice_id',
        'approver_id',
        'status',
        'rejection_reason',
        'started_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
