<?php

namespace NckRtl\Toolbar\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProcessesController
{
    public function status(Request $request): JsonResponse
    {
        $domain = $request->query('domain');

        if (! $domain) {
            return response()->json([
                'available' => false,
                'reason' => 'No domain provided',
            ]);
        }

        $statePath = $this->resolveStatePath($domain);

        if (! $statePath || ! file_exists($statePath)) {
            return response()->json([
                'available' => false,
                'reason' => 'No process state found',
            ]);
        }

        $data = json_decode((string) file_get_contents($statePath), true);

        return response()->json([
            'available' => true,
            'processes' => $data['processes'] ?? [],
        ]);
    }

    public function stream(Request $request): StreamedResponse
    {
        $domain = $request->query('domain');

        $response = new StreamedResponse(function () use ($request, $domain): void {
            set_time_limit(0);

            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', false);

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            echo "retry: 3000\n\n";
            flush();

            $statePath = $this->resolveStatePath($domain ?? '');

            if (! $statePath) {
                echo "event: error\ndata: {\"reason\":\"Cannot resolve state path\"}\n\n";
                flush();

                return;
            }

            $lastMtime = (int) $request->header('Last-Event-ID', '0');
            $lastPing = time();

            while (true) {
                if (connection_aborted()) {
                    break;
                }

                if (file_exists($statePath)) {
                    clearstatcache(true, $statePath);
                    $mtime = filemtime($statePath);

                    if ($mtime > $lastMtime) {
                        $lastMtime = $mtime;
                        $data = file_get_contents($statePath);

                        if ($data !== false) {
                            // SSE data field must be a single line — collapse pretty-printed JSON
                            $compactData = json_encode(json_decode($data));

                            echo "id: {$mtime}\n";
                            echo "event: state-changed\n";
                            echo "data: {$compactData}\n";
                            echo "\n";
                            flush();
                        }
                    }
                }

                $now = time();
                if ($now - $lastPing >= 30) {
                    $lastPing = $now;
                    echo ": keepalive\n\n";
                    flush();
                }

                usleep(500_000);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    /**
     * Resolve the state.json path from a domain like "feature-auth.myapp.test".
     *
     * Orbit writes state to: ~/.config/orbit/processes/{app}/{branch}/state.json
     * Domain format: {branch}.{app}.{tld} (worktree) or {app}.{tld} (main app)
     */
    private function resolveStatePath(string $domain): ?string
    {
        $orbitDataPath = ($_SERVER['HOME'] ?? '/home').'/.config/orbit';

        // Strip port if present
        $domain = explode(':', $domain)[0];

        // Split domain parts — at least app.tld
        $parts = explode('.', $domain);

        if (count($parts) < 2) {
            return null;
        }

        // Try worktree: {branch}.{app}.{tld}
        if (count($parts) >= 3) {
            $branch = $parts[0];
            $app = $parts[1];
            $path = "{$orbitDataPath}/processes/{$app}/{$branch}/state.json";

            if (file_exists($path)) {
                return $path;
            }
        }

        // Try main app: {app}.{tld} → uses "main" as branch
        $app = $parts[count($parts) - 2];
        $path = "{$orbitDataPath}/processes/{$app}/main/state.json";

        if (file_exists($path)) {
            return $path;
        }

        return null;
    }
}
