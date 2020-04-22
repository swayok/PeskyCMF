<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use PeskyORM\Core\DbConnectionsManager;
use PeskyORM\Core\Utils;
use PeskyORM\DbExpr;

class CmfAddAdmin extends BaseCommand {

    protected $description = 'Create administrator in DB';
    protected $signature = 'cmf:add_admin {email_or_login} {password} {role=admin} {table=admins} {schema=public} {--login : use [login] field instead of [email]}';

    public function fire() {
        $driver = config('database.default');
        $db = DbConnectionsManager::getConnection($driver);
        $args = $this->input->getArguments();
        $emailOrLogin = strtolower(trim($args['email_or_login']));
        $authField = $this->input->getOption('login') ? 'login' : 'email';
        $password = \Hash::make($args['password']);
        $table = "`{$args['schema']}`.`{$args['table']}`";
        $exists = Utils::getDataFromStatement(
            $db->query(DbExpr::create("SELECT 1 FROM {$table} WHERE `{$authField}`=``{$emailOrLogin}``")),
            Utils::FETCH_VALUE
        );
        if ($exists > 0) {
            $query = "UPDATE {$table} SET `password`=``{$password}``, `role`=``{$args['role']}`` WHERE `{$authField}`=``{$emailOrLogin}``";
        } else {
            $query = "INSERT INTO {$table} (`{$authField}`, `password`, `role`) VALUES (``{$emailOrLogin}``,``{$password}``, ``{$args['role']}``)";
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