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
            CREATE TABLE IF NOT EXISTS `job_pool` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
              `name` varchar(1024) NOT NULL DEFAULT 'default' COMMENT '组名',
              `maxNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最大数',
              `num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已使用的数量',
              `timeout` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行超时时间',
              `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `job_detail` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
              `name` varchar(1024) NOT NULL COMMENT '任务名称',
              `pool` varchar(1024) NOT NULL DEFAULT 'default' COMMENT '所属组',
              `source` varchar(1024) NOT NULL DEFAULT 'MAIN' COMMENT '来源',
              `expression` varchar(1024) NOT NULL DEFAULT '' COMMENT '任务触发的表达式',
              `class` varchar(1024) NOT NULL COMMENT '任务的Class名称',
              `data` text COMMENT '任务参数',
              `priority` int(10) unsigned NOT NULL DEFAULT 50 COMMENT '优先级',
              `status` varchar(1024) NOT NULL DEFAULT 'waiting' COMMENT '任务执行状态, waiting, acquired, executing, finished, missed',
              `preFireTime` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '任务下次执行的时间',
              `nextFireTime` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '任务下次执行的时间',
              `misfireThreshold` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '触发过期时间，单位秒',
              `misfirePolicy` varchar(1024) NOT NULL COMMENT '触发过期策略: missed, executing, ',
              `enabled` tinyint(1) DEFAULT 1 COMMENT '是否启用',
              `creatorId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务创建人',
              `updatedTime` int(10) unsigned NOT NULL COMMENT '修改时间',
              `createdTime` int(10) unsigned NOT NULL COMMENT '任务创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `job_fired` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
              `jobDetailId` varchar(1024) NOT NULL COMMENT '任务名称',
              `firedTime` int(10) unsigned NOT NULL COMMENT '触发时间',
              `priority` int(10) unsigned NOT NULL DEFAULT 50 COMMENT '优先级',
              `status` varchar(1024) NOT NULL DEFAULT 'created' COMMENT '状态：created, executing',
              `updatedTime` int(10) unsigned NOT NULL COMMENT '修改时间',
              `createdTime` int(10) unsigned NOT NULL COMMENT '任务创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `job_log` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
              `jobDetailId` int(10) unsigned NOT NULL COMMENT '任务编号',
              `jobFiredId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '激活的任务编号',
              `hostname` varchar(1024) NOT NULL DEFAULT '' COMMENT '执行的主机',
              `name` varchar(1024) NOT NULL COMMENT '任务名称',
              `pool` varchar(1024) NOT NULL DEFAULT 'default' COMMENT '所属组',
              `source` varchar(1024) NOT NULL COMMENT '来源',
              `class` varchar(1024) NOT NULL COMMENT '任务的Class名称',
              `data` text COMMENT '任务参数',
              `priority` int(10) unsigned NOT NULL DEFAULT 50 COMMENT '优先级',
              `status` varchar(1024) NOT NULL DEFAULT 'waiting' COMMENT '任务执行状态',
              `createdTime` int(10) unsigned NOT NULL COMMENT '任务创建时间',
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
