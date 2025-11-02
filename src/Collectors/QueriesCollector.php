<?php

namespace NckRtl\Toolbar\Collectors;

use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\Configurations\QueriesConfig;
use NckRtl\Toolbar\Data\QueriesData;
use NckRtl\Toolbar\Observers\QueryObserver;
use NckRtl\Toolbar\Toolbar;

class QueriesCollector extends Collector implements CollectorInterface
{
    public float $totalTime = 0;

    public float $totalTimeFilteredQueries = 0;

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
            totalTimeFilteredQueries: $this->totalTimeFilteredQueries,
            databases: $this->databases,
            connections: $this->connections,
            drivers: $this->drivers,
            queries: $this->queries,
        );
    }

    public function setEntries(CollectorManager $collectorManager): void
    {
        $toolbar = app(Toolbar::class);

        $queryObserver = $toolbar->config->getObserver(QueryObserver::class);

        $this->totalTime = $queryObserver->totalTime;

        $this->queries = $queryObserver->queries;

        $this->connections = $queryObserver->connections;

        $this->drivers = $queryObserver->drivers;

        $this->databases = $queryObserver->databases;

        $offset = 0;

        $this->totalTimeFilteredQueries = array_sum(array_column($this->queries, 'duration'));

        // Avoid division by zero when there are no queries
        if ($this->totalTimeFilteredQueries > 0) {
            foreach ($this->queries as $query) {
                $query->percentage = $query->duration / $this->totalTimeFilteredQueries;
                $query->offset = $offset;
                $offset += $query->percentage;
            }
        }
    }
}
