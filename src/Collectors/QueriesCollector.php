<?php

namespace NckRtl\Toolbar\Collectors;


use NckRtl\Toolbar\Toolbar;
use NckRtl\Toolbar\Data\QueryData;
use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\QueriesData;
use NckRtl\Toolbar\Observers\QueryObserver;
use NckRtl\Toolbar\Data\Configurations\QueriesConfig;

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


        $toolbar = app(Toolbar::class);

        // if ($toolbar->telescopeIsInstalled()) {
        //     $this->handleTelescopeEntries($collectorManager, $toolbar);
        //     return;
        // }



        $queryObserver = $toolbar->observers[QueryObserver::class];

        $this->totalTime = $queryObserver->totalTime;

        $this->queries = $toolbar->observers[QueryObserver::class]->queries;

        $offset = 0;

        foreach($this->queries as $query) {
            $query->percentage = $query->duration / $this->totalTime;
            $query->offset = $offset;
            $offset += $query->percentage;
        }
    }

    public function handleTelescopeEntries(CollectorManager $collectorManager): void
    {
        if($collectorManager->telescopeEntries->has('query'));
        {
            return;
        }

        $this->queries = $collectorManager->telescopeEntries->get('query')->toArray() ?? [];

        if(empty($this->queries)) {
            return;
        }

        $this->totalTime = array_sum(array_column($this->queries, 'duration'));

        $this->queries = array_map(function ($entry) {
            return $entry['content'];
        }, $this->queries);

        $offset = 0;

        $hashes = [];

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
                is_duplicate: $isDuplicateHash,
                is_slow: $entry['slow'],
                percentage: $percentage,
                offset: $offset,
            );

            $offset += $percentage;

            $this->addConnection($entry['connection']);
            $this->addDriver($entry['driver']);
            $this->addDatabase($database);

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
}
