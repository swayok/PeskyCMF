<?php

declare(strict_types=1);

namespace PeskyCMF\Db\MigrationHelpers;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use PeskyORM\Config\Connection\DbConnectionsFacade;
use PeskyORM\DbExpr;

abstract class TableCreationWithRecordPositioningColumnMigration extends Migration
{
    /**
     * Name of the table to create
     */
    abstract public function getTableName(): string;

    /**
     * Declare all columns you need in table except for the column used for positioning
     */
    abstract public function declareTableStructure(Blueprint $table);

    public function getPositionColumnName(): string
    {
        return 'position';
    }

    /**
     * Primary key column name to use in triggger function
     */
    public function getPrimaryKeyColumnName(): string
    {
        return 'id';
    }

    public function getSequenceStep(): int
    {
        return 100;
    }

    protected function getDbSchemaName(): ?string
    {
        return config(
            'database.connections.' . $this->getConnectionName() . '.schema',
            function () {
                throw new \UnexpectedValueException(
                    'Connection ' . $this->getConnectionName()
                    . ' not exists or has empty schema name'
                );
            }
        );
    }

    public function up(): void
    {
        $tableName = $this->getTableName();
        if (!Schema::hasTable($tableName)) {
            $schema = $this->getDbSchemaName();
            $step = $this->getSequenceStep();
            $columnName = $this->getPositionColumnName();
            $seqName = $this->getSequenceName();
            $dbExpr = $this->quoteDbExpr(
                DbExpr::create(
                    "CREATE SEQUENCE IF NOT EXISTS `{$schema}`.`{$seqName}` INCREMENT {$step} START {$step}"
                )
            );
            DB::statement($dbExpr);

            Schema::create($tableName, function (Blueprint $table) {
                $this->declareTableStructure($table);
                $this->declarePositioningColumn($table);
            });

            $query = $this->quoteDbExpr(
                DbExpr::create(
                    "ALTER SEQUENCE `{$schema}`.`{$seqName}` OWNED BY `{$schema}`.`{$tableName}`.`{$columnName}`"
                )
            );
            $success = DB::statement($query);
            if (!$success) {
                throw new \UnexpectedValueException(
                    'Query execuition returned negative result for query ' . $query
                );
            }

            $this->addPositioningUpdateTrigger();
        }
    }

    public function down(): void
    {
        Schema::dropIfExists($this->getTableName());
        $schema = $this->getDbSchemaName();
        $seqName = $this->getSequenceName();
        DB::statement(
            $this->quoteDbExpr(DbExpr::create("DROP SEQUENCE IF EXISTS `{$schema}`.`{$seqName}`"))
        );
        try {
            $triggerFunctionName = $this->getTriggerFunctionName();
            DB::statement(
                $this->quoteDbExpr(
                    DbExpr::create("DROP FUNCTION IF EXISTS `{$schema}`.`{$triggerFunctionName}`() RESTRICT")
                )
            );
        } catch (\PDOException) {
            // ignore
        }
    }

    protected function declarePositioningColumn(Blueprint $table): void
    {
        $columnName = $this->getPositionColumnName();
        if (!in_array($columnName, $table->getColumns(), true)) {
            $seqName = $this->getSequenceName();
            $schema = $this->getDbSchemaName();
            $seqNameQuoted = $this->quoteDbExpr(
                DbExpr::create("`{$schema}`.`{$seqName}`", false)
            );
            $table->integer($columnName)->default(
                DB::raw($this->quoteDbExpr(
                    DbExpr::create("nextval(``{$seqNameQuoted}``::regclass)", false)
                ))
            );
            $table->index($columnName);
        }
    }

    final protected function getSequenceName(): string
    {
        $tableName = $this->getTableName();
        $columnName = $this->getPositionColumnName();
        return "{$tableName}_{$columnName}_seq";
    }

    protected function addPositioningUpdateTrigger(): void
    {
        $schema = $this->getDbSchemaName();
        $tableName = $this->getTableName();
        $columnName = $this->getPositionColumnName();
        $this->createTriggerFunctionIfNotExists();
        $pkName = $this->getPrimaryKeyColumnName();
        $triggerFunctionName = $this->getTriggerFunctionName();
        $seqName = $this->getSequenceName();
        $seqStep = $this->getSequenceStep();
        $stub = File::get(__DIR__ . '/queries/position_column_trigger.sql.stub');
        $inserts = [
            '{$columnName}' => $columnName,
            '{$tableName}' => $tableName,
            '{$schema}' => $schema,
            '{$triggerFunctionName}' => $triggerFunctionName,
            '{$pkName}' => $pkName,
            '{$seqName}' => $seqName,
            '{$seqStep}' => $seqStep,
        ];
        $query = str_replace(array_keys($inserts), array_values($inserts), $stub);
        DB::statement(
            $this->quoteDbExpr(DbExpr::create($query))
        );
    }

    protected function createTriggerFunctionIfNotExists(): void
    {
        $schema = $this->getDbSchemaName();
        $funcName = $this->getTriggerFunctionName();
        $stub = File::get(__DIR__ . '/queries/position_column_trigger_function.sql.stub');
        $inserts = [
            '{$schema}' => $schema,
            '{$funcName}' => $funcName
        ];
        $query = str_replace(array_keys($inserts), array_values($inserts), $stub);
        DB::statement(
            $this->quoteDbExpr(DbExpr::create($query))
        );
    }

    protected function getTriggerFunctionName(): string
    {
        return 'trigger_fn_for_row_repositioning';
    }

    protected function getConnectionName()
    {
        return $this->getConnection() ?: config('database.default');
    }

    protected function quoteDbExpr(DbExpr $expr): string
    {
        return DbConnectionsFacade::getConnection($this->getConnectionName())
            ->quoteDbExpr($expr);
    }
}
