<?php

namespace App\Builders\Decorators;

use App\Builders\EloquentBuilder as Builder;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Tappable;

/**
 * EloquentBuilderDecorator
 *
 * This class decorates an Eloquent Builder with features for pagination
 * It can also use the request to set the options for the builder
 */
class EloquentBuilderDecorator
{
    use ForwardsCalls,
        Tappable;

    /** CUSTOM QueryBuilder */
    private Builder $builder;

    /** Options that will be applied to the builder */
    private array $options = [];

    /** Request to use for options */
    private ?Request $request = null;

    /**
     * Create New builder Decorator
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    /** Forward calls to builder */
    public function __call($method, $parameters)
    {
        $this->applyOptionsToQuery();

        // Forward to Builder
        return $this->forwardDecoratedCallTo($this->builder, $method, $parameters);
    }

    /** Set Options */
    public function setOptions(object|array $options): static
    {
        $defaults = [
            // Pagination
            'perPage' => null,
            'page' => 1,
            'limit' => null,
            // Filter
            'sortBy' => null,
            'sortDesc' => null,
            'q' => null,
            'searchBy' => null,
            'searchExact' => null,
            'excludeKey' => null,
            // Modify
            'mapItems' => null,
            'modifyResult' => null,
            'editQuery' => null,
        ];

        if (isset($options['search'])) {
            $options['q'] = $options['search'];
            unset($options['search']);
        }

        $this->options = array_merge($defaults, (array) $this->options, array_intersect_key((array) $options, $defaults));

        return $this;
    }

    /** Get Options */
    public function getOptions(): array
    {
        return $this->options;
    }

    /** Get Single Option */
    public function getOption(string $key)
    {
        return $this->options[$key] ??= null;
    }

    /** Set Total Limit */
    public function total(?int $limit = null): static
    {
        return $this->setOptions(['limit' => $limit]);
    }

    /** Set Page Limit */
    public function perPage(?int $perPage = null): static
    {
        return $this->setOptions(['perPage' => $perPage]);
    }

    /** Set Page */
    public function page(int $page = 1): static
    {
        return $this->setOptions(['page' => $page]);
    }

    /** Exclude IDs */
    public function excludeKey(int|string|array $keys = []): static
    {
        return $this->setOptions(['excludeKey' => is_array($keys) ? $keys : [$keys]]);
    }

    /** Apply Search */
    public function search(mixed $needle = null, null|string|array $searchBy = null, bool $exact = false): static
    {
        return $this->setOptions([
            'q' => $needle,
            'searchBy' => $searchBy,
            'searchExact' => $exact,
        ]);
    }

    /** Apply Sort */
    public function sort(string $key, bool $desc = false): static
    {
        return $this->setOptions([
            'sortBy' => $key,
            'sortDesc' => $desc,
        ]);
    }

    /** Edit Query */
    public function editQuery(?Closure $closure = null): static
    {
        return $this->setOptions(['editQuery' => $closure]);
    }

    /** Modify Result */
    public function modifyResult(?Closure $closure = null): static
    {
        return $this->setOptions(['modifyResult' => $closure]);
    }

    /** Map Items */
    public function mapItems(?Closure $closure = null): static
    {
        return $this->setOptions(['mapItems' => $closure]);
    }

    // ============= Request

    /** Use a http Request with this builder to set Options */
    public function useRequest(Request $request): static
    {
        $this->request = $request;

        return $this;
    }

    /** Apply the request to options */
    protected function applyRequestToOptions()
    {
        if (! $this->request) {
            return $this;
        }

        $request = $this->request;

        $this->setOptions([
            'perPage' => ((int) $request->get('per_page', $this->options['perPage'] ?? null)) ?: null,
            'page' => ((int) $request->get('page', $this->options['page'] ?? null)) ?: null,
            'limit' => ((int) $request->get('limit', $this->options['limit'] ?? null)) ?: null,
            'sortBy' => $request->get('sortBy', $this->options['sortBy'] ?? null) ?: null,
            'sortDesc' => $request->get('sortDesc', $this->options['sortDesc'] ?? null) ?: null,
            'q' => $request->get('q', $request->get('search', $this->options['q'] ?? null)) ?: null,
            'searchBy' => $request->get('searchBy', $this->options['searchBy'] ?? null) ?: null,
            'searchExact' => (bool) $request->get('searchExact', $this->options['searchExact'] ?? null),
            'excludeKey' => $request->get('excludeKey', $this->options['excludeKey'] ?? null) ?: null,
        ]);

        return $this;
    }

    // ================= Applying Options

    /** Apply Options and pipe builder through edits */
    public function applyOptionsToQuery()
    {
        // If theres a http request, then map the options before apply
        $this->applyRequestToOptions();

        return app(Pipeline::class)
            ->send($this->builder)
            ->through(array_filter([
                $this->pipeSqlSearch(),
                $this->pipeSort(),
                $this->pipeByColumn(),
                $this->pipeExcludeKey(),
            ]))
            ->then(fn ($passable) => $passable);
    }

    /** Add SQL Search to Pipe */
    private function pipeSqlSearch(): ?callable
    {
        return function ($request, Closure $next) {
            /** @var Builder */
            $builder = $next($request);

            if (! $this->options['q']) {
                return $builder;
            }

            if (! $this->options['searchBy'] && method_exists($builder->getModel(), 'searchBy')) {
                $this->options['searchBy'] = $builder->getModel()->searchBy();
            }

            return $builder->searchBy(
                needle: $this->options['q'],
                searchBy: $this->options['searchBy'],
                exact: (bool) ($this->options['searchExact'] ??= null)
            );
        };
    }

    /** Match column value  */
    private function pipeByColumn(): ?callable
    {
        return function ($request, Closure $next) {
            $builder = $next($request);
            $columnKeys = collect($this->request)
                ->filter(fn ($v, $key) => str($key)->startsWith(['col:', 'column:', '-col:', '-column:']))
                ->map(function ($v, $k) {
                    return [
                        'column' => str($k)->afterLast(':')->toString(),
                        'operator' => Str::startsWith($k, '-') ? '!=' : '=',
                        'value' => $v,
                    ];
                })
                ->values()
                ->toArray();

            return $builder->where($columnKeys);
        };
    }

    /** Sort by column */
    private function pipeSort(): ?callable
    {
        return function ($request, Closure $next) {
            if (! $this->options['sortBy']) {
                return $next($request);
            }

            return $next($request)->orderBy(
                (string) $this->options['sortBy'],
                (bool) $this->options['sortDesc'] ? 'DESC' : 'ASC'
            );
        };
    }

    /** Match column value  */
    private function pipeExcludeKey(): ?callable
    {
        return function ($request, Closure $next) {
            return $next($request)->whereNotIn($this->builder->getModel()->getQualifiedKeyName(), $this->options['excludeKey'] ?? []);
        };
    }

    /** Edit Query */
    private function pipeFinalEdit(): ?callable
    {
        return function ($request, Closure $next) {
            $builder = $next($request);
            if (! ($this->options['editQuery'] ?? null) instanceof Closure) {
                return $builder;
            }

            return call_user_func($this->options['editQuery'], $builder);
        };
    }
}
