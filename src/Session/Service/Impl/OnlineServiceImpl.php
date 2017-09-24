<?php

namespace Codeages\Biz\Framework\Session\Service\Impl;

use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Session\Service\OnlineService;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class OnlineServiceImpl extends BaseService implements OnlineService
{
    public function sample($online)
    {
        if (!empty($online['sess_id'])) {
            $savedOnine = $this->getOnlineBySessId($online['sess_id']);
            if (empty($savedOnine)) {
                $this->createOnline($online);
            } else {
                $this->updateOnline($savedOnine['id'], $online);
            }
        }
    }

    public function createOnline($online)
    {
        if(!ArrayToolkit::requireds($online, array('sess_id'))) {
            throw new InvalidArgumentException('sess_id is required.');
        }
        $user = $this->biz['user'];
        if (!empty($user['id'])) {
            $online['user_id'] = $user['id'];
        }
        $online = ArrayToolkit::parts($online, array('sess_id' , 'user_id', 'access_url', 'ip', 'user_agent', 'source'));
        return $this->getOnlineDao()->create($online);
    }

    public function updateOnline($id, $online)
    {
        $user = $this->biz['user'];
        if (!empty($user['id'])) {
            $online['user_id'] = $user['id'];
        }
        $online = ArrayToolkit::parts($online, array('sess_id' , 'user_id', 'access_url', 'ip', 'user_agent', 'source'));
        return $this->getOnlineDao()->update($id, $online);
    }

    public function getOnlineBySessId($sessId)
    {
        return $this->getOnlineDao()->getBySessId($sessId);
    }

    public function countLogined($ltAccessTime)
    {
        $condition = array(
            'lt_access_time' => $ltAccessTime,
            'gt_user_id' => 0
        );
        return $this->getOnlineDao()->count($condition);
    }

    public function countOnline($ltAccessTime)
    {
        $condition = array(
            'lt_access_time' => $ltAccessTime,
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