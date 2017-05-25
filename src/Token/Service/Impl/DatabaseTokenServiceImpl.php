<?php
namespace Codeages\Biz\Framework\Token\Service\Impl;

use Codeages\Biz\Framework\Token\Dao\TokenDao;
use Codeages\Biz\Framework\Token\Service\TokenService;
use Codeages\Biz\Framework\Service\BaseService;

class DatabaseTokenServiceImpl extends BaseService implements TokenService
{
    public function generate($place, $lifetime, $times = 0, $data = null)
    {
        $token = array();
        $token['place'] = $place;
        $token['_key'] = $this->_makeTokenValue(32);
        $token['data'] = $data;
        $token['expired_time'] = empty($lifetime) ? 0 : time() + $lifetime;
        $token['times'] = $times;
        $token['remaining_times'] = $times;
        $token['created_time'] = time();

        $token = $this->getTokenDao()->create($token);

        return $this->filter($token);
    }

    public function verify($place, $key)
    {
        $token = $this->getTokenDao()->getByKey($key);

        if (empty($token)) {
            return false;
        }

        if ($token['place'] != $place) {
            return false;
        }

        if (($token['expired_time'] > 0) && ($token['expired_time'] < time())) {
            $this->getTokenDao()->delete($token['id']);
            return false;
        }

        if ($token['times'] > 0 && ($token['remaining_times'] < 1)) {
            $this->getTokenDao()->delete($token['id']);
            return false;
        }

        if ($token['remaining_times'] >= 1) {
            $this->getTokenDao()->wave(array($token['id']), array('remaining_times' => -1));
            $token['remaining_times'] -= 1;
        }

        if ($token['times'] > 0 && $token['remaining_times'] == 0) {
            $this->getTokenDao()->delete($token['id']);
        }

        return $this->filter($token);
    }

    public function destroy($key)
    {
        $token = $this->getTokenDao()->getByKey($key);
        if (empty($token)) {
            return;
        }

        $this->getTokenDao()->delete($token['id']);
    }

    protected function _makeTokenValue($length)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $value = '';

        for ($i = 0; $i < $length; ++$i) {
            $value .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $value;
    }

    protected function filter($token)
    {
        $token['key'] = $token['_key'];
        unset($token['_key']);
        return $token;
    }

    /**
     * @return TokenDao
     */
    protected function getTokenDao()
    {
        return $this->biz->dao('Token:TokenDao');
    }
}
