<?php
/**
 * SettingService.class.php
 * 培训设置表
 * @author: zhuxun37
 * @version: $Id$
 * @copyright: vchangyi.com
 */
namespace Common\Service;

use Common\Model\SyscacheModel;

class SyscacheService extends AbstractService
{

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_d = new SyscacheModel();
    }
}
