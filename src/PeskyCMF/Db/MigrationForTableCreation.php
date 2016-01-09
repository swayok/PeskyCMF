<?php


namespace PeskyCMF\Db;

abstract class MigrationForTableCreation extends Migration {

    public $table = '';
    public $file = __FILE__;
    public $schema = 'public';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $query = $this->getSqlFromFile($this->file);
        $this->createTableInSchema($this->schema, $query, $this->table);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $this->dropTableFromSchema($this->schema, $this->table);
    }

    public function getSqlFromFile($file, $rollback = false) {
        return preg_replace(
            ['%"(' . $this->schema . '|public|:schema)"\."NewTable"%m'],
            ['":schema".":table"'],
            parent::getSqlFromFile($file, $rollback)
        );
    }

}