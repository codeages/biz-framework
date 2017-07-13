<?php

use Phpmig\Migration\Migration;

class Session extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `sessions` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `sess_id` varbinary(128) NOT NULL,
              `sess_user_id` int(10) unsigned NOT NULL DEFAULT '0',
              `sess_data` blob NOT NULL,
              `sess_time` int(10) unsigned NOT NULL,
              `sess_lifetime` mediumint(9) NOT NULL,
              `type` VARCHAR(128) NOT NULL DEFAULT 'normal',
              PRIMARY KEY (`id`),
              UNIQUE KEY `sess_id` (`sess_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("drop table `sessions`;");
    }
}
