<?php

namespace Tests\Unit;

use Helldar\MigrateDB\Exceptions\InvalidArgumentException;
use Helldar\Support\Facades\Helpers\Arr;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class MigrateTest extends TestCase
{
    public function testFillable()
    {
        $this->assertNotEmpty($this->sourceConnection()->getAllTables());
        $this->assertEmpty($this->targetConnection()->getAllTables());

        $this->artisan('db:migrate', [
            '--schema-from' => $this->source,
            '--schema-to'   => $this->target,
        ])->assertExitCode(0)->run();

        $this->assertNotEmpty($this->sourceConnection()->getAllTables());
        $this->assertNotEmpty($this->targetConnection()->getAllTables());
    }

    public function testCount()
    {
        $this->assertDatabaseCount($this->table_foo, 3, $this->source);
        $this->assertDatabaseCount($this->table_bar, 3, $this->source);
        $this->assertDatabaseCount($this->table_baz, 3, $this->source);

        $this->artisan('db:migrate', [
            '--schema-from' => $this->source,
            '--schema-to'   => $this->target,
        ])->assertExitCode(0)->run();

        $this->assertDatabaseCount($this->table_foo, 3, $this->source);
        $this->assertDatabaseCount($this->table_bar, 3, $this->source);
        $this->assertDatabaseCount($this->table_baz, 3, $this->source);

        $this->assertDatabaseCount($this->table_foo, 3, $this->target);
        $this->assertDatabaseCount($this->table_bar, 3, $this->target);
        $this->assertDatabaseCount($this->table_baz, 3, $this->target);
    }

    public function testData()
    {
        $this->assertDatabaseHas($this->table_foo, ['value' => 'foo_1'], $this->source);
        $this->assertDatabaseHas($this->table_foo, ['value' => 'foo_2'], $this->source);
        $this->assertDatabaseHas($this->table_foo, ['value' => 'foo_3'], $this->source);

        $this->assertDatabaseHas($this->table_bar, ['value' => 'bar_1'], $this->source);
        $this->assertDatabaseHas($this->table_bar, ['value' => 'bar_2'], $this->source);
        $this->assertDatabaseHas($this->table_bar, ['value' => 'bar_3'], $this->source);

        $this->assertDatabaseHas($this->table_baz, ['value' => 'baz_1'], $this->source);
        $this->assertDatabaseHas($this->table_baz, ['value' => 'baz_2'], $this->source);
        $this->assertDatabaseHas($this->table_baz, ['value' => 'baz_3'], $this->source);

        $this->artisan('db:migrate', [
            '--schema-from' => $this->source,
            '--schema-to'   => $this->target,
        ])->assertExitCode(0)->run();

        $this->assertDatabaseHas($this->table_foo, ['value' => 'foo_1'], $this->target);
        $this->assertDatabaseHas($this->table_foo, ['value' => 'foo_2'], $this->target);
        $this->assertDatabaseHas($this->table_foo, ['value' => 'foo_3'], $this->target);

        $this->assertDatabaseHas($this->table_bar, ['value' => 'bar_1'], $this->target);
        $this->assertDatabaseHas($this->table_bar, ['value' => 'bar_2'], $this->target);
        $this->assertDatabaseHas($this->table_bar, ['value' => 'bar_3'], $this->target);

        $this->assertDatabaseHas($this->table_baz, ['value' => 'baz_1'], $this->target);
        $this->assertDatabaseHas($this->table_baz, ['value' => 'baz_2'], $this->target);
        $this->assertDatabaseHas($this->table_baz, ['value' => 'baz_3'], $this->target);
    }

    public function testSame()
    {
        $this->artisan('db:migrate', [
            '--schema-from' => $this->source,
            '--schema-to'   => $this->target,
        ])->assertExitCode(0)->run();

        $this->assertSame(
            $this->tableData($this->source, $this->table_foo),
            $this->tableData($this->target, $this->table_foo)
        );

        $this->assertSame(
            $this->tableData($this->source, $this->table_bar),
            $this->tableData($this->target, $this->table_bar)
        );

        $this->assertSame(
            $this->tableData($this->source, $this->table_baz),
            $this->tableData($this->target, $this->table_baz)
        );
    }

    public function testFailed()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "schema-from" option does not exist.');

        $this->artisan('db:migrate')->run();
    }

    public function testFromFailed()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "schema-from" option does not exist.');

        $this->artisan('db:migrate', ['--schema-to' => $this->target])->run();
    }

    public function testToFailed()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "schema-to" option does not exist.');

        $this->artisan('db:migrate', ['--schema-from' => $this->source])->run();
    }

    public function testFailedFromConnectionName()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported driver [qwerty].');

        $this->artisan('db:migrate', ['--schema-from' => 'qwerty', '--schema-to' => $this->target])->run();
    }

    public function testFailedToConnectionName()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported driver [qwerty].');

        $this->artisan('db:migrate', ['--schema-from' => $this->source, '--schema-to' => 'qwerty'])->run();
    }

    protected function tableData(string $connection, string $table): array
    {
        $items = DB::connection($connection)->table($table)->get();

        return Arr::toArray($items);
    }
}
