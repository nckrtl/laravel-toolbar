<?php

use NckRtl\Toolbar\Services\ProfilerService\Profiler;

if (! function_exists('profile')) {
    /**
     * Record a profile marker for substage profiling.
     *
     * This function allows you to track timing within a request stage (like a controller).
     * Each call creates a marker with the current timestamp and memory usage.
     * The time between consecutive markers becomes a substage.
     *
     * Example usage in a controller:
     *   profile('Fetching articles');
     *   $articles = Article::all();
     *   profile('Converting to DTOs');
     *   $dtos = $articles->map(fn($a) => ArticleDto::from($a));
     *   profile('Done');
     *
     * @param  string  $label  A descriptive label for this profile point
     */
    function profile(string $label): void
    {
        Profiler::profile($label);
    }
}
