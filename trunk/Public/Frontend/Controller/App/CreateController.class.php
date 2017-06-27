<?php
/**
 * 创建应用类
 * CreateBy: 原习斌
 * Date: 2016-08-15
 * Time: 11:46:12
 */

namespace Frontend\Controller\App;

use VcySDK\Service;
use VcySDK\Plugin;

class CreateController extends AbstractController
{

    protected $_require_login = false;

    public function Index()
    {

        $method = '_' . APP_IDENTIFIER;

        // 如果相应的获取应用方法不存在存在
        if (! method_exists($this, $method)) {
            $this->error('获取应用信息错误');
        }

        $param = $this->$method();

        $sdk = new Plugin(Service::instance());

        $re = $sdk->add($param);

        print_r($re);

        return true;
    }

    /**
     * 创建工作报告的请求数据
     *
     * @return array
     */
    protected function _Dailyreport()
    {
        return array(
            'plIdentifier' => 'dailyreport', // 插件标识名
            'pgGroupid' => 'T14EC159C0A806672ED4D349DA48B2C4', // 分组ID
            'plName' => '工作报告', // 应用名称
            'plIcon' =>'xxxx', // 图标地址
            'plDescription' => '测试工作报告', // 应用信息描述
            'thirdIdentifier' => 'qy', // 第三方标识 企业号：qy 服务号：mp
            'plVersion' => '2.0.0', // 版本号
            'plCallbackUrl' => 'http://%s/Dailyreport/Frontend/Callback/ThirdMessage', // 数据中心回调应用接口URL
            'qysSuiteid' => 'tj371afbea374f01b2',
            'appid' => 3,
            'plCallbackMsgurl' => 'http://%s/Dailyreport/Frontend/Callback/Install' // 应用安装成功后的回调Url
        );
    }
}
