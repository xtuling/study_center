<?php

/**
 * Created by PhpStorm.
 * 赋予勋章RPC测试接口
 */

namespace Api\Controller\Medal;

use Api\Controller\Integral\AbstractController;

class EndowController extends AbstractController
{

    public function Index_post()
    {
        $rpcURL = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/' . QY_DOMAIN . '/Integral/Rpc/Medal/Endow';
        $postData = [$_POST['im_id'],$this->uid, $this->_login->user['memUsername']];
        return \Com\Rpc::phprpc($rpcURL)->invoke('Index', $postData);
    }
}
