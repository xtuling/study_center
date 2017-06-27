<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/11
 * Time: 16:31
 */
namespace Apicp\Controller\NewsClass;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\ClassService;

class ListController extends \Apicp\Controller\AbstractController
{
    /**

     * List

     * @desc 分类列表

     *  @param int    is_open 获取的分类启禁用类型（1=禁用，2=启用）

     * @return array

     *             array(

     *                 'list' => array(

     *                      array(

     *                          'class_id' => 1, // 分类ID

     *                          'class_name' => '分类1', // 分类名称

     *                          'is_open' => 1, // 启用分类（1=禁用，2=启用）

     *                          'order' => 1, // 排序

     *                          'child' => array( // 子分类

     *                              array(

     *                                  'class_id' => 2, // 分类ID

     *                                  'class_name' => '分类2', // 分类名称

     *                                  'is_open' => 1, // 启用分类（1=禁用，2=启用）

     *                                  'order' => 1, // 排序

     *                              )

     *                          )

     *                      )

     *                  )

     *              )

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

        $classServ = new ClassService();

        // 排序规则
        $order_option = ['`order`' => 'asc'];

        // 取一级分类
        $classList = $classServ->list_by_conds(['parent_id' => 0], [], $order_option);
        if (empty($classList)) {
            return $this->_result = ['list' => []];
        }

        // 取二级分类
        if (isset($postData['is_open']) && in_array($postData['is_open'], [Constant::CLASS_IS_OPEN_FALSE, Constant::CLASS_IS_OPEN_TRUE])) {
            $conds = [
                'parent_id > ?' => 0,
                'is_open' => $postData['is_open'],
            ];
            $childList = $classServ->list_by_conds($conds, [], $order_option);
        } else {
            $childList = $classServ->list_by_conds(['parent_id > ?' => 0], [], $order_option);
        }


        // 组合数据
        foreach ($classList as $k => $v) {
            if ($childList) {
                foreach ($childList as $index => $info) {
                    if ($v['class_id'] == $info['parent_id']) {
                        $classList[$k]['child'][] = $info;
                        unset($childList[$index]);
                    }
                }
            }
        }
        $this->_result = ['list' => $classList];
    }
}
