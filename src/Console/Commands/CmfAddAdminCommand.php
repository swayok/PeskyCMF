<?php

declare(strict_types=1);

namespace PeskyCMF\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use PeskyCMF\Config\CmfConfig;
use PeskyORM\Core\DbConnectionsManager;
use PeskyORM\Core\DbExpr;
use PeskyORM\Core\Utils;

class CmfAddAdminCommand extends Command
{
    
    protected $description = 'Create administrator in DB';
    protected $signature = 'cmf:add-admin 
        {email_or_login} 
        {role=admin} 
        {table=admins} 
        {schema?} 
        {--login : use [login] field instead of [email]}';
    
    public function handle(): void
    {
        $db = DbConnectionsManager::getConnection('default');
        $args = $this->input->getArguments();
        $emailOrLogin = strtolower(trim($args['email_or_login']));
        $authField = $this->input->getOption('login') ? 'login' : 'email';
        $table = empty($args['schema']) ? $args['table'] : "{$args['schema']}.{$args['table']}";
        $exists = $db->query(
            DbExpr::create("SELECT 1 FROM {$table} WHERE `{$authField}`=``{$emailOrLogin}``"),
            Utils::FETCH_VALUE
        );
        $password = $this->secret('Enter password for admin');
        if (empty($password)) {
            $this->line('Cannot continue: password is empty');
            exit;
        }
        try {
            $data = [
                'password' => Hash::make($password),
                'role' => $args['role'],
                'is_superadmin' => true,
                $authField => $emailOrLogin,
                'language' => CmfConfig::getDefault()->default_locale(),
            ];
            if ($exists > 0) {
                $result = $db->update($table, $data, DbExpr::create("`{$authField}`=``{$emailOrLogin}``"));
            } else {
                $result = $db->insert($table, $data);
            }
            
            if ($result > 0) {
                $this->line($exists > 0 ? 'Admin updated' : 'Admin created');
            } else {
                $this->line('Fail. DB returned "0 rows updated"');
            }
        } catch (\Exception $exc) {
            $this->line('Fail. DB Exception:');
            $this->line($exc->getMessage());
            $this->line($exc->getTraceAsString());
            exit;
        }
    }
}