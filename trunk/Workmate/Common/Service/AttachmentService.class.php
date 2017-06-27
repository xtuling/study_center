<?php
/**
 * 同事圈信息表
 * User: 代军
 * Date: 2017-04-24
 */
namespace Common\Service;

use Common\Model\AttachmentModel;

class AttachmentService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        $this->_d = new AttachmentModel();

        parent::__construct();
    }
}

