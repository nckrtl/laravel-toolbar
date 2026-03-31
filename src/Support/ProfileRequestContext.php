<?php

declare(strict_types=1);

namespace NckRtl\Toolbar\Support;

use Illuminate\Http\Request;

readonly class ProfileRequestContext
{
    public function __construct(
        public ?string $requestId,
        public string $authMode,
        public ?string $userId,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            requestId: self::nullableHeaderValue($request, 'X-REQUEST-ID'),
            authMode: self::nullableHeaderValue($request, 'X-TOOLBAR-AUTH') ?? 'guest',
            userId: self::nullableHeaderValue($request, 'X-TOOLBAR-USER'),
        );
    }

    private static function nullableHeaderValue(Request $request, string $header): ?string
    {
        $value = $request->header($header);

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
