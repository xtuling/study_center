<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/11
 * Time: 19:16
 */
namespace Apicp\Controller\NewsClass;

use Com\PackageValidate;
use Common\Service\ClassService;

class UpdateOrderController extends \Apicp\Controller\AbstractController
{
    /**
     * UpdateOrder
     * @author tangxingguo
     * @desc 分类拖动排序
     * @param int list[].class_id:true 一级分类ID
     * @param int list[].child[].class_id:true 二级分类ID
     * @return array
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
            $classServ->update($v['class_id'], ['`order`' => $order]);
            $order ++;
            if (isset($v['child'])) {
                foreach ($v['child'] as $child) {
                    $classServ->update($child['class_id'], ['`order`' => $order]);
                    $order ++;
                }
            }
        }
    }
}
