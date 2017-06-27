<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Apicp\Controller\AnswerClass;

use Com\PackageValidate;
use Common\Common\User;
use Common\Service\ClassService;

class InfoController extends \Apicp\Controller\AbstractController
{
    /**
     * Info
     * @author
     * @desc 分类详情
     * @param int class_id:true 分类ID
     * @return array 分类详情
                array(
                    'class_id' => 1, // 分类ID
                    'class_name' => '第一个分类', // 分类名称
                    'description' => '这是第一个分类', // 分类描述
                    'manager_id' => 'B4B3B9D17F00000173E870DA9A855AE7', // 负责人UID
                    'manager_name' => '张三', // 负责人姓名
                    'manager_face' => 'http://qy.vchangyi.com', // 负责人头像
                )
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

        $classServ = new ClassService();
        $classInfo = $classServ->get($classId);
        if (empty($classInfo)) {
            E('_ERR_CLASS_DATA_NOT_FOUND');
        }

        // 负责人头像
        $userServ = &User::instance();
        $classInfo['manager_face'] = $userServ->avatar($classInfo['manager_id']);

        $this->_result = $classInfo;
    }
}
