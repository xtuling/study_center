<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Apicp\Controller\AnswerClass;

use Common\Service\ClassService;

class ListController extends \Apicp\Controller\AbstractController
{
    /**
     * List
     * @author
     * @desc 分类列表
     * @return array 分类列表
                array(
                    'class_id' => 1, // 分类ID
                    'class_name' => '第一个分类', // 分类名称
                    'description' => '这是第一个分类', // 分类描述
                    'manager_name' => '张三', // 负责人姓名
                    'created' => 1494495535924, // 创建时间
                )
     */
    public function Index_post()
    {
        $classServ = new ClassService();
        $order_option = ['`order`' => 'asc', 'created' => 'desc'];
        $list = $classServ->list_by_conds([], [], $order_option);

        $this->_result = $list;
    }
}
