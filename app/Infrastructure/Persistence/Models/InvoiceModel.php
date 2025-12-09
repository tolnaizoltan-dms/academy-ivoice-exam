<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent Model for Invoice persistence.
 *
 * This is a "dumb" persistence model - no business logic here.
 * All business logic stays in the Domain Aggregate.
 *
 * @property string $id
 * @property string $invoice_number
 * @property float $amount
 * @property string $submitter_id
 * @property string $supervisor_id
 * @property \Carbon\Carbon $submitted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class InvoiceModel extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'invoices';

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
        'invoice_number',
        'amount',
        'submitter_id',
        'supervisor_id',
        'submitted_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];
}
