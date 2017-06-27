<?php
/**
 * MedalService.class.php
 * 人员获得勋章日志表 Service
 */

namespace Common\Service;

use Common\Model\MedalLogModel;

class MedalLogService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new MedalLogModel();
    }
}
