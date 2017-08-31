<?php

use Phpmig\Migration\Migration;

class CreateOnlineTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `biz_online` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
              `sess_id` varchar(128) NOT NULL,
              `user_id` int(10) unsigned NOT NULL DEFAULT '0',
              `access_time` int(10) unsigned NOT NULL,
              `access_url` int(10) unsigned NOT NULL,
              `created_time` int(10) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `sess_id` (`sess_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
