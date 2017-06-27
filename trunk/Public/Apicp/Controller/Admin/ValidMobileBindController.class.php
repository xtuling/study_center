<?php
/**
 * Created by IntelliJ IDEA.
 * 验证手机号是否可以绑定(单点登录绑定手机号使用)
 * User: zhoutao
 * Date: 16/9/22
 * Time: 上午10:28
 */

namespace Apicp\Controller\Admin;

use VcySDK\Adminer;
use VcySDK\Service;

class ValidMobileBindController extends AbstractController
{

    public function Index()
    {

        $phone = I('post.eaMobile', '', 'strval');

        $adminSdk = new Adminer(Service::instance());
        $this->_result = $adminSdk->validMobileBind([
            'eaMobile' => $phone,
        ]);

        return true;
    }
}
