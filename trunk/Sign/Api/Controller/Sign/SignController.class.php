<?php
/**
 * 签到
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-04-24 10:18:44
 * @version $Id$
 */

namespace Api\Controller\Sign;

use Common\Service\ConfigService;
use Common\Service\CountService;

class SignController extends AbstractController
{

    public function Index_get()
    {
        $ser_config = new ConfigService();
        $ser_count  = new CountService();

        // 初始化签到配置
        if (!$ser_config->set_default_data()) {

            E('_ERR_SIGN_CONFIG_FAILED');
            return false;
        }
        
        // 用户签到
        if (!$ser_count->user_sign($this->_result, $this->_login->user)) {

            E('_ERR_SIGN_FAILED');
            return false;
        }

        return true;
    }

}
