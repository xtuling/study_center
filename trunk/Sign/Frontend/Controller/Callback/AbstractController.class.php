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
        $service = &Service::instance();
        $this->callBackData = $service->streamJsonData();

        return parent::before_action($action);
    }

    public function after_action($action = '')
    {

        parent::after_action($action);
        exit('SUCCESS');
    }

    /**
     * 获取 identifier
     * @return bool
     */
    protected function _identifier()
    {

        return true;
    }


    /**格式化考试的封面图片地址
     *
     * @param $cover_id - 封面 ID
     * @return string       - 封面 URL
     */
    public function format_cover($cover_id)
    {
        $cover_url = '';
        if (!empty($cover_id)) {
            $cover_url = imgUrl($cover_id);
        } else {
            $cover_url = cfg('PROTOCAL') . $_SERVER['HTTP_HOST'] . '/admincp/imgs/client/images/exam/examCover.png';

        }

        return $cover_url;
    }
}
