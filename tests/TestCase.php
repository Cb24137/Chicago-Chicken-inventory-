<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // Safety net: RefreshDatabase empties the database, so refuse to run
    // unless tests use the separate MySQL test database from phpunit.xml
    // (its name must end in "_test").
    protected function setUpTraits()
    {
        $connection = config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        $isTestDatabase = in_array($connection, ['mysql', 'mariadb'], true)
            && str_ends_with($database, '_test');

        if (! $isTestDatabase) {
            throw new \RuntimeException(
                "Refusing to run tests against \"{$database}\". Tests must use the "
                . 'separate MySQL database from phpunit.xml (chicago_chicken_inventory_test), '
                . 'then run "php artisan config:clear".'
            );
        }

        return parent::setUpTraits();
    }
}
