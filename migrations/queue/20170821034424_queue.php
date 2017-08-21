<?php

use Phpmig\Migration\Migration;

class Queue extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `biz_queue_job` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `queue` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
                `class` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `body` longtext COLLATE utf8_unicode_ci NOT NULL,
                `attempts` tinyint(3) unsigned NOT NULL DEFAULT '0',
                `reserved_time` int(10) unsigned DEFAULT '0',
                `available_time` int(10) unsigned NOT NULL DEFAULT '0',
                `expired_time` int(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `idx_queue_reserved_time` (`queue`,`reserved_time`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

            CREATE TABLE `biz_queue_failed_job` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `connection` text COLLATE utf8_unicode_ci NOT NULL,
                `queue` text COLLATE utf8_unicode_ci NOT NULL,
                `body` longtext COLLATE utf8_unicode_ci NOT NULL,
                `error` longtext COLLATE utf8_unicode_ci NOT NULL,
                `failed_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("DROP TABLE `biz_queue_job`");
        $connection->exec("DROP TABLE `biz_queue_failed_job`");
    }
}
