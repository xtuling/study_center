<?php
/**
 * 分类批量排序
 * OrderController.class.php
 * User: 何岳龙
 * Date: 2017年5月25日15:52:00
 */

namespace Apicp\Controller\Category;

class OrderController extends AbstractController
{

    public function Index_post()
    {

        $params = I('post.');

        // 批量排序分类验证
        if (!$this->cate_serv->order_cate_validation($params)) {

            return false;
        }

        // 分类批量排序
        $this->cate_serv->order_cate($params);

        // 返回数据
        $this->_result = array();

        return true;
    }

}
