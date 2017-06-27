<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Apicp\Controller\AnswerClass;

use Com\PackageValidate;
use Common\Service\ClassService;

class OrderController extends \Apicp\Controller\AbstractController
{
    /**
     * Order
     * @author
     * @desc 分类排序
     * @param array class_ids:true 分类ID
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'class_ids' => 'require|array',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $classIds = $validate->postData['class_ids'];

        $classServ = new ClassService();
        $order = 1;
        foreach ($classIds as $classId) {
            $classServ->update($classId, ['`order`' => $order]);
            $order++;
        }
    }
}
