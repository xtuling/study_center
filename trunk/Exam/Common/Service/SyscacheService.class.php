<?php
/**
 * SyscacheService.class.php
 * 活动中心缓存表
 * @author: houyingcai
 * @copyright: vchangyi.com
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
