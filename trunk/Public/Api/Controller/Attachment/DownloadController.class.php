<?php
/**
 * 微信(H5端)下载附件
 * Created by PhpStorm.
 * User: mr.song
 * Date: 2016/7/22
 * Time: 16:49
 */
namespace Api\Controller\Attachment;

use VcySDK\Message;
use VcySDK\Service;

class DownloadController extends AbstractController
{

    public function Index()
    {

        $at_id = I("get.atid");

        // 判断附件ID是否为空
        if (empty($at_id)) {
            $this->_set_error('_ERR_ATID_EMPTY');
            return false;
        }

        // SDK获取应用列表
        $serv = &Service::instance();
        $sdkMessage = new Message($serv);
        // 推送文件到应用主页面
        $data = array(
            "toUser" => $this->_login->user["memUid"],
            "msgtype" => "file",
            "atId" => $at_id
        );
        $sdkMessage->sendFile($data);

        return true;
    }
}
