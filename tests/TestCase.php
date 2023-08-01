<?php

declare(strict_types=1);

namespace Tests;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    final protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:.
        $app['config']->set('database.default', 'testbench');
        $app['config']->set(
            'database.connections.testbench',
            [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
        );

        // Setup Spatie Data config.
        $app['config']->set('data-table.pagination.per_page', 10);

        // Setup Spatie Query Builder config.
        $app['config']->set(
            'query-builder.parameters',
            [
                'include' => 'include',
                'filter' => 'filter',
                'sort' => 'sort',
                'fields' => 'fields',
                'append' => 'append',
            ],
        );
    }

    final protected function defineDatabaseMigrations(): void
    {
        $this->loadLaravelMigrations();
    }

    /**
     * @throws \ReflectionException
     */
    final protected function getValueForInaccessibleProperty(
        object $object,
        string $propertyName,
    ): mixed {
        $reflectedClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectedClass->getProperty($propertyName);

        return $reflectionProperty->getValue($object);
    }
}
