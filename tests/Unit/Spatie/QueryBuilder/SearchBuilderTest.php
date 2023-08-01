<?php

declare(strict_types=1);

namespace Tests\Unit\Spatie\QueryBuilder;

use Coddin\SpatieQueryBuilder\Spatie\QueryBuilder\SearchBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Filters\FiltersCallback;
use Tests\TestCase;

#[CoversClass(SearchBuilder::class)]
final class SearchBuilderTest extends TestCase
{
    #[Test]
    public function it_throws_a_LogicException_on_incorrect_usage(): void
    {
        self::expectException(\LogicException::class);

        $searchBuilder = new SearchBuilder();
        $searchBuilder->create([]);
    }

    /**
     * @throws \ReflectionException
     */
    #[Test]
    public function it_returns_an_AllowedFilter(): void
    {
        $searchBuilder = new SearchBuilder();
        $allowedFilter = $searchBuilder->create([
            'column1',
            'column2',
        ]);

        /** @phpstan-ignore-next-line */
        self::assertInstanceOf(AllowedFilter::class, $allowedFilter);
        self::assertSame('search', $allowedFilter->getName());

        $filterClass = $this->getValueForInaccessibleProperty($allowedFilter, 'filterClass');
        self::assertInstanceOf(FiltersCallback::class, $filterClass);

        $filterClassCallback = $this->getValueForInaccessibleProperty($filterClass, 'callback');
        $reflectionCallback = new \ReflectionFunction($filterClassCallback);

        self::assertArrayHasKey('columns', $reflectionCallback->getStaticVariables());
        self::assertSame(['column1', 'column2'], $reflectionCallback->getStaticVariables()['columns']);
    }
}
