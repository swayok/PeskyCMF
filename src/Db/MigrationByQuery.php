<?php


namespace PeskyCMF\Db;

abstract class MigrationByQuery extends Migration {

    public $tables = [''];
    public $file = __FILE__;
    public $upIntro = '';
    public $downIntro = '';
    public $ignoreErrors = false;
    public $schema = 'public';

    /**
     * Run the migrations.
     *
     * @throws \Exception
     * @throws \PeskyORM\Exception\DbException
     */
    public function up() {
        $this->out($this->upIntro);
        $query = $this->getSqlFromFile($this->file);
        try {
            $this->executeQueryOnSchema($this->schema, $query, $this->tables, $this->getUpTestQuery());
        } catch (\Exception $exc) {
            if ($this->ignoreErrors) {
                $this->out('Exception: ' . $exc->getMessage());
            } else {
                throw $exc;
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @throws \Exception
     * @throws \PeskyORM\Exception\DbException
     */
    public function down() {
        $this->out($this->downIntro);
        $query = $this->getSqlFromFile($this->file, true);
        try {
            $this->executeQueryOnSchema($this->schema, $query, $this->tables, $this->getDownTestQuery());
        } catch (\Exception $exc) {
            if ($this->ignoreErrors) {
                $this->out('Exception: ' . $exc->getMessage());
            } else {
                throw $exc;
            }
        }
    }

    public function getUpTestQuery() {
        return null;
    }

    public function getDownTestQuery() {
        return null;
    }

}