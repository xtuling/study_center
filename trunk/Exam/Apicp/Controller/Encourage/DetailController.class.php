<?php
/**
 * 获取激励详情
 * DetailController.class.php
 * User: 何岳龙
 * Date: 2017年6月1日14:46:41
 */

namespace Apicp\Controller\Encourage;

class DetailController extends AbstractController
{
    public function Index_post()
    {

        $params = I('post.');

        // 详情数据验证
        if (!$this->medal_serv->medal_info_validation($params)) {

            return false;
        }

        // 获取详情
        $info = $this->medal_serv->get_medal_info($params);

        // 组装返回数据
        $this->_result = $info;

        return true;
    }

}
