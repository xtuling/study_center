<?php
/**
 * 保存签到配置接口
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-04-26 10:16:14
 * @version $Id$
 */

namespace Apicp\Controller\Sign;

use Common\Service\ConfigService;

class ConfigSaveController extends AbstractController
{

    public function Index_post()
    {
        $ser = new ConfigService();

        // 保存签到配置
        if (!$ser->save_config($this->_result, I('post.'))) {

            return false;
        }

        return true;
    }
}
