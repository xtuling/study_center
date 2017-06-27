<?php
/**
 * 添加试卷分类
 * AddController.class.php
 * User: 何岳龙
 * Date: 2017年5月25日15:52:23
 */

namespace Apicp\Controller\Category;

class AddController extends AbstractController
{

    public function Index_post()
    {

        $params = I('post.');

        // 添加分类验证
        if (!$this->cate_serv->add_cate_validation($params)) {

            return false;
        }

        // 写入分类表
        if (!$this->cate_serv->insert_cate($params)) {

            return false;
        }

        // 返回数据
        $this->_result = array();

        return true;
    }

}
