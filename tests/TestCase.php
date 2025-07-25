<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app(Kernel::class)->call('db:seed', ['--class' => RolePermissionSeeder::class, '--no-interaction' => true]);
    }
}
