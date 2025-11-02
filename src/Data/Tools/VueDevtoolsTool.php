<?php

namespace NckRtl\Toolbar\Data\Tools;

use Spatie\LaravelData\Data;

class VueDevtoolsTool extends Data implements ToolInterface
{
    public function component(): string
    {
        return 'VueDevtools';
    }

    public function additionalLightDomHtml(): ?string
    {
        if (app()->environment('production')) {
            return null;
        }

        return '<style>
      #__vue-devtools-container__ {
        opacity: 0 !important;
        pointer-events: none !important;
      }
    </style>';
    }
}
