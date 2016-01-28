<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use PeskyORM\Db;
use PeskyORM\DbExpr;

class CmfAddAdmin extends BaseCommand {

    protected $description = 'Create administrator in DB';
    protected $signature = 'cmf:add_admin {email} {password} {role=admin} {table=admins} {schema=public}';

    public function fire() {
        $db = new Db(Db::PGSQL, env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_HOST', 'localhost'));
        $args = $this->input->getArguments();
        $email = strtolower(trim($args['email']));
        $password = \Hash::make($args['password']);
        $table = "`{$args['schema']}`.`{$args['table']}`";
        $exists = $db->processRecords(
            $db->query(DbExpr::create("SELECT 1 FROM {$table} WHERE `email`=``{$email}``")),
            $db::FETCH_VALUE
        );
        if ($exists > 0) {
            $query = "UPDATE {$table} SET `password`=``{$password}``, `role`=``{$args['role']}`` WHERE `email`=``{$email}``";
        } else {
            $query = "INSERT INTO {$table} (`email`, `password`, `role`) VALUES (``{$email}``,``{$password}``, ``{$args['role']}``)";
        }

        try {
            $result = $db->exec(DbExpr::create($query));
            if ($result > 0) {
                $this->line($exists ? 'Admin updated' : 'Admin created');
            } else {
                $this->line('Fail. DB returned "0 rows updated"');
            }
        } catch (\Exception $exc) {
            $this->line('Fail. DB Exception:');
            $this->line($exc->getMessage());
            $this->line($exc->getTraceAsString());
        }
    }
}