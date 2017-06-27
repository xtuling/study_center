<?php
/**
 * 删除激励
 * DeleteController.class.php
 * User: 何岳龙
 * Date: 2017年6月1日14:46:37
 */

namespace Apicp\Controller\Encourage;

class DeleteController extends AbstractController
{

    public function Index_post()
    {

        $params = I('post.');

        // 数据验证
        if (!$this->medal_serv->medal_info_validation($params)) {

            return false;
        }

        // 删除激励
        $this->medal_serv->delete($params['em_id']);

        // 返回数据
        $this->_result = array();

        return true;
    }

}
