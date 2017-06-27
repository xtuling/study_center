<?php
/**
 * 获取题目标签详情
 * DetailController.class.php
 * User: 何岳龙
 * Date: 2017-04-24
 */

namespace Apicp\Controller\Tag;

class DetailController extends AbstractController
{
    public function Index_post()
    {

        $params = I('post.');

        // 详情数据验证
        if (!$this->tag_serv->tag_info_validation($params)) {

            return false;
        }

        // 验证标签详情
        $info = $this->tag_serv->get_tag_info($params);

        // 组装返回数据
        $this->_result = $info;

        return true;
    }

}
