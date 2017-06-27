<?php
/**
 * 获取试卷分类详情
 * DetailController.class.php
 * User: 何岳龙
 * Date: 2017-04-24
 */

namespace Apicp\Controller\Category;

class DetailController extends AbstractController
{

    public function Index_post()
    {
        $params = I('post.');

        // 详情验证
        if (!$this->cate_serv->info_cate_validation($params)) {

            return false;
        }

        // 获取详情
        $info = $this->cate_serv->get_cate_info($params);

        // 返回数据
        $this->_result = $info;

        return true;
    }

}
