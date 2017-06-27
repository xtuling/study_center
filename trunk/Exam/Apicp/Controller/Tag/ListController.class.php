<?php
/**
 * 获取题目标签列表
 * ListController.class.php
 * User: 何岳龙
 * Date: 2017-04-24
 */

namespace Apicp\Controller\Tag;

class ListController extends AbstractController
{

    public function Index_post()
    {

        $params = I('post.');

        // 根据条件获取列表
        $data = $this->tag_serv->get_tag_list($params);

        // 组装返回数据
        $this->_result = $data;

        return true;
    }

}
