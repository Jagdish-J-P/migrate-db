<?php

namespace Helldar\MigrateDB\Database;

final class ClickHouseBuilder extends Builder
{
    public function getAllTables(): array
    {
        // $tables = $this->schema()->getAllTables();
        //
        // $key = $this->tableNameColumn();
        //
        // return $this->pluckTableNames($this->filteredTables($tables, $key), $key);
    }

    protected function tableNameColumn(): string
    {
        return 'Tables_in_' . $this->database();
    }
}
