<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/11
 * Time: 19:16
 */
namespace Apicp\Controller\CourseClass;

use Com\PackageValidate;
use Common\Service\ClassService;

class UpdateOrderController extends \Apicp\Controller\AbstractController
{
    /**
     * UpdateOrder
     * @author tangxingguo
     * @desc 分类排序接口
     * @param int list[].class_id:true 一级分类ID
     * @param int list[].child[].class_id:true 二级分类ID
     * @param int list[].child[].child[].class_id:true 三级分类ID
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'list' => 'require|array',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 入库
        $classServ = new ClassService();
        $order = 0;
        foreach ($postData['list'] as $k => $v) {
            // 一级分类操作
            $classServ->update($v['class_id'], ['`order`' => $order]);
            $order ++;
            if (isset($v['child']) && !empty($v['child'])) {
                foreach ($v['child'] as $child) {
                    // 二级分类操作
                    $classServ->update($child['class_id'], ['`order`' => $order]);
                    $order ++;
                    if (isset($child['child']) && !empty($child['child'])) {
                        foreach ($child['child'] as $subChild) {
                            // 三级分类操作
                            $classServ->update($subChild['class_id'], ['`order`' => $order]);
                            $order ++;
                        }
                    }
                }
            }
        }
    }
}
