<?php

namespace Codeages\Biz\Framework\Session\Service\Impl;

use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Session\Service\OnlineService;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use DeviceDetector\DeviceDetector;

class OnlineServiceImpl extends BaseService implements OnlineService
{
    public function sample($online)
    {
        if (!empty($online['sess_id'])) {
            $savedOnine = $this->getOnlineBySessId($online['sess_id']);
            if (!empty($online['user_agent'])) {
                $detector = new DeviceDetector($online['user_agent']);
                $detector->parse();
                $online['device'] = $detector->getDeviceName();
                $online['device_brand'] = $detector->getBrandName();
                $online['os'] = $detector->getOs();
                $online['client'] = $detector->getClient();
            }

            if (empty($savedOnine)) {
                $this->createOnline($online);
            } else {
                $this->updateOnline($savedOnine['id'], $online);
            }
        }
    }

    public function createOnline($online)
    {
        if(!ArrayToolkit::requireds($online, array('sess_id', 'lifetime'))) {
            throw new InvalidArgumentException('sess_id is required.');
        }
        $user = $this->biz['user'];
        if (!empty($user['id'])) {
            $online['user_id'] = $user['id'];
        }
        $online = ArrayToolkit::parts($online, array(
            'lifetime',
            'sess_id',
            'user_id',
            'access_url',
            'ip',
            'user_agent',
            'source',
            'device',
            'os',
            'client',
            'device_brand'
        ));
        return $this->getOnlineDao()->create($online);
    }

    public function updateOnline($id, $online)
    {
        $user = $this->biz['user'];
        if (!empty($user['id'])) {
            $online['user_id'] = $user['id'];
        }
        $online = ArrayToolkit::parts($online, array(
            'lifetime',
            'sess_id',
            'user_id',
            'access_url',
            'ip',
            'user_agent',
            'source',
            'device',
            'os',
            'client',
            'device_brand'
        ));

        return $this->getOnlineDao()->update($id, $online);
    }

    public function getOnlineBySessId($sessId)
    {
        return $this->getOnlineDao()->getBySessId($sessId);
    }

    public function countLogined($gtAccessTime)
    {
        $condition = array(
            'gt_access_time' => $gtAccessTime,
            'gt_user_id' => 0
        );
        return $this->getOnlineDao()->count($condition);
    }

    public function countOnline($gtAccessTime)
    {
        $condition = array(
            'gt_access_time' => $gtAccessTime,
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