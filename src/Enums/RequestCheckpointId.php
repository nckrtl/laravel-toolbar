<?php

namespace NckRtl\Toolbar\Enums;

enum RequestCheckpointId: string
{
    case LARAVEL_START = 'laravel_start';
    case BEFORE_SERVICES_PROVIDERS = 'before_services_providers';
    case AFTER_SERVICES_PROVIDERS = 'after_services_providers';
    case BEFORE_ROUTING = 'before_routing';
    case AFTER_ROUTING = 'after_routing';
    case BEFORE_MIDDLEWARE = 'before_middleware';
    case AFTER_MIDDLEWARE = 'after_middleware';
    case BEFORE_CONTROLLER = 'before_controller';
    case BEFORE_VIEW_RENDERING = 'before_view_rendering';
    case AFTER_VIEW_RENDERING = 'after_view_rendering';
    case REQUEST_HANDLED = 'request_handled';
}
