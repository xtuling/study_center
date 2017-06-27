<?php
/**
 * 获取试卷分类（添加试卷用）
 * ListForAddController.class.php
 * User: daijun
 * Date: 2017-05-23
 */

namespace Apicp\Controller\Category;

use Common\Service\CategoryService;

class ListForAddController extends AbstractController
{

    public function Index_post()
    {
        /**
         * 只查询已启用的分类列表（不分页）
         */

        // 查询启用的分类列表
        $list = $this->cate_serv->list_by_conds(array('ec_status' => CategoryService::EC_OPEN_STATES), null,
            array('order_num' => 'ASC','ec_id'=>'DESC'), 'ec_id,ec_name');

        // 循环格式化返回数据
        foreach ($list as &$v) {
            $v['ec_id'] = intval($v['ec_id']);
        }

        // 组装返回数据
        $this->_result = array('list' => $list);

        return true;

    }

}
