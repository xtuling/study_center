<?php
/**
 * 前台选人组件 JsSdk 权限验证
 * AuthChooseController.class.php
 * $author$ 何岳龙
 * $date$   2016年8月29日11:26:24
 */

namespace Api\Controller\ChooseMem;

use VcySDK\Service;
use VcySDK\WxQy\WebAuth;

class AuthController extends AbstractController
{

    public function Index()
    {

        // 获取GET参数
        $get = I('get.');

        // 实例化
        $service = new WebAuth(Service::instance());

        // 获取url
        $url = isset($_SERVER['HTTP_REFERER']) ? preg_replace('/(#.+?)$/', '', $_SERVER['HTTP_REFERER']) : '';

        // 如果随机字符和时间戳存在
        if (!empty($get['noncestr']) && !empty($get['timestamp'])) {
            $condition['noncestr'] = $get['noncestr'];
            $condition['time'] = $get['timestamp'];
        }

        $condition['url'] = $url;

        // 获取返回数据
        $this->_result = $service->jsGroupSignAture($condition);

        return true;
    }

}
