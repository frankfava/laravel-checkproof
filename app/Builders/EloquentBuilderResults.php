<?php

namespace App\Builders;

use App\Builders\EloquentBuilder as Builder;
use Closure;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;

class EloquentBuilderResults
{
    use ForwardsCalls;

    /**
     * QueryBuilder
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    private $builder;

    /**
     * Per Page
     */
    private ?int $perPage;

    /**
     * Page
     */
    private ?int $page;

    /**
     * Limit
     */
    private ?int $limit;

    /**
     * Modify Result
     */
    private ?Closure $modifyResult;

    /**
     * Map Items before Result
     */
    private ?Closure $mapItems;

    /**
     * Create
     */
    public function __construct(
        Builder $builder,
        ?int $perPage = null,
        ?int $page = null,
        ?int $limit = null,
        ?Closure $modifyResult = null,
        ?Closure $mapItems = null
    ) {
        $this->builder = $builder;

        $this->perPage($perPage)
            ->page($page)
            ->limit($limit)
            ->modifyResultWith($modifyResult)
            ->mapItemsWith($mapItems);
    }

    /**
     * Dynamically handle calls into the query instance.
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->builder, $method, $parameters);
    }

    /********
     * RESULTS
    ********/

    /**
     * Get Results as Collection or LengthAwarePaginator (if per page is set)
     */
    public function results($columns = ['*'])
    {
        return ! isset($this->perPage) || ! $this->perPage ?
            $this->collectResults($columns) :
            $this->paginateResults($columns);
    }

    /**
     * Get Paginated Results
     */
    protected function paginateResults($columns = ['*']): LengthAwarePaginator
    {
        $paginator = $this->builder->paginate(
            perPage : (int) $this->perPage,
            columns: $columns,
            page : (int) $this->page,
            total : $this->builder->count()
        );

        return $this->mapItems($this->modifyResults($paginator));
    }

    /**
     * Get Results as Collection
     */
    protected function collectResults($columns = ['*']): Collection
    {
        $limit = (int) $this->limit ??= null;
        if ($limit) {
            $this->builder->limit($limit);
        }

        return $this->mapItems($this->modifyResults($this->builder->get(columns : $columns)));
    }

    /**
     * Map Items before return
     */
    private function modifyResults(Collection|LengthAwarePaginator $data): Collection|LengthAwarePaginator
    {
        if (! $this->modifyResult instanceof Closure) {
            return $data;
        }

        $items = ($data instanceof LengthAwarePaginator) ? $data->getCollection() : $data;

        $items = call_user_func($this->modifyResult, $items);

        if ($data instanceof LengthAwarePaginator) {
            $data->setCollection($items);

            return $data;
        }

        return $items;
    }

    /**
     * Map Items before return
     */
    private function mapItems(Collection|LengthAwarePaginator $data): Collection|LengthAwarePaginator
    {
        if (! $this->mapItems instanceof Closure) {
            return $data;
        }

        $items = ($data instanceof LengthAwarePaginator) ? $data->getCollection() : $data;

        $items = $items->map(function ($item) {
            return call_user_func($this->mapItems, $item);
        });

        if ($data instanceof LengthAwarePaginator) {
            $data->setCollection($items);

            return $data;
        }

        return $items;
    }

    /********
     * MODIFY
    ********/

    public function limit(?int $limit = null): static
    {
        $this->limit = $limit;

        return $this;
    }

    public function perPage(?int $perPage = null): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function page(int $page = 1): static
    {
        $this->page = $page;

        return $this;
    }

    public function modifyResultWith(?Closure $closure = null): static
    {
        $this->modifyResult = $closure;

        return $this;
    }

    public function mapItemsWith(?Closure $closure = null): static
    {
        $this->mapItems = $closure;

        return $this;
    }
}
