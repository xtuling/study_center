<?php
/**
 * SyscacheService.class.php
 * 问卷调查缓存表
 * @author: dj
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
