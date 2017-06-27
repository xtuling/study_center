<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/5/5
 * Time: 10:19
 */

namespace Api\Controller\Course;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\ClassService;

class ClassListController extends \Api\Controller\AbstractController
{
    /**
     * ClassList
     * @author tangxingguo
     * @desc 分类列表
     * @param int    class_id 新闻一级分类ID
     * @return array 新闻列表
                array(
                    'list' => array( // 分类列表
                        'order' => 1, // 排序
                        'class_id' => 8, // 二级分类ID
                        'parent_id' => 1, // 父ID
                        'class_name' => '核心单品', // 分类名称
                        'child' => array( // 三级分类
                            'order' => 3, // 排序
                            'class_id' => 9, // 三级分类ID
                            'parent_id' => 8, // 父ID
                            'class_name' => '推荐商品', // 分类名称
                        ),
                    )
                );
     *
     */

    public function Index_post()
    {
        // 验证规则
        $rules = [
            'class_id' => 'integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 条件
        $classServ = new ClassService();
        $conds = ['is_open <> ?' => Constant::CLASS_IS_OPEN_FALSE];
        // 存在一级分类ID
        if (isset($postData['class_id']) && $postData['class_id'] > 0) {
            // 取出所有二级分类
            $conds['parent_id = ?'] = $postData['class_id'];
            $child = $classServ->list_by_conds($conds);
            $parentIds = [];
            if ($child) {
                $parentIds = array_column($child, 'class_id');
            }
            // 二级分类与一级分类ID组合为父ID集合
            array_push($parentIds, $postData['class_id']);
            $conds['parent_id in (?)'] = $parentIds;
            unset($conds['parent_id = ?']);
        }

        // 排序
        $order_option = ['`order`' => 'asc'];

        // 列表
        $list = $classServ->list_by_conds($conds, [], $order_option);
        if (isset($postData['class_id'])) {
            $list = $classServ->formatClass($list, $postData['class_id']);
        } else {
            $list = $classServ->formatClass($list);
            $temp = [];
            foreach ($list as $k => $v) {
                foreach ($v['child'] as $value) {
                    $temp[] = $value;
                }
            }
            $list = $temp;
        }

        $this->_result = ['list' => $list];
    }
}
