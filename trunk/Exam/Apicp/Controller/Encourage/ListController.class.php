<?php
/**
 * 获取激励列表
 * ListController.class.php
 * User: 何岳龙
 * Date: 2017年6月1日14:46:46
 */

namespace Apicp\Controller\Encourage;

class ListController extends AbstractController
{

    public function Index_post()
    {

        $params = I('post.');

        // 根据条件获取列表
        $data = $this->medal_serv->get_medal_list($params);

        // 组装返回数据
        $this->_result = $data;

        return true;
    }

}
