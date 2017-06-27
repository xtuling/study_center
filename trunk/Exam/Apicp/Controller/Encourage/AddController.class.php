<?php
/**
 * 新增激励
 * AddController.class.php
 * User: 何岳龙
 * Date: 2017年6月1日14:46:56
 */

namespace Apicp\Controller\Encourage;

class AddController extends AbstractController
{
    public function Index_post()
    {

        $params = I('post.');

        // 添加激励验证
        if (!$this->medal_serv->add_medal_validation($params)) {

            return false;
        }

        // 添加激励
        if (!$this->medal_serv->add_medal($params)) {

            return false;
        }

        // 返回数据
        $this->_result = array();

        return true;
    }

}
