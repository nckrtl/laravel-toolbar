<?php

namespace NckRtl\Toolbar\Tests\Helpers;

use Illuminate\Http\Response;

class MockResponse
{
    public static function make(string $content = '<html><body></body></html>', int $status = 200, array $headers = []): Response
    {
        return new Response($content, $status, $headers);
    }
}
