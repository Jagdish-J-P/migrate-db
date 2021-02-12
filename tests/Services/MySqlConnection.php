<?php

namespace Tests\Services;

use Helldar\Support\Concerns\Makeable;
use Illuminate\Database\Connectors\MySqlConnector;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use PDO;

final class MySqlConnection
{
    use Makeable;

    protected $config = [];

    protected $database;

    public function of(string $name): self
    {
        $this->database = $name;

        return $this;
    }

    public function dropDatabase(string $name = null): self
    {
        $name = $this->database($name);

        $this->query($this->compileDropDatabase($name));

        return $this;
    }

    public function createDatabase(string $name = null): self
    {
        $name = $this->database($name);

        $this->query($this->compileCreateDatabase($name));

        return $this;
    }

    protected function query(string $query): void
    {
        $this->connection()->query($query);
    }

    protected function connection(): PDO
    {
        $config = $this->config();

        return $this->connector()->connect($config);
    }

    protected function connector(): MySqlConnector
    {
        return new MySqlConnector();
    }

    protected function config(): array
    {
        if (! empty($this->config)) {
            return $this->config;
        }

        $config = Config::get('database.connections.' . $this->driver());

        return $this->config = $this->cleanConfig($config);
    }

    protected function driver(): string
    {
        return Config::get('database.default');
    }

    protected function cleanConfig(array $config): array
    {
        $this->setDatabase($config);

        return $config;
    }

    protected function setDatabase(array &$config): void
    {
        Arr::set($config, 'database', '');
    }

    protected function compileDropDatabase(string $name): string
    {
        return sprintf(
            'drop database if exists %s',
            $this->wrapValue($name)
        );
    }

    protected function compileCreateDatabase(string $name): string
    {
        return sprintf(
            'create database %s default character set %s default collate %s',
            $this->wrapValue($name),
            $this->wrapValue(Arr::get($this->config, 'charset')),
            $this->wrapValue(Arr::get($this->config, 'collation'))
        );
    }

    protected function database(string $name = null): string
    {
        return $name ?: $this->database;
    }

    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param  string  $value
     *
     * @return string
     */
    protected function wrapValue(string $value): string
    {
        if ($value !== '*') {
            return '`' . str_replace('`', '``', $value) . '`';
        }

        return $value;
    }
}
