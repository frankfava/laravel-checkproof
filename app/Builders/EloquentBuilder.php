<?php

namespace App\Builders;

use App\Builders\Decorators\EloquentBuilderDecorator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Extended Eloquent Builder
 *
 * Allows you to set options using helper methods, which is the applied to the query
 * Provide options to filter
 *
 * @example `User::query()->setOptions(['q' => 'needle', 'searchBy' => 'column', 'per_page' => 2 ])->get();`
 *
 * @method static EloquentBuilderDecorator setOptions(object|array $options)
 * @method static EloquentBuilderDecorator limit(?int $limit = null)
 * @method static EloquentBuilderDecorator perPage(?int $perPage = null)
 * @method static EloquentBuilderDecorator page(int $page = 1)
 * @method static EloquentBuilderDecorator excludeKey(int|string|array $keys = [])
 * @method static EloquentBuilderDecorator search(mixed $needle = null, null|string|array $searchBy = null, bool $exact = false)
 * @method static EloquentBuilderDecorator sort(string $key, bool $desc = false)
 * @method static EloquentBuilderDecorator editQuery(?\Closure $closure = null)
 * @method static EloquentBuilderDecorator modifyResult(?\Closure $closure = null)
 * @method static EloquentBuilderDecorator mapItems(?\Closure $closure = null)
 * @method static EloquentBuilderDecorator useRequest(\Illuminate\Http\Request $request)
 */
class EloquentBuilder extends Builder
{
    protected EloquentBuilderDecorator $decorator;

    protected bool $isDecorated = false;

    /** Construct the default builder and setup the decorator */
    public function __construct(QueryBuilder $query)
    {
        parent::__construct($query);
        $this->decorator = new EloquentBuilderDecorator($this);
    }

    /** If the method is in the decorator call that first */
    public function __call($method, $parameters)
    {
        if (method_exists($this->decorator, $method)) {
            $this->isDecorated = true;

            return call_user_func([$this->decorator, $method], ...$parameters);
        }

        // Return to Parent
        return parent::__call($method, $parameters);
    }

    /** Get Results */
    public function results()
    {
        // If decorator has been called then apply the options
        if ($this->isDecorated) {
            $this->decorator->applyOptionsToQuery($this);
        }

        return (new EloquentBuilderResults(
            builder : $this,
            perPage : $this->decorator->getOption('perPage'),
            page : $this->decorator->getOption('page'),
            limit : $this->decorator->getOption('limit'),
            modifyResult : $this->decorator->getOption('modifyResult'),
            mapItems : $this->decorator->getOption('mapItems'),
        ))->results();
    }

    /**
     * Search - Directly edits Query
     *
     * Takes a needle, the valid columns and searches in each column
     * if the column is a relationship then it will automatically join the table
     *
     * eg. ->searchBy('example.com', 'user.email')
     */
    public function searchBy(mixed $needle = null, null|string|array $searchBy = null, bool $exact = false): static
    {
        // Setup SearchBy if just string
        $searchBy = array_filter((! is_array($searchBy) ? [$searchBy] : $searchBy));

        // Search Keys
        $searchKeys = ! empty($searchBy) ? $searchBy : (array_keys((new $this->model)->getAttributes()));
        if (empty($searchKeys)) {
            return $this;
        }

        // Check if Needle is empty or wilcard
        if (! $needle || (is_string($needle) && $needle == '**')) {
            return $this;
        }

        // Join Related Tables
        $searchKeys = $this->joinRelatedTables($this, $searchKeys);

        // Needle as Array
        $needles = ! is_array($needle) ? explode(',', $needle) : $needle;
        $this->where(function ($subq) use ($searchKeys, $needles, $exact) {
            // Loop exact Key
            foreach ($searchKeys as $key) {
                $key = str($key)->lower()->snake()->toString();
                // Get Exact Match
                if ($exact) {
                    $subq->orWhereIn($key, $needles);
                }
                // Partial Match
                else {
                    foreach ($needles as $needle) {
                        $subq->orWhere($key, 'like', "%{$needle}%");
                    }
                }
            }
        });

        return $this;
    }

    /**
     * joinRelatedTables
     *
     * Takes an array of keys and joins related tables if needed
     * Supports dot notation for related tables on BelongsTo and HasMany relationships
     */
    private function joinRelatedTables(Builder $builder, array $keys): array
    {
        $newKeys = [];
        foreach ($keys as $key) {
            $newKey = [];
            if (count($exploded = explode('.', $key)) == 1) {
                $newKeys[] = $key;
            } else {
                foreach ($exploded as $key) {
                    if ($builder->getModel()->isRelation($key)) {
                        $relationship = ($model = $builder->getModel())->{$key}();
                        if (! in_array(class_basename($relationship), ['BelongsTo', 'HasMany'])) {
                            break;
                        }
                        /** @var Model $related */
                        $related = $relationship->getRelated();

                        $builder->distinct();

                        // Join
                        $builder->join(
                            $related->getTable(),
                            $model->getQualifiedKeyName(),
                            '=',
                            $relationship->getQualifiedForeignKeyName()
                        );
                        $newKey[] = $related->getTable();
                    } else {
                        $newKey[] = $key;
                    }
                }
                $newKeys[] = implode('.', $newKey);
            }
        }

        return array_unique(array_filter($newKeys));
    }
}
