<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Apicp\Controller\AnswerClass;

use Com\PackageValidate;
use Common\Service\AnswerService;
use Common\Service\ClassService;

class DeleteController extends \Apicp\Controller\AbstractController
{
    /**
     * Delete
     * @author
     * @desc 删除分类
     * @param int class_id:true 分类ID
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'class_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $classId = $validate->postData['class_id'];

        // 分类内存在问答
        $answerServ = new AnswerService();
        $count = $answerServ->count_by_conds(['class_id' => $classId]);
        if ($count > 0) {
            E('_ERR_CLASS_EXIST_QUESTION');
        }

        // 删除
        $classServ = new ClassService();
        $classServ->delete($classId);
    }
}
