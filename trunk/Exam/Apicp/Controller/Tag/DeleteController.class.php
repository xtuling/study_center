<?php
/**
 *删除题目标签
 * DeleteController.class.php
 * User: 何岳龙
 * Date: 2017-04-24
 */

namespace Apicp\Controller\Tag;

class DeleteController extends AbstractController
{

    public function Index_post()
    {

        $params = I('post.');

        // 数据验证
        if (!$this->tag_serv->tag_info_validation($params)) {

            return false;
        }

        // 删除标签
        if (!$this->tag_serv->delete_tag($params)) {

            return false;
        }

        // 返回数据
        $this->_result = array();

        return true;
    }

}
