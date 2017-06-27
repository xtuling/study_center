<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/13
 * Time: 17:02
 */
namespace Apicp\Controller\CourseClass;

use Com\PackageValidate;
use Common\Service\ClassService;
use Common\Service\RightService;

class InfoController extends \Apicp\Controller\AbstractController
{
    /**
     * Info
     * @author tangxingguo
     * @desc 分类详情
     * @param int    class_id:true 分类ID
     * @return array 分类信息
                array(
                    'class_id' => 2, // 分类ID
                    'class_name' => '分类名称', // 分类名称
                    'parent_id' => 1, // 父类ID
                    'parent_name' => '一级分类', // 父类名称
                    'description' => '分类描述', // 分类描述
                    'is_open' => 1, // 启用分类（1=禁用，2=启用）
                    'right' => array( // 权限
                        'is_all' => 1, // 是否全公司（1=否，2=是）
                        'user_list' => array( // 人员信息
                            array(
                                'uid' => '0E19B0B47F0000012652058BA42EEEDE', // 人员ID
                                'username' => '张三', // 人员姓名
                                'face' => 'http://qy.vchangyi.com', // 人员头像
                            ),
                        ),
                        'tag_list' => array( // 标签信息
                            array(
                                'tag_id' => '0E19B0B47F0000012652058BA42EEEDE', // 标签ID
                                'tag_name' => '吃货', // 标签名称
                            ),
                        ),
                        'dp_list' => array( // 部门信息
                            array(
                                'dp_id' => '0E19B0B47F0000012652058BA42EEEDE', // 部门ID
                                'dp_name' => '技术部', // 部门名称
                            ),
                        ),
                        'job_list' => array( // 职位信息
                            array(
                                'job_id' => '0E19B0B47F0000012652058BA42EEEDE', // 职位ID
                                'job_name' => '攻城狮', // 职位名称
                            ),
                        ),
                        'role_list' => array( // 角色信息
                            array(
                                'role_id' => '0E19B0B47F0000012652058BA42EEEDE', // 角色ID
                                'role_name' => '国王', // 角色名称
                            ),
                        ),
                    ),
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
        $postData = $validate->postData;

        // 取分类信息
        $classServ = new ClassService();
        $classInfo = $classServ->get($postData['class_id']);
        if (empty($classInfo)) {
            E('_ERR_CLASS_DATA_NOT_FOUND');
        }
        $info = [
            'class_id' => $classInfo['class_id'],
            'class_name' => $classInfo['class_name'],
            'description' => $classInfo['description'],
        ];

        // 二级三级分类取父类、权限
        $classServ->getLevel($postData['class_id'], $level);
        if (in_array($level, [2, 3])) {
            $parentInfo = $classServ->get($classInfo['parent_id']);
            if ($parentInfo) {
                $info['parent_name'] = $parentInfo['class_name'];
                $info['parent_id'] = $parentInfo['class_id'];
                $info['is_open'] = $classInfo['is_open'];
            }

            // 取权限(三级继承二级权限)
            $classId = $postData['class_id'];
            if ($level == 3) {
                $classId = $info['parent_id'];
            }
            $rightServ = new RightService();
            $info['right'] = $rightServ->getData(['class_id' => $classId]);
        }
        $this->_result = $info;
    }
}
