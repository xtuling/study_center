<?php
/**
 * 试卷分类列表
 * ListController.class.php
 * User: 何岳龙
 * Date: 2017年5月25日15:52:16
 */

namespace Apicp\Controller\Category;

class ListController extends AbstractController
{

    public function Index_post()
    {
        $list = $this->cate_serv->list_by_conds(array(), null, array('order_num' => 'ASC','ec_id'=>'DESC'),
            'ec_id,ec_name,ec_desc,order_num,ec_status,is_all');

        // 格式化数据
        $this->form_data($list);

        // 返回数据
        $this->_result = array('list' => $list);

        return true;
    }

    /**
     * 格式化数据格式 何岳龙
     * @param array &$data 数据
     */
    public function form_data(&$data = array())
    {
        // 格式化代码
        foreach ($data as $key => &$v) {

            $v['ec_id'] = intval($v['ec_id']);
            $v['order_num'] = intval($v['order_num']);
            $v['ec_status'] = intval($v['ec_status']);
            $v['is_all'] = intval($v['is_all']);
        }

        return true;
    }

}
