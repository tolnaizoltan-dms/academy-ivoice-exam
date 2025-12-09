<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Invoice Approval Process API
|
*/

// Invoice API v1
Route::prefix('v1')->group(function (): void {
    // Invoice endpoints
    // POST /api/v1/invoices - Submit a new invoice
    Route::post('/invoices', [InvoiceController::class, 'store'])
        ->name('invoices.store');

    // Approval endpoints
    // PUT /api/v1/approvals/{id}/approve - Approve an invoice
    Route::put('/approvals/{id}/approve', [ApprovalController::class, 'approve'])
        ->name('approvals.approve');

    // PUT /api/v1/approvals/{id}/reject - Reject an invoice
    Route::put('/approvals/{id}/reject', [ApprovalController::class, 'reject'])
        ->name('approvals.reject');
});
