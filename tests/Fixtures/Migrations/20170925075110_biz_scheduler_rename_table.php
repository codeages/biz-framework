<?php

use Phpmig\Migration\Migration;

class BizSchedulerRenameTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("RENAME TABLE job_pool TO biz_job_pool");
        $connection->exec("RENAME TABLE job TO biz_job");
        $connection->exec("RENAME TABLE job_fired TO biz_job_fired");
        $connection->exec("RENAME TABLE job_log TO biz_job_log");

        $connection->exec("ALTER TABLE `biz_job_fired` ADD COLUMN `retry_num` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '重试次数';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("RENAME TABLE biz_job_pool TO job_pool");
        $connection->exec("RENAME TABLE biz_job TO job");
        $connection->exec("RENAME TABLE biz_job_fired TO job_fired");
        $connection->exec("RENAME TABLE biz_job_log TO job_log");

        $connection->exec("ALTER TABLE `biz_job_fired` DROP COLUMN `retry_num`;");
    }
}
