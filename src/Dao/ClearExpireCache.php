<?php

namespace Codeages\Biz\Framework\Dao;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Dao\CacheStrategy\RowStrategy;

class ClearExpireCache
{
    const DEFAULT_MISS_TIME = 9;

    const MAX_CLEAR_ROW_COUNT = 1000;

    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @param $clearDaos [ ['class' => daoImpl::class, 'isMillisecond' => boolean, 'updatedTimeColumn' => 'updatedTime'], ... ]
     * @return array
     */
    public function clear($clearDaos)
    {
        $clearedCount = array();

        foreach ($clearDaos as $dao) {
            $clearedCount[$dao['class']] = $this->clearDaoCache($dao['class'], $dao['isMillisecond'], $dao['updatedTimeColumn']);
        }

        return $clearedCount;
    }

    private function clearDaoCache($daoName, $isMillisecond = false, $updatedTimeColumn = 'updatedTime')
    {
        $strategy = new RowStrategy($this->biz['redis'], $this->biz['dao.metadata_reader']);

        $dao = new $daoName($this->biz);

        $idAndUpdatedTimes = $dao->pickIdAndUpdatedTimesByUpdatedTimeGT($this->getLastClearTimestamp($daoName, $isMillisecond), 0, self::MAX_CLEAR_ROW_COUNT, $updatedTimeColumn);
        $lastUpdatedTime = 0;

        foreach ($idAndUpdatedTimes as $idAndUpdatedTime) {
            $strategy->afterUpdate($dao, null, null, array('id' => $idAndUpdatedTime['id']));
            $lastUpdatedTime = $idAndUpdatedTime[$updatedTimeColumn];
        }

        $this->setLastClearTimestamp($daoName, $lastUpdatedTime);

        return count($idAndUpdatedTimes);
    }

    /**
     * @param $daoName
     * @param bool $isMillisecond
     * @return bool|mixed|string
     */
    private function getLastClearTimestamp($daoName, $isMillisecond = false)
    {
        $timestamp = $this->biz['redis']->get($this->getClearTimestampCacheKey($daoName));

        if (empty($timestamp)) {
            $timestamp = (time() - self::DEFAULT_MISS_TIME) * ($isMillisecond ? 1000 : 1);
        }

        return $timestamp;
    }

    /**
     * @param $daoName
     * @param $timestamp
     */
    private function setLastClearTimestamp($daoName, $timestamp)
    {
        $this->biz['redis']->set($this->getClearTimestampCacheKey($daoName), $timestamp);
    }

    protected function getClearTimestampCacheKey($daoName)
    {
        return "clear_expired_cache:last_timestamp:" . substr(strrchr($daoName, '\\'), 1);
    }
}