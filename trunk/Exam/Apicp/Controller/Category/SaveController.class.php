<?php
/**
 * 编辑试卷分类
 * SaveController.class.php
 * User: 何岳龙
 * Date: 2017年5月25日15:52:08
 */

namespace Apicp\Controller\Category;

class SaveController extends AbstractController
{
    public function Index_post()
    {
        $params = I('post.');

        // 分类验证
        if (!$this->cate_serv->save_cate_validation($params)) {

            return false;
        }

        // 更新数据
        if (!$this->cate_serv->update_cate($params)) {

            return false;
        }

        // 返回数据
        $this->_result = array();

        return true;
    }

}
