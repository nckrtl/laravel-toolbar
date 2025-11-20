<?php

namespace NckRtl\Toolbar\Collectors;

use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\Configurations\QueriesConfig;
use NckRtl\Toolbar\Data\QueriesData;
use NckRtl\Toolbar\Data\QueryData;
use NckRtl\Toolbar\Toolbar;

class QueriesCollector extends Collector implements CollectorInterface
{
    public float $totalTime = 0;

    public array $queries = [];

    public array $databases = [];

    public array $connections = [];

    public array $drivers = [];

    public function key(): string
    {
        return 'queries';
    }

    public function configClass(): string
    {
        return QueriesConfig::class;
    }

    public function collectData(CollectorManager $collectorManager): QueriesData
    {
        $this->setEntries($collectorManager);
        $this->setTotalTime();

        return new QueriesData(
            totalTime: $this->totalTime,
            databases: $this->databases,
            connections: $this->connections,
            drivers: $this->drivers,
            queries: $this->queries,
        );
    }

    public function setEntries(CollectorManager $collectorManager): void
    {
        $hashes = [];

        $toolbar = app(Toolbar::class);

        if (! $toolbar->telescopeIsInstalled() || ! $collectorManager->telescopeEntries->has('query')) {
            return;
        }

        $this->queries = $collectorManager->telescopeEntries->get('query')->toArray() ?? [];

        $this->queries = array_map(function ($entry) {
            return $entry['content'];
        }, $this->queries);

        $this->setTotalTime();

        $offset = 0;

        $this->queries = array_map(function ($entry) use (&$hashes, &$offset) {
            $isDuplicateHash = in_array($entry['hash'], $hashes);
            if (! $isDuplicateHash) {
                $hashes[] = $entry['hash'];
            }

            $percentage = $this->totalTime > 0 ? round($entry['time'] / $this->totalTime, 2) : 0;

            $data = new QueryData(
                hash: $entry['hash'],
                sql: $entry['sql'],
                bindings: $entry['bindings'],
                duration: $entry['time'],
                connection: $entry['connection'],
                driver: $entry['driver'],
                file: $entry['file'],
                line: $entry['line'],
                isDuplicate: $isDuplicateHash,
                isSlow: $entry['slow'],
                percentage: $percentage,
                offset: $offset,
            );

            $offset += $percentage;

            if (! in_array($entry['connection'], $this->connections)) {
                $this->connections[] = $entry['connection'];

                if (! in_array($entry['driver'], $this->drivers)) {
                    $this->drivers[] = $entry['driver'];
                }

                $database = config('database.connections.'.$entry['connection'].'.database');

                if (! in_array($database, $this->databases)) {
                    $this->databases[] = $database;
                }
            }

            return $data;
        }, $this->queries);

    }

    public function setTotalTime(): void
    {
        $this->totalTime = array_sum(array_column($this->queries, 'duration'));
    }
}
