<?php
/**
 * 编辑激励
 * SaveController.class.php
 * User: 何岳龙
 * Date: 2017年6月1日14:46:50
 */

namespace Apicp\Controller\Encourage;

class SaveController extends AbstractController
{
    public function Index_post()
    {

        $params = I('post.');

        // 保存激励验证
        if (!$this->medal_serv->save_medal_validation($params)) {

            return false;
        }

        // 保存激励
        if (!$this->medal_serv->update_medal_data($params)) {

            return false;
        }

        // 返回数据
        $this->_result = array();

        return true;
    }

}
