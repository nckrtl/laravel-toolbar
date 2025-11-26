<?php

namespace NckRtl\Toolbar\Observers;

use Illuminate\Database\Events\QueryExecuted;
use NckRtl\Toolbar\Data\DatabaseData;
use NckRtl\Toolbar\Data\QueryData;
use NckRtl\Toolbar\Enums\DataSizeUnit;
use NckRtl\Toolbar\Measurement;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;

class QueryObserver
{
    use FetchesStackTrace;

    public float $totalTime = 0;

    public array $connections = [];

    public array $drivers = [];

    public array $databases = [];

    public array $queries = [];

    public array $hashes = [];

    private int $queryMemory = 0;

    private bool $lookUpCallerFromStackTrace;

    public function __construct()
    {
        $this->lookUpCallerFromStackTrace = ! app()->isProduction();

        app('events')->listen(QueryExecuted::class, [$this, 'recordQuery']);
    }

    public function recordQuery(QueryExecuted $event)
    {
        if ($this->queryMemory == 0) {
            $this->queryMemory = Profiler::getCurrentMemoryUsage()->value;
        }

        $memoryBefore = $this->queryMemory;
        $memoryAfter = memory_get_usage(true);

        $time = $event->time;
        $this->totalTime += $time;

        $caller = $this->lookUpCallerFromStackTrace ? $this->getCallerFromStackTrace() : null;

        [$hash, $queryIsduplicate] = $this->hash($event->sql);

        $this->addConnection($event->connectionName);
        $this->addDriver($event->connection->getDriverName());
        $this->addDatabase($event->connection->getDatabaseName(), $event);

        $this->queries[] = new QueryData(
            hash: $hash,
            sql: $this->replaceBindings($event),
            bindings: $this->formatBindings($event),
            duration: $time,
            connection: $event->connectionName,
            driver: $event->connection->getDriverName(),
            file: $caller ? $caller['file'] : null,
            line: $caller ? $caller['line'] : null,
            is_duplicate: $queryIsduplicate,
            is_slow: $time >= 100,
            memory_used: new Measurement($memoryAfter - $memoryBefore, DataSizeUnit::BYTES)->convertTo(DataSizeUnit::KILOBYTES),
        );
    }

    public function hash($sql): array
    {
        $hash = md5($sql);

        $queryIsduplicate = in_array($hash, $this->hashes);

        if (! $queryIsduplicate) {
            $this->hashes[] = $hash;
        }

        return [$hash, $queryIsduplicate];
    }

    public function addConnection($connection)
    {
        if (! in_array($connection, $this->connections)) {
            $this->connections[] = $connection;
        }
    }

    public function addDriver($driver)
    {
        if (! in_array($driver, $this->drivers)) {
            $this->drivers[] = $driver;
        }
    }

    public function addDatabase($database, $event)
    {
        if (array_key_exists($database, $this->databases)) {
            return;
        }

        $this->databases[$database] = new DatabaseData(
            name: $database,
            connection: $event->connectionName,
            driver: $event->connection->getDriverName(),
        );

    }

    protected function formatBindings($event)
    {
        return $event->connection->prepareBindings($event->bindings);
    }

    public function replaceBindings($event)
    {
        $sql = $event->sql;

        foreach ($this->formatBindings($event) as $key => $binding) {
            $regex = is_numeric($key)
                ? "/\?(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/"
                : "/:{$key}(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/";

            if ($binding === null) {
                $binding = 'null';
            } elseif (! is_int($binding) && ! is_float($binding)) {
                $binding = $this->quoteStringBinding($event, $binding);
            }

            $sql = preg_replace(
                $regex,
                $binding,
                $sql,
                is_numeric($key) ? 1 : -1
            );
        }

        return $sql;
    }

    /**
     * Add quotes to string bindings.
     *
     * @param  \Illuminate\Database\Events\QueryExecuted  $event
     * @param  string  $binding
     * @return string
     */
    protected function quoteStringBinding($event, $binding)
    {
        try {
            $pdo = $event->connection->getPdo();

            if ($pdo instanceof \PDO) {
                return $pdo->quote($binding);
            }
        } catch (\PDOException $e) {
            throw_if($e->getCode() !== 'IM001', $e);
        }

        // Fallback when PDO::quote function is missing...
        $binding = \strtr($binding, [
            chr(26) => '\\Z',
            chr(8) => '\\b',
            '"' => '\"',
            "'" => "\'",
            '\\' => '\\\\',
        ]);

        return "'".$binding."'";
    }
}
