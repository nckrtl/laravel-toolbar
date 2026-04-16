<?php

namespace NckRtl\Toolbar\Observers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Event;

class ViewObserver
{
    public ?string $viewName = null;

    public ?array $viewData = null;

    /**
     * Internal Laravel/Blade variables to exclude from view data.
     */
    private const INTERNAL_KEYS = [
        '__env',
        '__data',
        'errors',
        'obLevel',
        'app',
    ];

    public function __construct()
    {
        Event::listen('composing: *', function (string $event, array $payload) {
            $view = $payload[0] ?? null;

            if (! $view instanceof View) {
                return;
            }

            // Only capture the outermost (first) view, not nested partials/layouts
            if ($this->viewName !== null) {
                return;
            }

            $this->viewName = $view->name();
            $this->viewData = $this->filterViewData($view->getData());
        });
    }

    public function reset(): void
    {
        $this->viewName = null;
        $this->viewData = null;
    }

    private function filterViewData(array $data): array
    {
        return array_filter(
            $data,
            fn ($key) => ! in_array($key, self::INTERNAL_KEYS, true),
            ARRAY_FILTER_USE_KEY,
        );
    }
}
