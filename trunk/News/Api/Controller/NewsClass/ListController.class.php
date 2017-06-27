<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:33
 */
namespace Api\Controller\NewsClass;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\ClassService;

class ListController extends \Api\Controller\AbstractController
{
    /**
     * List
     * @author tangxingguo
     * @desc 分类列表
     * @param int    class_id 新闻一级分类ID
     * @return array 新闻列表
     *              array(
                        'list' => array(
                            'class_id' => '1', // 一级分类ID
                            'child_list' => array(
                                'order' => 1, // 排序
                                'class_id' => 8, // 二级分类ID
                                'parent_id' => 3, // 一级分类ID
                                'class_name' => '核心单品', // 分类名称
                            )
                        )
                    );
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'class_id' => 'integer',
        ];

        // 默认分类
        $defaultClass = [
            'class_id' => '',
            'class_name' => '最新',
            'description' => '',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 条件
        $conds = ['parent_id > ?' => 0, 'is_open = ?' => Constant::CLASS_IS_OPEN_TRUE];
        if (isset($postData['class_id']) && $postData['class_id'] > 0) {
            $conds['parent_id = ?'] = $postData['class_id'];
        }

        // 排序
        $order_option = ['`order`' => 'asc'];

        // 列表
        $classServ = new ClassService();
        $classList = $classServ->list_by_conds($conds, [], $order_option);
        array_unshift($classList, $defaultClass);
        $this->_result = ['list' => $classList];
    }
}
