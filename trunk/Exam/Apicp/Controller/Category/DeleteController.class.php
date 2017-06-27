<?php
/**
 * 删除试卷分类
 * DeleteController.class.php
 * User: 何岳龙
 * Date: 2017年5月25日15:52:43
 */

namespace Apicp\Controller\Category;

class DeleteController extends AbstractController
{

    public function Index_post()
    {

        $params = I('post.');

        // 验证删除试卷分类
        if (!$this->cate_serv->delete_cate_validation($params)) {

            return false;
        }

        // 返回数据
        $this->_result = array();

        return true;
    }

}
