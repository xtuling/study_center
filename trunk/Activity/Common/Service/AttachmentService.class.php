<?php
/**
 * AttachmentService.class.php
 * 活动评论附件信息表
 * @author: daijun
 * @copyright: vchangyi.com
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
