<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/2/8
 * Time: 15:43
 */
namespace Common\Service;

use Common\Model\SettingModel;

class SettingService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new SettingModel();
    }
}
