<?php
// src/Controllers/HorizonController.php

namespace NckRtl\Toolbar\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Process;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;

class HorizonController
{
    public function status(): JsonResponse
    {
        if (!$this->isAvailable()) {
            return response()->json([
                'available' => false,
                'reason' => 'Horizon not installed',
            ]);
        }

        return response()->json([
            'available' => true,
            'running' => $this->isRunning(),
            'paused' => $this->isPaused(),
        ]);
    }

    public function start(): JsonResponse
    {
        if (!$this->guardEnvironment()) {
            return response()->json(['error' => 'Not allowed in this environment'], 403);
        }

        if ($this->isRunning()) {
            return response()->json([
                'success' => false,
                'message' => 'Horizon is already running',
            ]);
        }



    $logFile = escapeshellarg(storage_path('logs/horizon.log'));
    $basePath = escapeshellarg(base_path());

    // Find php CLI binary
    $php = trim(shell_exec('which php') ?? '');

    if (empty($php)) {
        // Herd typical location
        $php = $_SERVER['HOME'] . '/Library/Application Support/Herd/bin/php';
    }

    $logFile = escapeshellarg(storage_path('logs/horizon.log'));
    $basePath = escapeshellarg(base_path());
    $php = escapeshellarg($php);

    pclose(popen(
        "cd {$basePath} && {$php} artisan horizon >> {$logFile} 2>&1 &",
        "r"
    ));

    return response()->json([
        'success' => true,
        'running' => null,
        'message' => 'Horizon starting...',
    ]);
    }

    public function stop(): JsonResponse
    {
        if (!$this->guardEnvironment()) {
            return response()->json(['error' => 'Not allowed in this environment'], 403);
        }

        if (!$this->isRunning()) {
            return response()->json([
                'success' => false,
                'message' => 'Horizon is not running',
            ]);
        }

        $php = trim(shell_exec('which php') ?? '');
        if (empty($php)) {
            $php = $_SERVER['HOME'] . '/Library/Application Support/Herd/bin/php';
        }

        $basePath = escapeshellarg(base_path());
        $php = escapeshellarg($php);

        shell_exec("cd {$basePath} && {$php} artisan horizon:terminate");

        return response()->json([
            'success' => true,
            'running' => false,
            'message' => 'Horizon terminated',
        ]);
    }

    private function isAvailable(): bool
    {
        return class_exists(\Laravel\Horizon\Horizon::class);
    }

    private function getSupervisors(): \Illuminate\Support\Collection
    {
        return collect(app(MasterSupervisorRepository::class)->all());
    }

    private function isRunning(): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        return $this->getSupervisors()->isNotEmpty();
    }

    private function isPaused(): bool
    {
        if (!$this->isRunning()) {
            return false;
        }

        return $this->getSupervisors()->every(fn ($s) => $s->status === 'paused');
    }

    private function guardEnvironment(): bool
    {
        // Only allow in local/development
        if (!app()->environment('local', 'development')) {
            return false;
        }

        // Check config
        return true;
    }
}