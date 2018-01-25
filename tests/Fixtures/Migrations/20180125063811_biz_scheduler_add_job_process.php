<?php

namespace Phpmig\Migration\Migration;

use Phpmig\Migration\Migration;

class BizSchedulerAddJobProcess extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
          CREATE TABLE `biz_scheduler_job_process` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `process_id` varchar(32) NOT NULL DEFAULT '' COMMENT '进程组ID',
          `start_time` bigint(15) unsigned NOT NULL COMMENT '起始时间/毫秒',
          `end_time` bigint(15) unsigned NOT NULL COMMENT '终止时间/毫秒',
          `cost_time` int(11) unsigned NOT NULL COMMENT '花费时间/毫秒',
          `peak_memory` bigint(15) unsigned NOT NULL DEFAULT '0' COMMENT '内存峰值/byte',
          `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
          PRIMARY KEY (`id`),
          KEY `process_id` (`process_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $connection->exec("ALTER TABLE `biz_scheduler_job_fired` ADD COLUMN `process_id` int(10) unsigned DEFAULT NULL COMMENT 'jobProcessId';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("DROP TABLE `biz_scheduler_job_process`;");
        $connection->exec("ALTER TABLE `biz_scheduler_job_process` DROP COLUMN `process_id`;");
    }
}