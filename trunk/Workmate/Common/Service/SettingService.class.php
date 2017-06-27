<?php
/**
 * 配置表信息
 * User: 代军
 * Date: 2017-04-24
 */
namespace Common\Service;

use Common\Model\SettingModel;

class SettingService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        $this->_d = new SettingModel();

        parent::__construct();
    }

    /*
     * 获取 setting 的缓存信息
     *
     */
    public function Setting()
    {

        // 获取全部数据
        $listAll = $this->_d->list_all();

        // 获取键值对
        $setting = [];
        if (!empty($listAll)) {
            foreach ($listAll as $_set) {
                $setting[$_set['key']] = $_set['value'];
            }
        }

        return $setting;
    }
}

