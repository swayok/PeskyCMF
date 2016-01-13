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
        $password = \Hash::make($args['password']);
        $query = "INSERT INTO `{$args['schema']}`.`{$args['table']}` (`email`, `password`, `role`) VALUES (``{$args['email']}``,``{$password}``, ``{$args['role']}``)";
        try {
            $result = $db->exec(DbExpr::create($query));
            if ($result > 0) {
                $this->line('Done');
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