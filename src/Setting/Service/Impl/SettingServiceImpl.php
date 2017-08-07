<?php

namespace Codeages\Biz\Framework\Setting\Service\Impl;

use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Setting\Service\SettingService;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

class SettingServiceImpl extends BaseService implements SettingService
{
    protected $cache = null;

    public function get($name, $default = null)
    {
        list($name, $subName) = $this->splitName($name);

        $setting = $this->getByName($name);
        if (!$setting) {
            return $default;
        }

        if ($subName && (!is_array($setting['data']) || !isset($setting['data'][$subName]))) {
            return $default;
        }

        if (is_null($subName)) {
            return $setting['data'];
        }

        return $setting['data'][$subName];
    }

    public function set($name, $data)
    {
        list($name, $subName) = $this->splitName($name);

        $setting = $this->getByName($name);
        if ($setting) {
            if ($subName && !is_array($setting['data'])) {
                throw new ServiceException("Setting `{$name}` is not array, so it not support dot syntax.");
            }
            if ($subName) {
                $data = array_merge($setting['data'], array($subName => $data));
            }
            $this->getSettingDao()->update($setting['id'], array(
                'data' => $data,
            ));
        } else {
            if ($subName) {
                $data = array($subName => $data);
            }
            $this->getSettingDao()->create(array(
                'name' => $name,
                'data' => $data,
            ));
        }
        $this->cache = null;
    }

    public function remove($name)
    {
        list($name, $subName) = $this->splitName($name);

        $setting = $this->getByName($name);
        if (empty($setting)) {
            throw new ServiceException("Setting {$name} is not exist, delte failed.");
        }

        if ($subName) {
            if (!is_array($setting['data'])) {
                throw new ServiceException("Setting `{$name}` is not array, so it not support dot syntax.");
            }
            if (!isset($setting['data'][$subName])) {
                throw new ServiceException("Setting {$name}.{$subName} is not exist, deleted failed.");
            }

            unset($setting['data'][$subName]);

            $this->getSettingDao()->update($setting['id'], array(
                'data' => $setting['data'],
            ));
        } else {
            $this->getSettingDao()->delete($setting['id']);
        }
        $this->cache = null;
    }

    private function getByName($name)
    {
        if (!$this->cache) {
            $settings = $this->getSettingDao()->findAll();
            $settings = array_column($settings, null, 'name');
            $this->cache = $settings;
        } else {
            $settings = $this->cache;
        }

        if (!isset($settings[$name])) {
            return null;
        }

        return $settings[$name];
    }

    private function splitName($name)
    {
        $parts = explode('.', $name, 2);
        if (count($parts) == 2) {
            $name = $parts[0];
            $subName = $parts[1];
        } else {
            $subName = null;
        }

        return array($name, $subName);
    }

    /**
     * @return Codeages\Biz\Framework\Setting\Dao\SettingDao
     */
    protected function getSettingDao()
    {
        return $this->biz->dao('Setting:SettingDao');
    }
}
