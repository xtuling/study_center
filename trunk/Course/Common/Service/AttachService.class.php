<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/12
 * Time: 10:34
 */
namespace Common\Service;

use Common\Model\AttachModel;

class AttachService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new AttachModel();
    }
}
