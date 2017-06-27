<?php
/**
 * 图片上传回调
 * Created by PhpStorm.
 * User: mr.song
 * Date: 2016/7/22
 * Time: 16:49
 */

namespace Api\Controller\Attachment;

use Common\Common\Attach;
use Common\Common\Cache;
use Common\Common\Login;
use VcySDK\Service;

class UploadCallbackController extends AbstractController
{
    /**
     * 用户登陆
     *
     * @return boolean
     */
    protected function _userLogin()
    {
        $this->_login = &Login::instance();
        return true;
    }

    public function Index()
    {
        // 接收消息
        $serviceSdk = &Service::instance();
        $callbackData = $serviceSdk->streamJsonData();

        // 没有收到消息
        if (empty($callbackData)) {
            return false;
        }

        $atId = $callbackData['atId'];
        if (isset($callbackData['code']) && $callbackData['code'] == "ERROR") {
            Attach::instance()->setErrorFlag($atId);
        } else {
            Attach::instance()->clearFlag($atId);
        }

        exit('success');
    }
}
