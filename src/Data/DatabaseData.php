<?php

namespace NckRtl\Toolbar\Data;

use Spatie\LaravelData\Data;

class DatabaseData extends Data
{
    public ?string $tablePlusConnectionUrl = null;

    public function __construct(
        public string $name,
        public string $connection,
        public string $driver,
    ) {
        $this->setTablePlusConnectionUrl();
    }

    public function setTablePlusConnectionUrl(): void
    {
        /** @var string|null $username */
        $username = config('database.connections.'.$this->connection.'.username');
        /** @var string|null $password */
        $password = config('database.connections.'.$this->connection.'.password');
        /** @var string|null $host */
        $host = config('database.connections.'.$this->connection.'.host');
        /** @var int|string|null $port */
        $port = config('database.connections.'.$this->connection.'.port');

        if ($username === null || $username === '' || $host === null || $host === '' || $port === null || $port === '') {
            return;
        }

        $protocol = match ($this->driver) {
            'mysql' => 'mysql',
            'pgsql' => 'postgresql',
            'sqlite' => 'sqlite',
            default => null,
        };

        if ($protocol === null) {
            return;
        }

        $this->tablePlusConnectionUrl = $protocol.'://';

        // Username, host, and port are guaranteed non-empty after check above
        $this->tablePlusConnectionUrl .= $username;

        if ($password !== null && $password !== '') {
            $this->tablePlusConnectionUrl .= ':'.$password;
        }

        $this->tablePlusConnectionUrl .= '@'.$host;
        $this->tablePlusConnectionUrl .= ':'.$port;
        $this->tablePlusConnectionUrl .= '/'.$this->name;
    }
}
