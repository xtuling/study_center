<?php
/**
 * 【考试中心-手机端】获取分类列表接
 *  ListController.class.php
 * @author: 蔡建华
 * @date :  2017-05-23
 */

namespace Api\Controller\Category;

use Common\Service\CategoryService;

class ListController extends AbstractController
{
    /**
     * 分类yService对象
     * @var CategoryService
     */
    protected $category_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化分类Service
        $this->category_serv = new CategoryService();

        return true;
    }

    /**
     * 获取分类列表
     * @limit Int    默认全部
     * @return bool
     */
    public function Index_post()
    {
        // 获取分类无需调用已经禁用的，无需判断权限，需要进行排序

        $data = $this->category_serv->list_by_conds(array('ec_status' => CategoryService::EC_OPEN_STATES), null,
            array('order_num' => 'asc'), 'ec_id,ec_name');
        $data = $this->category_serv->format_category_all($data);
        $this->_result['list'] = $data;

        return true;
    }


}
