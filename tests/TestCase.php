<?php

namespace Tests;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // phpunit.xml uses sqlite :memory: so tests never touch the DB from .env.
        // Dashboard and other code expect Spatie roles to exist when those tables are present.
        if (config('database.default') === 'sqlite' && Schema::hasTable('roles')) {
            $this->seed(RolesAndPermissionsSeeder::class);
        }
    }
}
