<?php

declare(strict_types=1);

namespace NckRtl\Toolbar\Support;

use Illuminate\Http\Request;

readonly class ProfileRequestContext
{
    public const SNAPSHOT_REQUEST_ID_ATTRIBUTE = 'toolbar.snapshot_request_id';

    public const RESOLVED_AUTH_MODE_ATTRIBUTE = 'toolbar.resolved_auth_mode';

    public const RESOLVED_AUTH_USER_ID_ATTRIBUTE = 'toolbar.resolved_auth_user_id';

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

    public static function setResolvedAuth(Request $request, string $authMode, mixed $userId = null): void
    {
        if (! in_array($authMode, ['first-user', 'user'], true) || $userId === null || $userId === '') {
            $request->attributes->set(self::RESOLVED_AUTH_MODE_ATTRIBUTE, 'guest');
            $request->attributes->set(self::RESOLVED_AUTH_USER_ID_ATTRIBUTE, null);

            return;
        }

        $request->attributes->set(self::RESOLVED_AUTH_MODE_ATTRIBUTE, $authMode);
        $request->attributes->set(self::RESOLVED_AUTH_USER_ID_ATTRIBUTE, $userId);
    }

    public static function resolvedAuthFromRequest(Request $request): array
    {
        $authMode = $request->attributes->get(self::RESOLVED_AUTH_MODE_ATTRIBUTE, 'guest');
        $userId = $request->attributes->get(self::RESOLVED_AUTH_USER_ID_ATTRIBUTE);

        if (! in_array($authMode, ['first-user', 'user'], true) || $userId === null || $userId === '') {
            return [
                'auth_mode' => 'guest',
                'auth_user_id' => null,
            ];
        }

        return [
            'auth_mode' => $authMode,
            'auth_user_id' => $userId,
        ];
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
