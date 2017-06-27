<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/26
 * Time: 20:41
 */

namespace Common\Service;

use Common\Model\InviteUserRightModel;

class InviteUserRightService extends AbstractService
{

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_d = new InviteUserRightModel();
    }

}
