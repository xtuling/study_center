<?php
/**
 * Created by IntelliJ IDEA.
 * 验证手机号的唯一性
 * User: zhoutao
 * Date: 16/9/22
 * Time: 上午10:28
 */

namespace Apicp\Controller\Admin;

use VcySDK\Adminer;
use VcySDK\Service;

class ValidMobileController extends AbstractAnonymousController
{

    public function Index()
    {

        $phone = I('post.eaMobile', '', 'strval');
        $postData = [
            'eaMobile' => $phone
        ];
        $enumber = I('post.epEnumber', '', 'strval');
        if (! empty($enumber)) {
            $postData['epEnumber'] = $enumber;
        }

        $adminSdk = new Adminer(Service::instance());
        $adminSdk->validMoblie($postData);

        return true;
    }
}
