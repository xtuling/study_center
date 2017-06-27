<?php
/**
 * Created by PhpStorm.
 * 应用回调控制层
 * User: zhoutao
 * Date: 16/7/14
 * Time: 下午2:56
 */

namespace Frontend\Controller\Callback;

use VcySDK\Service;

abstract class AbstractController extends \Common\Controller\Frontend\AbstractController
{

    // 回调数据
    protected $callBackData = [];

    public function before_action($action = '')
    {

        // 不必登陆
        $this->_require_login = false;
        // 接收消息
        $serviceSdk = &Service::instance();
        $this->callBackData = $serviceSdk->streamJsonData();

        return parent::before_action($action);
    }

    /**
     * 后置操作
     *
     * @param string $action
     *
     * @return bool
     */
    public function after_action($action = '')
    {

        exit('success');
        return true;
    }

    /**
     * 获取 identifier
     *
     * @return bool
     */
    protected function _identifier()
    {

        return true;
    }
}
