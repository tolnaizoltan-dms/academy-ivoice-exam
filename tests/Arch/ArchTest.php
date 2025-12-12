<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->security();

/*
|--------------------------------------------------------------------------
| Domain Layer Architecture Tests
|--------------------------------------------------------------------------
*/

arch('domain layer should not depend on application layer')
    ->expect('App\Domain')
    ->not->toUse('App\Application');

arch('domain layer should not depend on infrastructure layer')
    ->expect('App\Domain')
    ->not->toUse('App\Infrastructure');

arch('domain layer should not depend on http layer')
    ->expect('App\Domain')
    ->not->toUse('App\Http');

arch('aggregates should extend AggregateRoot')
    ->expect('App\Domain\*\Aggregates')
    ->toExtend('App\Domain\Shared\AggregateRoot');

arch('domain exceptions should extend DomainException')
    ->expect('App\Domain\*\Exceptions')
    ->toExtend('App\Exceptions\DomainException');

/*
|--------------------------------------------------------------------------
| Application Layer Architecture Tests
|--------------------------------------------------------------------------
*/

arch('application layer should not depend on infrastructure layer')
    ->expect('App\Application')
    ->not->toUse('App\Infrastructure');

arch('application layer should not depend on http layer')
    ->expect('App\Application')
    ->not->toUse('App\Http');

arch('actions should have Action suffix')
    ->expect('App\Application\Actions')
    ->toHaveSuffix('Action');

arch('DTOs should be final and readonly')
    ->expect('App\Application\DTOs')
    ->toBeFinal()
    ->toBeReadonly();

arch('DTOs should have Data suffix')
    ->expect('App\Application\DTOs')
    ->toHaveSuffix('Data');

arch('listeners should have Listener suffix')
    ->expect('App\Application\Listeners')
    ->toHaveSuffix('Listener');

/*
|--------------------------------------------------------------------------
| Infrastructure Layer Architecture Tests
|--------------------------------------------------------------------------
*/

arch('eloquent repositories should have Repository suffix')
    ->expect('App\Infrastructure\Persistence\Repositories')
    ->toHaveSuffix('Repository');

arch('in-memory repositories should have Repository suffix')
    ->expect('App\Infrastructure\Repositories')
    ->toHaveSuffix('Repository');

arch('eloquent models should extend Eloquent Model')
    ->expect('App\Infrastructure\Persistence\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model');

arch('eloquent models should have Model suffix')
    ->expect('App\Infrastructure\Persistence\Models')
    ->toHaveSuffix('Model');

/*
|--------------------------------------------------------------------------
| HTTP Layer Architecture Tests
|--------------------------------------------------------------------------
*/

arch('controllers should have Controller suffix')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller');

arch('requests should have Request suffix')
    ->expect('App\Http\Requests')
    ->toHaveSuffix('Request');

arch('requests should extend FormRequest')
    ->expect('App\Http\Requests')
    ->toExtend('Illuminate\Foundation\Http\FormRequest');
