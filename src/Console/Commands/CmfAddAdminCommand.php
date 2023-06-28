<?php

declare(strict_types=1);

namespace PeskyCMF\Console\Commands;

class CmfAddAdminCommand extends CmfCommand
{
    protected $description = 'Create administrator in DB';

    protected $signature = 'cmf:add-admin
        {email_or_login}
        {role=admin}
        {cmf-section? : cmf section name (key) that exists in config(\'peskycmf.cmf_configs\')}
        {--login}
    ';

    public function handle(): int
    {
        $adminsTable = $this->getCmfConfig()->getAuthModule()->getUsersTable();
        $args = $this->input->getArguments();
        $emailOrLogin = strtolower(trim($args['email_or_login']));
        $authField = $this->input->getOption('login') ? 'login' : 'email';
        $admin = $adminsTable->newRecord()->fetch([
            $authField => [$emailOrLogin, strtolower(trim($emailOrLogin))],
        ]);
        $password = $this->secret('Enter password for admin');
        if (empty($password)) {
            $this->line('Cannot continue: password is empty');
            return 1;
        }
        try {
            $adminsTableStructure = $adminsTable->getTableStructure();
            $isCreation = !$admin->existsInDb();
            if (!$isCreation) {
                $admin->begin();
            }
            $admin->updateValue('password', $password, false);
            if ($adminsTableStructure->hasColumn('role')) {
                $admin->updateValue('role', $args['role'] ?? 'admin', false);
            }
            if ($adminsTableStructure->hasColumn('is_superadmin')) {
                $admin->updateValue('is_superadmin', true, false);
            }

            if ($isCreation) {
                $admin->updateValue($authField, $emailOrLogin, false);
                if ($adminsTableStructure->hasColumn('language')) {
                    $admin->updateValue(
                        'language',
                        $this->getCmfConfig()->defaultLocale(),
                        false
                    );
                }
                $admin->save();
            } else {
                $admin->commit();
            }

            $this->line($isCreation ? 'Admin created' : 'Admin updated');
        } catch (\Exception $exc) {
            $this->line('Fail. DB Exception:');
            $this->line($exc->getMessage());
            $this->line($exc->getTraceAsString());
            return 1;
        }
        return 0;
    }
}
