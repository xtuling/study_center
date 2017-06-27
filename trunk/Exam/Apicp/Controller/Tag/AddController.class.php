<?php
/**
 * 新增题目标签
 * AddController.class.php
 * User: 何岳龙
 * Date: 2017-04-24
 */

namespace Apicp\Controller\Tag;

class AddController extends AbstractController
{
    public function Index_post()
    {

        $params = I('post.');

        // 添加标签验证
        if (!$this->tag_serv->add_tag_validation($params)) {

            return false;
        }

        // 添加标签
        if (!$this->tag_serv->add_tag($params)) {

            return false;
        }

        // 返回数据
        $this->_result = array();

        return true;
    }

}
