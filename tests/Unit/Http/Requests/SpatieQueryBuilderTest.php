<?php

/**
 * @noinspection PhpMissingFieldTypeInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use Coddin\SpatieQueryBuilder\Http\Requests\SpatieQueryBuilder;
use Coddin\SpatieQueryBuilder\Spatie\QueryBuilder\SearchBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Tests\Models\User;
use Tests\TestCase;

#[CoversClass(SpatieQueryBuilder::class)]
final class SpatieQueryBuilderTest extends TestCase
{
    /** @var SearchBuilder&MockObject $searchBuilder */
    private $searchBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        /** @phpstan-ignore-next-line */
        $this->searchBuilder = self::createMock(SearchBuilder::class);
    }

    #[Test]
    public function it_expects_a_model_on_collect(): void
    {
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Given class should be a Model');

        $spatieQueryBuilder = $this->createSpatieQueryBuilder();
        $spatieQueryBuilder->collect(
        /** @phpstan-ignore-next-line */
            'NotAModel',
        );
    }

    #[Test]
    public function it_expects_a_model_on_paginate(): void
    {
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Given class should be a Model');

        $spatieQueryBuilder = $this->createSpatieQueryBuilder();
        $spatieQueryBuilder->paginate(
        /** @phpstan-ignore-next-line */
            'NotAModel',
        );
    }

    #[Test]
    public function it_expects_correctly_configured_pagination_values(): void
    {
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Incorrect pagination configuration');

        $this->app['config']->set('data-table.pagination.per_page', 'not_an_integer');

        $spatieQueryBuilder = $this->createSpatieQueryBuilder();
        $spatieQueryBuilder->paginate(
            User::class,
        );
    }

    #[Test]
    public function it_uses_filters_sorts_and_search_correctly_on_collect(): void
    {
        $this->searchBuilder
            ->expects(self::once())
            ->method('create')
            ->with(['name']);

        $spatieQueryBuilder = $this->createSpatieQueryBuilder();
        $queryBuilder = $spatieQueryBuilder->collect(
            model: User::class,
            filters: ['name'],
            sorts: ['name'],
            search: ['name'],
        );

        self::assertInstanceOf(Collection::class, $queryBuilder);
    }

    #[Test]
    public function it_uses_filters_sorts_and_search_correctly_on_paginate(): void
    {
        $this->searchBuilder
            ->expects(self::once())
            ->method('create')
            ->with(['name']);

        $spatieQueryBuilder = $this->createSpatieQueryBuilder();
        $queryBuilder = $spatieQueryBuilder->paginate(
            model: User::class,
            filters: ['name'],
            sorts: ['name'],
            search: ['name'],
        );

        self::assertInstanceOf(LengthAwarePaginator::class, $queryBuilder);
    }

    #[Test]
    public function it_uses_the_callback_functionality_on_collect(): void
    {
        $this->searchBuilder->expects(self::once())
            ->method('create')
            ->with(['name']);

        /** @var callable&MockObject $callback */
        $callback = self::getMockBuilder(\stdClass::class)
            ->addMethods(['__invoke'])
            ->getMock();
        $callback->expects(self::once())
            ->method('__invoke')
            ->willReturnArgument(0);

        $spatieQueryBuilder = $this->createSpatieQueryBuilder();
        $queryBuilder = $spatieQueryBuilder->collect(
            model: User::class,
            filters: ['name'],
            sorts: ['name'],
            search: ['name'],
            callback: $callback,
        );

        self::assertInstanceOf(Collection::class, $queryBuilder);
    }

    #[Test]
    public function it_uses_the_callback_functionality_on_paginate(): void
    {
        $this->searchBuilder->expects(self::once())
            ->method('create')
            ->with(['name']);

        /** @var callable&MockObject $callback */
        $callback = self::getMockBuilder(\stdClass::class)
            ->addMethods(['__invoke'])
            ->getMock();
        $callback->expects(self::once())
            ->method('__invoke')
            ->willReturnArgument(0);

        $spatieQueryBuilder = $this->createSpatieQueryBuilder();
        $queryBuilder = $spatieQueryBuilder->paginate(
            model: User::class,
            filters: ['name'],
            sorts: ['name'],
            search: ['name'],
            callback: $callback,
        );

        self::assertInstanceOf(LengthAwarePaginator::class, $queryBuilder);
    }

    #[Test]
    public function it_uses_an_allowed_filter_instead_of_strings(): void
    {
        $this->searchBuilder
            ->expects(self::once())
            ->method('create')
            ->with(['name']);

        $spatieQueryBuilder = $this->createSpatieQueryBuilder();
        $queryBuilder = $spatieQueryBuilder->collect(
            model: User::class,
            filters: ['name', AllowedFilter::exact('not_name')],
            sorts: ['name'],
            search: ['name'],
        );

        self::assertInstanceOf(Collection::class, $queryBuilder);
    }

    #[Test]
    public function it_uses_an_allowed_sort_instead_of_strings(): void
    {
        $this->searchBuilder
            ->expects(self::once())
            ->method('create')
            ->with(['name']);

        $spatieQueryBuilder = $this->createSpatieQueryBuilder();
        $queryBuilder = $spatieQueryBuilder->collect(
            model: User::class,
            filters: ['name', AllowedFilter::exact('not_name')],
            sorts: ['name', AllowedSort::field('not_name', 'maybe_a_name')],
            search: ['name'],
        );

        self::assertInstanceOf(Collection::class, $queryBuilder);
    }

    /**
     * @return SpatieQueryBuilder<\Illuminate\Database\Eloquent\Model>
     */
    private function createSpatieQueryBuilder(): SpatieQueryBuilder
    {
        return new SpatieQueryBuilder($this->searchBuilder);
    }
}
