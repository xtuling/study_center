<?php
/**
 * Frontend\Controller\Callback\UploadController
 * 上传文件回调处理
 * @author: Deepseath
 * @version: $Id$
 */

namespace Frontend\Controller\Callback;

use VcySDK\Service;
use VcySDK\Attach;
use Common\Service\AnswerAttachService;
use Think\Log;

class UploadController extends AbstractController
{

    /** 当前处理的答卷详情 ID */
    private $__ead_id = 0;
    /** 当前处理的文件 ID */
    private $__order_id = '';

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        // 不需要强制登录访问
        $this->_require_login = false;

        return true;
    }

    public function Index()
    {
        $serv = &Service::instance();
        $attach = new Attach($serv);

        // 答卷详情 ID
        $this->__ead_id = I('get.ead_id');

        // 文件 ID
        $this->__order_id = I('get.order_id');

        if (empty($this->__ead_id)) {
            // 未指定答卷详情 id
            return $this->__output(1);
        }
        if (empty($this->__order_id)) {
            // 未指定文件 id
            return $this->__output(2);
        }

        // 获取文件信息
        $count_attach_serv = new AnswerAttachService();
        $attachment = $count_attach_serv->getByOrderid($this->__order_id, $this->__ead_id);
        if (empty($attachment)) {
            // 无法找到文件信息
            return $this->__output(3);
        }
        if (!isset($attachment['is_complete'])) {
            // 未知的数据
            return $this->__output(4);
        }
        if ($attachment['is_complete'] != AnswerAttachService::IS_COMPLETE_NO) {
            // 已转换完毕不需要再次处理
            return $this->__output(5);
        }

        // 标记转换完毕
        $count_attach_serv->update($attachment['atta_id'], [
            'is_complete' => AnswerAttachService::IS_COMPLETE_YES
        ]);

        return $this->__output(0);
    }

    /**
     * 输出返回
     * @param number $error_code 0=成功
     */
    private function __output($error_code = 0)
    {
        // $error_code
        if ($error_code) {
            Log::record("ead_id:{$this->__ead_id}; order_id:{$this->__order_id}; errcode: {$error_code}");

            return false;
        }

        exit('success');
    }

}
