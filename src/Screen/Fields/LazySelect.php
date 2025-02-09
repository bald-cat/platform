<?php

declare(strict_types=1);

namespace Orchid\Screen\Fields;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Orchid\Screen\Concerns\Multipliable;
use Orchid\Screen\Field;

/**
 * Class LazySelect.
 *
 * @method LazySelect accesskey($value = true)
 * @method LazySelect autofocus($value = true)
 * @method LazySelect disabled($value = true)
 * @method LazySelect form($value = true)
 * @method LazySelect name(string $value = null)
 * @method LazySelect required(bool $value = true)
 * @method LazySelect size($value = true)
 * @method LazySelect tabindex($value = true)
 * @method LazySelect help(string $value = null)
 * @method LazySelect popover(string $value = null)
 * @method LazySelect options($value = null)
 * @method LazySelect title(string $value = null)
 * @method LazySelect maximumSelectionLength(int $value = 0)
 * @method LazySelect allowAdd($value = true)
 */
class LazySelect extends Field
{
    use Multipliable;

    protected string $view = 'platform::fields.lazy-select';

    /**
     * Default attributes value.
     */
    protected array $attributes = [
        'class'        => 'form-control',
        'options'      => [],
        'allowEmpty'   => '',
        'allowAdd'     => false,
        'isOptionList' => false,
        'chunk'        => false,
    ];

    /**
     * Attributes available for a particular tag.
     */
    protected array $inlineAttributes = [
        'accesskey',
        'autofocus',
        'disabled',
        'form',
        'name',
        'required',
        'placeholder',
        'size',
        'tabindex',
        'tags',
        'maximumSelectionLength',
    ];

    public function __construct()
    {
        $this->addBeforeRender(function () {
            $isOptionList = array_is_list((array) $this->get('options', []));
            $this->set('isOptionList', $isOptionList);
        });
    }

    /**
     * @param string      $enum
     * @param string|null $displayName
     *
     * @throws \ReflectionException
     *
     * @return static
     */
    public function fromEnum(string $enum, ?string $displayName = null): static
    {
        if (!is_a($enum, \UnitEnum::class, true)) {
            throw new \InvalidArgumentException("Class '$enum' is not a valid enum.");
        }

        if (!method_exists($enum, 'cases')) {
            throw new \LogicException("Enum '$enum' does not have a 'cases' method.");
        }

        $options = [];
        foreach ($enum::cases() as $item) {
            $key = $this->getEnumKey($item);
            $options[$key] = ($displayName && method_exists($item, $displayName) && is_callable([$item, $displayName]))
                ? $item->$displayName()
                : __($item->name);
        }

        $this->set('options', $options);

        return $this->addBeforeRender(function () use ($enum) {
            $this->set('value', $this->normalizeEnumValue($this->get('value'), $enum));
        });
    }

    private function getEnumKey(\UnitEnum $enum): string|int
    {
        return $enum instanceof \BackedEnum ? $enum->value : $enum->name;
    }

    private function normalizeEnumValue(mixed $value, string $enum): array
    {
        return array_map(
            fn($item) => $item instanceof $enum ? $this->getEnumKey($item) : $item,
            (array) $value
        );
    }

    public function fromModel(string|Model|Builder $model, string $name, ?string $key = null): static
    {
        $model = is_object($model) ? $model : new $model;
        $key = $key ?? $model->getModel()->getKeyName();

        return $this->fromEloquent($model, $name, $key);
    }

    private function fromEloquent(Builder|Model $model, string $name, string $key): static
    {
        $options = $model
            ->when($this->get('chunk'), function ($query) {
                $query->limit($this->get('chunk'));
            })
            ->get()
            ->pluck($display = $this->getDisplayAppend($model, $name), $key);

        $this->set('options', $options);

        return $this->addBeforeRender(function () use ($display) {
            $value = [];

            collect($this->get('value'))->each(static function ($item) use (&$value, $display) {
                if (is_object($item)) {
                    $value[$item->id] = $item->$display;
                } else {
                    $value[] = $item;
                }
            });

            $this->set('value', $value);
        });

    }

    public function empty(string $name = '', string $key = ''): static
    {
        return $this->addBeforeRender(function () use ($name, $key) {
            $options = $this->get('options', []);

            if (! is_array($options)) {
                $options = $options->toArray();
            }

            $value = [$key => $name] + $options;

            $this->set('options', $value);
            $this->set('allowEmpty', '1');
        });
    }

    public function displayAppend(string $append): static
    {
        $this->set('displayAppend', Crypt::encryptString($append));

        return $this;
    }

    public function getDisplayAppend(Model|Builder $model, string $name): string
    {
        $append = $this->get('displayAppend');

        if (is_string($append)) {
            $append = Crypt::decryptString($append);
        }

        if ($model instanceof Builder) {
            $model = $model->getModel();
        }

        if (!empty($append) && $model->hasAttribute($append)) {
            return $append;
        }

        return $name;
    }

    public function chunk($chunk = 10): static
    {
        return $this->set('chunk', $chunk);
    }

    public function lazy(): static
    {
        return $this->set('lazy');
    }


    public function taggable(): static
    {
        return $this->set('tags');
    }

}
