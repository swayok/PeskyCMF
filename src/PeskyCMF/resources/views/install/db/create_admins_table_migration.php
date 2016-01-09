<?php echo "<?php\n"; ?>

class CreateAdminsTable extends PeskyCMF\Db\MigrationForTableCreation {

    public $table = 'admins';
    public $schema = 'public';

    public function getSqlFromFile($file, $rollback = false) {
        if ($rollback) {
            return 'DROP TABLE "' . $this->table . '";';
        } else {
            $timezone = env('TIME_ZONE', 'UTC');
            return <<<EOF
CREATE TABLE ":schema".":table" (
    "id" serial4 NOT NULL,
    "email" varchar(100) COLLATE "default" NOT NULL,
    "password" varchar(100) COLLATE "default" NOT NULL,
    "parent_id" int4,
    "created_at" timestamp(6) DEFAULT timezone('$timezone'::text, now()) NOT NULL,
    "updated_at" timestamp(6) DEFAULT timezone('$timezone'::text, now()) NOT NULL,
    "remember_token" varchar(100) COLLATE "default" DEFAULT ''::character varying,
    "is_superadmin" bool DEFAULT false NOT NULL,
    "language" char(2) COLLATE "default" DEFAULT 'en'::bpchar,
    "ip" inet DEFAULT '192.168.1.1'::inet,
    "role" varchar(100) COLLATE "default" DEFAULT ''::character varying NOT NULL,
    "is_active" bool DEFAULT true NOT NULL,
    "name" varchar(200) COLLATE "default" DEFAULT ''::character varying NOT NULL
) WITH (OIDS=FALSE);

CREATE INDEX ":table_idx_id01" ON ":schema".":table" USING btree (id);
CREATE INDEX ":table_idx_parent_id01" ON ":schema".":table" USING btree (parent_id);
CREATE INDEX ":table_idx_token01" ON ":schema".":table" USING btree (remember_token);

ALTER TABLE ":schema".":table" ADD PRIMARY KEY ("id");

ALTER TABLE ":schema".":table" ADD UNIQUE ("email");

ALTER TABLE ":schema".":table" ADD FOREIGN KEY ("parent_id") REFERENCES "public".":table" ("id") ON DELETE SET NULL ON UPDATE CASCADE;

CREATE TRIGGER "trigger_timestamp_renew" BEFORE UPDATE OF "remember_token" ON ":schema".":table"
FOR EACH ROW EXECUTE PROCEDURE "for_trigger_timestamp_renew"();
EOF;
        }
    }
}