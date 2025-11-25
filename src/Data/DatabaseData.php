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
      $username = config('database.connections.' . $this->connection . '.username');
      $password = config('database.connections.' . $this->connection . '.password');
      $host = config('database.connections.' . $this->connection . '.host');
      $port = config('database.connections.' . $this->connection . '.port');

      if(empty($username) || empty($host) || empty($port))
      {
        return;
      }

      $protocol = match($this->driver) {
        'mysql' => 'mysql',
        'pgsql' => 'postgresql',
        'sqlite' => 'sqlite',
        default => 'null',
      };

      if(empty($protocol !== 'null'))
      {
        return;
      }

      $this->tablePlusConnectionUrl = $protocol . '://';

      if(!empty($username))
      {
        $this->tablePlusConnectionUrl .= $username;
      }

      if(!empty($password))
      {
        $this->tablePlusConnectionUrl .= ':' . $password;
      }

      if(!empty($host))
      {
        $this->tablePlusConnectionUrl .= '@' . $host;
      }

      if(!empty($port))
      {
        $this->tablePlusConnectionUrl .= ':' . $port;
      }

      $this->tablePlusConnectionUrl .= '/' . $this->name;
    }
}
