<?php

declare(strict_types=1);

namespace Orchid\Screen\Actions;

use Orchid\Screen\Action;

/**
 * Class Link.
 *
 * @method Link name(string $name = null)
 * @method Link icon(string $icon = null)
 * @method Link class(string $classes = null)
 * @method Link target(string $target = null)
 * @method Link title(string $title = null)
 * @method Link download($download = true)
 */
class Link extends Action
{
    protected string $view = 'platform::actions.link';

    /**
     * Default attributes value.
     */
    protected array $attributes = [
        'class' => 'btn btn-link icon-link',
        'icon'  => null,
        'href'  => '#!',
        'turbo' => true,
    ];

    /**
     * Attributes available for a particular tag.
     */
    public array $inlineAttributes = [
        'autofocus',
        'disabled',
        'tabindex',
        'href',
        'target',
        'title',
        'download',
    ];

    public function href(string $link = ''): static
    {
        $this->set('href', $link);

        return $this;
    }

    public function route(string $name, mixed $parameters = [], bool $absolute = true): static
    {
        $route = route($name, $parameters, $absolute);

        return $this->href($route);
    }
}
