<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Listeners\StartApprovalProcessListener;
use App\Domain\Approval\Contracts\ApprovalRepositoryInterface;
use App\Domain\InvoiceReception\Contracts\InvoiceRepositoryInterface;
use App\Domain\InvoiceReception\Events\InvoiceSubmitted;
use App\Infrastructure\Persistence\Repositories\EloquentApprovalRepository;
use App\Infrastructure\Persistence\Repositories\EloquentInvoiceRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Repository Interfaces to Eloquent Implementations
        // This enables database persistence while keeping domain layer clean
        $this->app->singleton(
            InvoiceRepositoryInterface::class,
            EloquentInvoiceRepository::class
        );

        $this->app->singleton(
            ApprovalRepositoryInterface::class,
            EloquentApprovalRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Policy: InvoiceSubmitted → Start Approval Process
        Event::listen(
            InvoiceSubmitted::class,
            StartApprovalProcessListener::class,
        );
    }
}
