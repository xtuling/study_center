<?php
/**
 * 获取指定缓存
 * User: zhuxun37
 * Date: 2017/5/12
 * Time: 上午10:32
 */

namespace Apicp\Controller\Setting;


use Common\Common\Cache;

class GetController extends AbstractController
{

    public function Index_post()
    {

        $key = (string)I('post.key');
        if (empty($key)) {
            E('1001:参数错误');
            return false;
        }

        $settings = Cache::instance()->get('Common.AppSetting');
        if (empty($settings[$key])) {
            E('1002:参数错误');
            return false;
        }

        $this->_result = empty($settings[$key]['value']) ? array() : $settings[$key]['value'];
        return true;
    }

}