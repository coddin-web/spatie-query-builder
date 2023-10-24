<?php

declare(strict_types=1);

namespace Coddin\SpatieQueryBuilder\Http\Requests;

use Coddin\SpatieQueryBuilder\Spatie\QueryBuilder\SearchBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @template T as Model
 */
final readonly class SpatieQueryBuilder
{
    public function __construct(
        private SearchBuilder $searchBuilder,
    ) {
    }

    /**
     * @param class-string<Model> $model
     * @param array<int, string|AllowedFilter> $filters
     * @param array<int, string|AllowedSort> $sorts
     * @param array<int, string> $search
     * @param callable|null $callback
     * @return Collection<int, T>
     */
    public function collect(
        string $model,
        array $filters = [],
        array $sorts = [],
        array $search = [],
        callable $callback = null,
    ): Collection {
        $this->validateModel($model);

        /* @phpstan-ignore-next-line */
        return $this
            ->getQueryBuilder($model, $filters, $sorts, $search, $callback)
            ->get();
    }

    /**
     * @param class-string<Model> $model
     * @param array<int, string|AllowedFilter> $filters
     * @param array<int, string|AllowedSort> $sorts
     * @param array<int, string> $search
     * @param callable|null $callback
     * @return LengthAwarePaginator<T>
     */
    public function paginate(
        string $model,
        array $filters = [],
        array $sorts = [],
        array $search = [],
        callable $callback = null,
    ): LengthAwarePaginator {
        $this->validateModel($model);

        $paginate = config('data-table.pagination.per_page');
        if (!\is_int($paginate)) {
            throw new \LogicException('Incorrect pagination configuration');
        }

        /* @phpstan-ignore-next-line */
        return $this
            ->getQueryBuilder($model, $filters, $sorts, $search, $callback)
            ->paginate($paginate)
            ->onEachSide(1);
    }

    /**
     * @param class-string<Model> $model
     */
    private function validateModel(string $model): void
    {
        if (!\is_a($model, Model::class, true)) {
            throw new \LogicException('Given class should be a Model');
        }
    }

    /**
     * @param class-string<Model> $model
     * @param array<int, string|AllowedFilter> $filters
     * @param array<int, string|AllowedSort> $sorts
     * @param array<int, string> $search
     */
    private function getQueryBuilder(
        string $model,
        array $filters = [],
        array $sorts = [],
        array $search = [],
        callable $callback = null,
    ): QueryBuilder {
        $exactFilters = [];
        $customFilters = [];

        foreach ($filters as $filter) {
            if ($filter instanceof AllowedFilter) {
                $customFilters[] = $filter;

                continue;
            }

            $exactFilters[] = AllowedFilter::exact(Str::camel($filter), Str::snake($filter));
        }

        $aliasedSorts = [];
        $customSorts = [];
        foreach ($sorts as $sort) {
            if ($sort instanceof AllowedSort) {
                $customSorts[] = $sort;

                continue;
            }

            $aliasedSorts[] = AllowedSort::field(Str::camel($sort), Str::snake($sort));
        }

        $mergedSorts = [
            ...$aliasedSorts,
            ...$customSorts,
        ];

        if (\count($search) > 0) {
            $search = [$this->searchBuilder->create($search)];
        }

        $mergedFilters = [
            ...$exactFilters,
            ...$customFilters,
            ...$search,
        ];

        $mergedFilters = \array_filter($mergedFilters);

        $queryBuilder = QueryBuilder::for($model)
            ->allowedFilters($mergedFilters)
            ->allowedSorts($mergedSorts);

        if (is_callable($callback)) {
            $queryBuilder = $callback($queryBuilder);
        }

        return $queryBuilder;
    }
}
