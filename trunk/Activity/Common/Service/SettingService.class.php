<?php
/**
 * SettingService.class.php
 * 问卷调查设置表
 * @author: dj
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
