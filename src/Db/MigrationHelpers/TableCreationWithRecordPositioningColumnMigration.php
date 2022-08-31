<?php

declare(strict_types=1);

namespace PeskyCMF\Db\MigrationHelpers;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PeskyORM\Core\DbConnectionsManager;
use PeskyORM\Core\DbExpr;

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
        return config('database.connections.' . $this->getConnectionName() . '.schema', function () {
            throw new \UnexpectedValueException('Connection ' . $this->getConnectionName() . ' not exists or has empty schema name');
        });
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
                    $this->quoteDbExpr(DbExpr::create("DROP FUNCTION IF EXISTS `{$schema}`.`{$triggerFunctionName}`() RESTRICT"))
            );
        } catch (\PDOException $exc) {
            // ignore
        }
    }
    
    protected function declarePositioningColumn(Blueprint $table): void
    {
        $columnName = $this->getPositionColumnName();
        if (!in_array($columnName, $table->getColumns(), true)) {
            $seqName = $this->getSequenceName();
            $schema = $this->getDbSchemaName();
            $seqNameQuoted = $this->quoteDbExpr(DbExpr::create("`{$schema}`.`{$seqName}`", false));
            $table->integer($columnName)->default(DB::raw($this->quoteDbExpr(DbExpr::create("nextval(``{$seqNameQuoted}``::regclass)", false))));
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
        $query = <<<QUERY
CREATE TRIGGER `trigger_after_{$columnName}_update_in_{$tableName}`
AFTER UPDATE OF `{$columnName}` ON `{$schema}`.`{$tableName}`
FOR EACH ROW EXECUTE PROCEDURE `{$schema}`.`{$triggerFunctionName}`(``{$columnName}``,``{$pkName}``,``{$seqName}``,``{$seqStep}``);
QUERY;
        DB::statement(
            $this->quoteDbExpr(DbExpr::create($query))
        );
    }
    
    protected function createTriggerFunctionIfNotExists(): void
    {
        $schema = $this->getDbSchemaName();
        $funcName = $this->getTriggerFunctionName();
        $query = <<<QUERY
CREATE OR REPLACE FUNCTION `{$schema}`.`{$funcName}`()
    RETURNS `pg_catalog`.`trigger` AS \$BODY\$
DECLARE
    step integer;
    table_name text;
    col_name text;
    col_name_quoted text;
    col_value integer;
    seq_name text;
    pk_name text;
    pk_value integer;
    max_position integer;
    is_conflict boolean;
BEGIN
    -- args: 0 = positioning column name; 1 = primary key column name; 2 = sequence name; 3 = sequence step
    IF (TG_OP = ``UPDATE`` AND TG_LEVEL = ``ROW``) THEN
        table_name := (quote_ident(TG_TABLE_SCHEMA) || ``.`` || quote_ident(TG_TABLE_NAME));
        col_name := COALESCE(TG_ARGV[0], ``position``);
        col_name_quoted := quote_ident(col_name);
        EXECUTE ``SELECT ($1).`` || col_name_quoted || ``::integer`` INTO col_value USING NEW;
        pk_name := quote_ident(COALESCE(TG_ARGV[1], ``id``));
        EXECUTE ``SELECT ($1).`` || pk_name || ``::integer`` INTO pk_value USING NEW;
        EXECUTE ``SELECT true FROM `` || table_name || `` WHERE `` || pk_name || `` != $1 AND `` || col_name_quoted || `` = $2 LIMIT 1`` INTO is_conflict USING pk_value, col_value;
        IF is_conflict = true THEN
            step := COALESCE(TG_ARGV[3], ``100``)::integer;
            EXECUTE ``UPDATE `` || table_name || `` SET `` || col_name_quoted || `` = `` || col_name_quoted || `` + $1 WHERE `` || pk_name || `` != $2 AND `` || col_name_quoted || `` >= $3`` USING step, pk_value, col_value;
        END IF;
        EXECUTE ``SELECT (`` || col_name_quoted || ``)::INTEGER FROM `` || table_name || `` ORDER BY `` || col_name_quoted || `` DESC LIMIT 1`` INTO max_position;
        seq_name := quote_ident(TG_TABLE_SCHEMA) || ``.`` || quote_ident(COALESCE(TG_ARGV[2], TG_TABLE_NAME || ``_`` || col_name || ``_seq``));
        EXECUTE ``SELECT setval(`` || quote_literal(seq_name) || ``::regclass, `` || max_position || ``::integer)``;
    END IF;
    RETURN NULL;
END;
\$BODY$
    LANGUAGE ``plpgsql`` VOLATILE COST 100
;
QUERY;
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
        return DbConnectionsManager::getConnection($this->getConnectionName())->quoteDbExpr($expr);
    }
}