<?php
/**
 * SettingService.class.php
 * 签到设置表
 * @author: houyingcai
 * @copyright: vchangyi.com
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
}
