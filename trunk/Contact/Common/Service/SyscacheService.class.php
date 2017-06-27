<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/18
 * Time: 14:44
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
