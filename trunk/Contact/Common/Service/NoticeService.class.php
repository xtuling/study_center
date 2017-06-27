<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 2016-11-25 10:26:43
 */
namespace Common\Service;

use Common\Model\NoticeModel;

class NoticeService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new NoticeModel();
    }
}
