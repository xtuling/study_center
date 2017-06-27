<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/5/25
 * Time: 17:36
 */

namespace Apicp\Controller\Doc;

use Common\Common\Attach;
use VcySDK\Service;
use VcySDK\Cron;
use VcySDK\FileConvert;

class TestController extends \Apicp\Controller\AbstractController
{
    protected $_require_login = false;

    /**
     * 测试接口
     * @author liyifei
     */
    public function Index_post()
    {
        // 定时任务详情
        $cronSdk = new Cron(Service::instance());
        $cron_id = '8CCE8F9C7F0000015420C966CA97A9C3';
//        $res = $cronSdk->get($cron_id);

        // 文件详情
        $at_id = 'CF06ED357F0000013D2D79C2ADB2DDA0';
        $attachServ = &Attach::instance();
        $res = $attachServ->getAttachDetail($at_id);

        // 文件转码
        $at_id = '8CCE8EA37F0000017E93B769E2D5DF84';
        $convertServ = new FileConvert(Service::instance());
        $param = [
            'atIds' => [$at_id],
            'convertType' => 1, // html
        ];
//        $res = $convertServ->convert($param);

        // 文件转码详情
//        $res = $convertServ->get($at_id);

        var_dump($res);
        exit;

    }
}