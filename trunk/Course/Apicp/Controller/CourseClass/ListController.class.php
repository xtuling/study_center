<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/11
 * Time: 16:31
 */
namespace Apicp\Controller\CourseClass;

use Com\PackageValidate;
use Common\Service\ClassService;

class ListController extends \Apicp\Controller\AbstractController
{
    /**
     * List
     * @author tangxingguo
     * @desc 分类列表
     * @param int    is_open 获取的分类启禁用类型（1=禁用，2=启用）
     * @return array
                    array(
                        'list' => array(
                            array(
                            'class_id' => 1, // 分类ID
                            'class_name' => '一级分类', // 分类名称
                            'order' => 1, // 排序
                                'child' => array( // 二级分类
                                    array(
                                        'class_id' => 2, // 分类ID
                                        'class_name' => '二级分类', // 分类名称
                                        'is_open' => 1, // 启用分类（1=禁用，2=启用）
                                        'order' => 2, // 排序
                                        'child' => array( // 三级分类
                                            'class_id' => 3, // 分类ID
                                            'class_name' => '三级分类', // 分类名称
                                            'is_open' => 2, // 启用分类（1=禁用，2=启用）
                                            'order' => 2, // 排序
                                        ),
                                    )
                                )
                            )
                        )
                    )
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'is_open' => 'integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 条件
        $conds = [];
        if (isset($postData['is_open'])) {
            $conds = ['is_open in (?)' => [$postData['is_open'], 0]];
        }

        // 排序规则
        $order_option = ['`order`' => 'asc', 'created' => 'desc'];

        // 取数据
        $classServ = new ClassService();
        $list = $classServ->list_by_conds($conds, [], $order_option);

        // 格式化
        $classList = $classServ->formatClass($list);

        $this->_result = ['list' => $classList];
    }
}
