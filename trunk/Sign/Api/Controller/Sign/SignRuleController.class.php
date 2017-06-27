<?php
/**
 * 签到规则获取接口
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-04-24 10:56:58
 * @version $Id$
 */

namespace Api\Controller\Sign;

use Common\Service\ConfigService;

class SignRuleController extends AbstractController
{

    public function Index_get()
    {
        // 获取签到配置
        $serv_config = new ConfigService();
        $sign_config = $serv_config->get_by_conds(array());

        $this->_result = array(
            'sign_rules' => $sign_config['sign_rules'],
        );

        return true;
    }

}
