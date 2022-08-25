<?php
declare(strict_types=1);

echo "<?php\n";
?>

class CreateTimestampRenewTriggerFunction extends PeskyCMF\Db\MigrationByQuery {

    public $tables = null;
    public $upIntro = 'Create cmf trigger for timestamp renew';
    public $downIntro = 'Undo Create cmf trigger for timestamp renew';
    public $ignoreErrors = false;
    public $schema = 'public';
    public $triggerName = 'for_trigger_timestamp_renew';

    public function getUpTestQuery() {
        return $this->getTriggerTestQuery($this->triggerName, $this->schema);
    }

    public function getDownTestQuery() {
        return $this->getTriggerTestQuery($this->triggerName, $this->schema);
    }

    public function getSqlFromFile($file, $rollback = false) {
        if ($rollback) {
            return 'DROP FUNCTION ":schema"."' . $this->triggerName . '"();';
        } else {
            $timezone = config('app.timezone', 'UTC');
            return <<<EOF
CREATE FUNCTION ":schema"."{$this->triggerName}"()
  RETURNS "pg_catalog"."trigger" AS \$BODY\$BEGIN
  IF (NEW != OLD) THEN
      NEW.updated_at := (current_timestamp at time zone '$timezone')::timestamp(0);
  END IF;
  RETURN NEW;
END
\$BODY\$
LANGUAGE 'plpgsql' VOLATILE SECURITY DEFINER  COST 100;

EOF;
        }
    }
}