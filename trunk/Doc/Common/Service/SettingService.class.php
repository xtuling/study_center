<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/5/18
 * Time: 11:37
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
