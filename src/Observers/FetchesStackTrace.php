<?php

namespace NckRtl\Toolbar\Observers;

use Illuminate\Support\Str;

trait FetchesStackTrace
{
    /**
     * Find the first frame in the stack trace outside of Telescope/Laravel.
     *
     * @param  string|array  $forgetLines
     */
    protected function getCallerFromStackTrace($forgetLines = 0): ?array
    {
        $trace = collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))->forget($forgetLines);

        return $trace->first(function ($frame) {
            if (! isset($frame['file'])) {
                return false;
            }

            return ! Str::contains($frame['file'], $this->ignoredPaths());
        });
    }

    /**
     * Get the file paths that should not be used by backtraces.
     */
    protected function ignoredPaths(): array
    {
        return array_merge(
            [base_path('vendor'.DIRECTORY_SEPARATOR.$this->ignoredVendorPath())],
            $this->options['ignore_paths'] ?? []
        );
    }

    /**
     * Choose the frame outside of either Telescope / Laravel or all packages.
     *
     * Returns empty string to ignore all vendor packages, or 'laravel' to only ignore Laravel packages.
     */
    protected function ignoredVendorPath(): string
    {
        // When ignore_packages is false, only ignore Laravel-specific vendor paths
        if (! ($this->options['ignore_packages'] ?? true)) {
            return 'laravel';
        }

        // Default: ignore all vendor packages (empty string matches all paths under vendor/)
        return '';
    }
}
