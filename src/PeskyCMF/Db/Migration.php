<?php


namespace PeskyCMF\Db;

use Illuminate\Database\Migrations\Migration as Base;
use PeskyORM\Db;
use PeskyORM\DbExpr;
use Swayok\Utils\StringUtils;

class Migration extends Base {

    public function getConnection() {
        return new Db(Db::PGSQL, env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_HOST', 'localhost'));
    }

    protected function out($string) {
        echo $string . "\n";
    }

    protected function createTableInSchema($schema, $query, $tableName) {
        $test = "SELECT to_regclass('{$schema}.{$tableName}')::int;";
//        $test = "SELECT EXISTS (SELECT 1 FROM `information_schema`.`tables` WHERE `table_schema` = ``$schema`` AND `table_name` = ``$tableName``);";
        $query = StringUtils::insert($query, ['table' => $tableName, 'schema' => $schema]);
        $this->out('Create table: ' . $tableName . ' in DB Schema ' . $schema);
        $ds = $this->getConnection();
        $stmnt = $ds->query(DbExpr::create($test));
        if ($stmnt) {
            $exists = Db::processRecords($stmnt, DB::FETCH_VALUE);
            if (!empty($exists)) {
                $this->out('- Table already exists');
            } else {
                $ds->exec(DbExpr::create($query));
                $this->out('+ Done');
            }
        } else {
            $this->out('- Failed to test if table already exists');
        }
        $ds->disconnect();
    }

    protected function dropTableFromSchema($schema, $tableName) {
        $this->out('Drop table: ' . $tableName . ' from DB Schema ' . $schema);
        $ds = $this->getConnection();
        $test = "SELECT to_regclass('{$schema}.{$tableName}')::int;";
        $stmnt = $ds->query(DbExpr::create($test));
        if ($stmnt) {
            $exists = Db::processRecords($stmnt, DB::FETCH_VALUE);
            if (empty($exists)) {
                $this->out('- Table not exists');
            } else {
                $ds->query(DbExpr::create("DROP TABLE `{$schema}`.`{$tableName}`"));
                $this->out('+ Done');
            }
        } else {
            $this->out('- Failed to test if table already exists');
        }
        $ds->disconnect();
    }

    /**
     * @param string $schema
     * @param string $queryTpl
     * @param array $tables - empty value: just execute $queryTpl
     * @param null|string $testQueryTpl
     * @throws \PeskyORM\Exception\DbException
     */
    public function executeQueryOnSchema($schema, $queryTpl, array $tables = [], $testQueryTpl = null) {
        $this->out('DB Schema: ' . $schema);
        $ds = $this->getConnection();
        if (!empty($tables)) {
            foreach ($tables as $tableName) {
                $this->out('Update table: ' . $tableName);
                $query = StringUtils::insert($queryTpl, ['table' => $tableName, 'schema' => $schema]);
                if (!empty($testQueryTpl)) {
                    $test = StringUtils::insert($testQueryTpl, ['table' => $tableName, 'schema' => $schema]);
                    $stmnt = $ds->query(DbExpr::create($test));
                    if ($stmnt) {
                        $exists = Db::processRecords($stmnt, DB::FETCH_VALUE);
                        if (!empty($exists)) {
                            $this->out('- Object already exists');
                        } else {
                            $ds->exec(DbExpr::create($query));
                            $this->out('+ Done');
                        }
                    } else {
                        $this->out('- Failed to test if Object already exists');
                    }
                } else {
                    $ds->exec(DbExpr::create($query));
                    $this->out('+ Done');
                }
            }
        } else {
            $query = $queryTpl;
            if (!empty($testQueryTpl)) {
                $test = StringUtils::insert($testQueryTpl, ['schema' => $schema]);
                $stmnt = $ds->query(DbExpr::create($test));
                if ($stmnt) {
                    $exists = Db::processRecords($stmnt, DB::FETCH_VALUE);
                    if (!empty($exists)) {
                        $this->out('- Object already exists');
                    } else {
                        $ds->exec(DbExpr::create($query));
                        $this->out('+ Done');
                    }
                } else {
                    $this->out('- Failed to test if Object already exists');
                }
            } else {
                $ds->exec(DbExpr::create($query));
                $this->out('+ Done');
            }
        }
        $ds->disconnect();
    }

    public function getTriggerTestQuery($triggerName, $schema) {
        return 'SELECT true FROM `information_schema`.`triggers` WHERE `event_object_table` = ``:table``' .
            " AND `event_object_schema` = ``$schema`` AND `event_object_catalog` = ``:db_name``" .
            " AND `trigger_name` = ``$triggerName``";
    }

    public function getSqlFromFile($file, $rollback = false) {
        $file = preg_replace(
            '%^(.*)([/\\\])([^/\\\]+)\.php$%is',
            '$1$2sql' . ($rollback ? DIRECTORY_SEPARATOR . 'rollback' : '') . '$2$3.sql',
            $file
        );
        if (!file_exists($file)) {
            throw new \Exception('SQL file ' . $file . ' not exists');
        }
        return file_get_contents($file);
    }

}