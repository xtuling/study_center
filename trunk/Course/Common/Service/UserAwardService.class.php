<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2017/6/5
 * Time: 14:33
 */
namespace Common\Service;

use Common\Model\UserAwardModel;

class UserAwardService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new UserAwardModel();
    }
}
