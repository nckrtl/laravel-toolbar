<?php

declare(strict_types=1);

namespace NckRtl\Toolbar\Support;

/**
 * The PHP settings and, under PHP-FPM, the pool status a developer looks for when a
 * request is slow, too large or out of workers. Read-only; pool settings are read once
 * per worker from the FPM pool file.
 */
final class PhpRuntimeDetails
{
    /** ini settings shown in the PHP tool, in display order. */
    private const SETTINGS = [
        'post_max_size',
        'upload_max_filesize',
        'max_file_uploads',
        'max_input_vars',
        'max_input_time',
        'default_socket_timeout',
        'display_errors',
        'error_reporting',
        'log_errors',
        'date.timezone',
        'realpath_cache_size',
        'opcache.enable',
        'opcache.memory_consumption',
        'opcache.validate_timestamps',
        'opcache.jit',
        'opcache.jit_buffer_size',
    ];

    /** Pool directives shown in the PHP-FPM tool. */
    private const POOL_SETTINGS = [
        'pm',
        'pm.max_children',
        'pm.start_servers',
        'pm.min_spare_servers',
        'pm.max_spare_servers',
        'pm.max_requests',
        'pm.process_idle_timeout',
        'request_terminate_timeout',
        'request_slowlog_timeout',
        'listen',
    ];

    /** @var array<string, array<string, string>|null> Pool settings per pool name, per worker. */
    private static array $poolSettings = [];

    /**
     * @return array<string, string|null>
     */
    public static function settings(): array
    {
        $settings = [];

        foreach (self::SETTINGS as $name) {
            $value = ini_get($name);
            $settings[$name] = $value === false ? null : $value;
        }

        return $settings;
    }

    /**
     * @return array{enabled: bool, memory_used: ?int, memory_free: ?int, hit_rate: ?float, cached_scripts: ?int}|null
     */
    public static function opcache(): ?array
    {
        if (! function_exists('opcache_get_status')) {
            return null;
        }

        try {
            $status = self::map(@opcache_get_status(false));
        } catch (\Throwable) {
            return null;
        }

        if ($status === null) {
            return ['enabled' => false, 'memory_used' => null, 'memory_free' => null, 'hit_rate' => null, 'cached_scripts' => null];
        }

        return [
            'enabled' => (bool) ($status['opcache_enabled'] ?? false),
            'memory_used' => self::int($status['memory_usage']['used_memory'] ?? null),
            'memory_free' => self::int($status['memory_usage']['free_memory'] ?? null),
            'hit_rate' => is_numeric($status['opcache_statistics']['opcache_hit_rate'] ?? null)
                ? round((float) $status['opcache_statistics']['opcache_hit_rate'], 1)
                : null,
            'cached_scripts' => self::int($status['opcache_statistics']['num_cached_scripts'] ?? null),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function extensions(): array
    {
        $extensions = get_loaded_extensions();
        natcasesort($extensions);

        return array_values($extensions);
    }

    /**
     * The FPM pool's live status and its process manager settings; null outside PHP-FPM.
     *
     * @return array{pool: ?string, process_manager: ?string, start_since: ?int, accepted_conn: ?int, listen_queue: ?int, max_listen_queue: ?int, idle_processes: ?int, active_processes: ?int, total_processes: ?int, max_active_processes: ?int, max_children_reached: ?int, slow_requests: ?int, settings: array<string, string>|null}|null
     */
    public static function fpm(): ?array
    {
        if (! function_exists('fpm_get_status')) {
            return null;
        }

        try {
            // Keys differ between PHP versions; read them defensively.
            $status = self::map(fpm_get_status());
        } catch (\Throwable) {
            return null;
        }

        if ($status === null) {
            return null;
        }

        $pool = is_string($status['pool'] ?? null) ? $status['pool'] : null;

        return [
            'pool' => $pool,
            'process_manager' => is_string($status['process-manager'] ?? null) ? $status['process-manager'] : null,
            'start_since' => self::int($status['start-since'] ?? null),
            'accepted_conn' => self::int($status['accepted-conn'] ?? null),
            'listen_queue' => self::int($status['listen-queue'] ?? null),
            'max_listen_queue' => self::int($status['max-listen-queue'] ?? null),
            'idle_processes' => self::int($status['idle-processes'] ?? null),
            'active_processes' => self::int($status['active-processes'] ?? null),
            'total_processes' => self::int($status['total-processes'] ?? null),
            'max_active_processes' => self::int($status['max-active-processes'] ?? null),
            'max_children_reached' => self::int($status['max-children-reached'] ?? null),
            'slow_requests' => self::int($status['slow-requests'] ?? null),
            'settings' => $pool === null ? null : self::poolSettings($pool),
        ];
    }

    /**
     * Reads `[pool]` from the FPM pool files next to the loaded php.ini (Debian `pool.d`,
     * RHEL and Homebrew `php-fpm.d`). Unreadable or missing files give null.
     *
     * @return array<string, string>|null
     */
    private static function poolSettings(string $pool): ?array
    {
        if (array_key_exists($pool, self::$poolSettings)) {
            return self::$poolSettings[$pool];
        }

        $settings = null;
        $ini = php_ini_loaded_file();

        if (is_string($ini)) {
            $files = array_merge(
                glob(dirname($ini).'/pool.d/*.conf') ?: [],
                glob(dirname($ini).'/php-fpm.d/*.conf') ?: [],
            );

            foreach ($files as $file) {
                $sections = is_readable($file) ? @parse_ini_file($file, true, INI_SCANNER_RAW) : false;

                if (is_array($sections) && is_array($sections[$pool] ?? null)) {
                    $settings = [];

                    foreach (self::POOL_SETTINGS as $name) {
                        if (isset($sections[$pool][$name]) && is_scalar($sections[$pool][$name])) {
                            $settings[$name] = (string) $sections[$pool][$name];
                        }
                    }

                    break;
                }
            }
        }

        return self::$poolSettings[$pool] = $settings;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function map(mixed $value): ?array
    {
        return is_array($value) ? $value : null;
    }

    private static function int(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
