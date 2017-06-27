<?php
/**
 * 缓存表信息
 * User: 代军
 * Date: 2017-04-24
 */

namespace Common\Service;

use Common\Model\SyscacheModel;

class SyscacheService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        $this->_d = new SyscacheModel();

        parent::__construct();
    }
}

