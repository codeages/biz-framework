<?php

use Phpmig\Migration\Migration;

class Scheduler extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `job_pool` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
              `group` varchar(1024) NOT NULL DEFAULT 'default' COMMENT '组名',
              `maxNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最大数',
              `num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已使用的数量',
              `timeOut` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行超时时间',
              `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
