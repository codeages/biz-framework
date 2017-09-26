<?php

namespace Codeages\Biz\Framework\Session\Service\Impl;

use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Session\Service\OnlineService;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class OnlineServiceImpl extends BaseService implements OnlineService
{
    public function saveOnline($online)
    {
        if(!ArrayToolkit::requireds($online, array('sess_id', 'sess_deadline'))) {
            throw new InvalidArgumentException('sess_id, sess_deadline is required.');
        }
        $user = $this->biz['user'];
        if (!empty($user['id'])) {
            $online['user_id'] = $user['id'];
            $online['is_login'] = 1;
        }

        $online = ArrayToolkit::parts($online, array(
            'sess_deadline',
            'sess_id',
            'user_id',
            'is_login',
            'ip',
            'user_agent',
            'source',
        ));

        if (!empty($online['sess_id'])) {
            $savedOnine = $this->getOnlineBySessId($online['sess_id']);

            if (empty($savedOnine)) {
                $this->getOnlineDao()->create($online);
            } else {
                $this->getOnlineDao()->update($savedOnine['id'], $online);
            }
        }
    }

    public function getOnlineBySessId($sessId)
    {
        return $this->getOnlineDao()->getBySessId($sessId);
    }

    public function countLogined($gtAccessTime)
    {
        $condition = array(
            'gt_sess_time' => $gtAccessTime,
            'is_login' => 1
        );
        return $this->getOnlineDao()->count($condition);
    }

    public function countOnline($gtAccessTime)
    {
        $condition = array(
            'gt_sess_time' => $gtAccessTime,
            'is_login' => 0
        );
        return $this->getOnlineDao()->count($condition);
    }
    public function gc()
    {
        return $this->getOnlineDao()->deleteByInvalid();
    }

    public function searchOnlines($condition, $orderBy, $start, $limit)
    {
        return $this->getOnlineDao()->search($condition, $orderBy, $start, $limit);
    }

    public function countOnlines($condition)
    {
        return $this->getOnlineDao()->count($condition);
    }

    protected function getOnlineDao()
    {
        return $this->biz->dao('Session:OnlineDao');
    }
}