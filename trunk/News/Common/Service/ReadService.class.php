<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/12
 * Time: 10:38
 */
namespace Common\Service;

use Common\Model\ReadModel;

class ReadService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new ReadModel();
    }
}
