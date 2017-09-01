<?php

use Phpmig\Migration\Migration;

class AddOrderPayment extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        if (!$this->isFieldExist('biz_order', 'payment')) {
            $connection->exec(
                "ALTER TABLE `biz_order` ADD COLUMN `payment` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '支付类型' AFTER `pay_time`;"
            );
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        if ($this->isFieldExist('biz_order', 'payment')) {
            $connection->exec(
                "ALTER TABLE `biz_order` DROP COLUMN `payment`"
            );
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $db->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
