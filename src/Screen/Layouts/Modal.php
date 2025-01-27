<?php

declare(strict_types=1);

namespace Orchid\Screen\Layouts;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Orchid\Screen\Commander;
use Orchid\Screen\Layout;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Dashboard;

/**
 * Class Modal.
 */
class Modal extends Layout
{
    use Commander;

    public const SIZE_XL = 'modal-xl';
    public const SIZE_LG = 'modal-lg';
    public const SIZE_SM = 'modal-sm';

    public const TYPE_CENTER = '';
    public const TYPE_RIGHT = 'slide-right';

    /**
     * The modal window variation key,
     * for example, on the right, in the center.
     */
    protected string $type = self::TYPE_CENTER;

    /**
     * The size of the modal window,
     * for example, large or small.
     */
    protected string $size;

    /**
     * The name of the method to be called
     * for loading data when the modal is opened.
     */
    protected string $dataLoadingMethod;

    protected string $template = 'platform::layouts.modal';

    public function __construct(string $key, array $layouts = [])
    {
        $this->variables = [
            'apply'              => __('Apply'),
            'close'              => __('Close'),
            'size'               => '',
            'type'               => self::TYPE_CENTER,
            'key'                => $key,
            'title'              => $key,
            'turbo'              => true,
            'commandBar'         => [],
            'withoutApplyButton' => false,
            'withoutCloseButton' => false,
            'open'               => false,
            'method'             => null,
            'staticBackdrop'     => false,
        ];

        $this->layouts = $layouts;
    }

    public function getSlug(): string
    {
        return $this->variables['key'];
    }

    public function build(Repository $repository): ?View
    {
        $this->variables = array_merge($this->variables, [
            'deferredRoute'  => route('platform.async'),
            'deferrerParams' => $this->getDeferrerDataLoadingParameters(),
        ]);

        return $this->buildAsDeep($repository);
    }

    /**
     * Set the text button for apply action.
     */
    public function applyButton(string $text): static
    {
        $this->variables['apply'] = $text;

        return $this;
    }

    /**
     * Whether to disable the applied button or not.
     */
    public function withoutApplyButton(bool $withoutApplyButton = true): static
    {
        $this->variables['withoutApplyButton'] = $withoutApplyButton;

        return $this;
    }

    /**
     * Whether to disable the close button or not.
     */
    public function withoutCloseButton(bool $withoutCloseButton = true): static
    {
        $this->variables['withoutCloseButton'] = $withoutCloseButton;

        return $this;
    }

    /**
     * Set the text button for cancel action.
     */
    public function closeButton(string $text): static
    {
        $this->variables['close'] = $text;

        return $this;
    }

    /**
     * Set CSS class for size modal.
     */
    public function size(string $class): static
    {
        $this->variables['size'] = $class;

        return $this;
    }

    /**
     * Set CSS class for type modal.
     */
    public function type(string $class): static
    {
        $this->variables['type'] = $class;

        return $this;
    }

    /**
     * Set title for header modal.
     */
    public function title(string $title): static
    {
        $this->variables['title'] = $title;

        return $this;
    }

    public function rawClick(bool $status = false): static
    {
        $this->variables['turbo'] = $status;

        return $this;
    }

    public function open(bool $status = true): static
    {
        $this->variables['open'] = $status;

        return $this;
    }

    public function method(string $method): static
    {
        $this->variables['method'] = url()->current().'/'.$method;

        return $this;
    }

    public function staticBackdrop(bool $status = true): static
    {
        $this->variables['staticBackdrop'] = $status;

        return $this;
    }

    /**
     * This method is an alias for the `deferred` method with the `async` prefix.
     *
     * If the provided method name does not start with the `async` prefix, it will be automatically added.
     * Then the `deferred` method is called with the processed method name.
     *
     * @param string $method The name of the method to be called asynchronously.
     *
     * @return $this Returns the current instance of the object for method chaining.
     */
    public function async(string $method): static
    {
        // If the method name does not start with 'async', prepend the 'async' prefix.
        if (! Str::startsWith($method, 'async')) {
            $method = Str::start(Str::ucfirst($method), 'async');
        }

        return $this->deferred($method);
    }

    /**
     * This method sets the method to be called in a deferred manner.
     *
     * @param string $method The name of the method to be called later when needed.
     *
     * @return $this Returns the current instance of the object for method chaining.
     */
    public function deferred(string $method): static
    {
        $this->dataLoadingMethod = $method;

        return $this;
    }

    /**
     * Return the deferrer parameters for loading data from the browser.
     */
    protected function getDeferrerDataLoadingParameters(): array
    {
        $screen = Dashboard::getCurrentScreen();

        if (! $screen) {
            return [];
        }

        if (empty($this->dataLoadingMethod)) {
            return [];
        }

        return [
            '_screen'   => Crypt::encryptString(get_class($screen)),
            '_call'     => $this->dataLoadingMethod,
            '_template' => $this->getSlug(),
        ];
    }
}
