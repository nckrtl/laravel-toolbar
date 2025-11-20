<?php

use Laravel\Mcp\Facades\Mcp;
use NckRtl\Toolbar\Mcp\Servers\DataServer;

Mcp::local('laravel-toolbar', DataServer::class);
