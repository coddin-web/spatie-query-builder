<?php

declare(strict_types=1);

namespace Coddin\SpatieQueryBuilder\Spatie\QueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;

final class SearchBuilder
{
    /**
     * @param array<int, string> $columns
     */
    public function create(array $columns): AllowedFilter
    {
        $columns = \array_values($columns);

        if (\count($columns) === 0) {
            throw new \LogicException('At least one column to search for is required.');
        }

        /** @var array<int, string> $columns */
        $columns = collect($columns)->map(fn (string $column) => Str::snake($column))->toArray();

        return AllowedFilter::callback(
            name: 'search',
            callback: function (Builder $query, $value) use ($columns): void {
                // @codeCoverageIgnoreStart
                $query->where(function (Builder $query) use ($columns, $value): void {
                    foreach ($columns as $column) {
                        if (\str_contains($column, '.')) {
                            list($model, $column) = \explode('.', $column, 2);
                            $column = Str::snake($column);

                            $query->orWhereHas(
                                relation: $model,
                                callback: fn(Builder $query) => $query
                                    ->where($column, 'LIKE', '%' . $value . '%'),
                            );

                            continue;
                        }

                        $query->orWhere($column, 'LIKE', '%' . $value . '%');
                    }
                });
                // @codeCoverageIgnoreEnd
            },
        );
    }
}
