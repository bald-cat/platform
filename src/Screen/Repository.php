<?php

declare(strict_types=1);

namespace Orchid\Screen;

use Countable;
use Illuminate\Support\Arr;

/**
 * Class Repository.
 */
class Repository extends \Illuminate\Config\Repository implements Countable
{
    protected int $position = 0;

    /**
     * Create a new configuration repository.
     *
     *
     * @return void
     */
    public function __construct(iterable $items = [])
    {
        $this->items = collect($items)->all();
    }

    public function getContent(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->items, $key, $default);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function set($key, $value = null): static
    {
        parent::set($key, $value);

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }
}
