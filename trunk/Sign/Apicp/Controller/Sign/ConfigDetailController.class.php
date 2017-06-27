<?php
/**
 * 获取签到配置详情
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-04-26 09:57:32
 * @version $Id$
 */

namespace Apicp\Controller\Sign;

use Common\Service\ConfigService;

class ConfigDetailController extends AbstractController
{

    public function Index_post()
    {
        $serv_config = new ConfigService();

        // 获取签到配置信息
        $config = $serv_config->get_by_conds(array());
        $config['integral_rules'] = unserialize($config['integral_rules']);

        // 剔除多余字段
        unset($config['domain']);
        unset($config['status']);
        unset($config['created']);
        unset($config['updated']);
        unset($config['deleted']);

        $this->_result = $config;

        return true;
    }
}
