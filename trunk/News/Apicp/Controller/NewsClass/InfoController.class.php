<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/13
 * Time: 17:02
 */
namespace Apicp\Controller\NewsClass;

use Com\PackageValidate;
use Common\Common\Constant;
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
                array (
                    'class_id' => 2 // 当前分类ID
                    'parent_id' => 1 // 这是父级分类ID（仅二级分类存在）
                    'parent_name' => 分类1 // 这是父级分类名称（仅二级分类存在）
                    'class_name' => 分类标题 // 这是分类标题
                    'description' => 分类描述 // 这是分类描述
                    'is_open' => 1 // 启用分类（1=禁用，2=启用）（仅二级分类存在）
                    'right' => array( // 权限详情
                        'is_all' => 1, // 是否全公司（1=否，2=是）
                            'tag_list' => array( // 标签信息（仅二级分类存在）
                            'tag_id' => '0E19B0B47F0000012652058BA42EEEDE', // 标签ID
                            'tag_name' => '吃货', // 标签名称
                        ),
                            'dp_list' => array( // 部门信息（仅二级分类存在）
                            'dp_id' => '0E19B0B47F0000012652058BA42EEEDE', // 部门ID
                            'dp_name' => '技术部', // 部门名称
                        ),
                        'user_list' => array( // 人员信息（仅二级分类存在）
                            'uid' => '0E19B0B47F0000012652058BA42EEEDE', // 人员ID
                            'username' => '张三', // 人员姓名
                            'face' => 'http://qy.vchangyi.com', // 人员姓名
                        ),
                        'job_list' => array( // 职位（仅二级分类存在）
                            array(
                                'job_id' => '62C316437F0000017AE8E6ACC7EFAC22', // 职位ID
                                'job_name' => '攻城狮', // 职位名称
                            ),
                        ),
                        'role_list' => array( // 角色（仅二级分类存在）
                            array(
                                'role_id' => '62C354B97F0000017AE8E6AC4FD6F429', // 角色ID
                                'role_name' => '国家元首', // 角色名称
                            ),
                        ),
                    ),
                );
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

        $classServ = new ClassService();
        $classInfo = $classServ->get($postData['class_id']);
        if (empty($classInfo)) {
            E('_ERR_CLASS_DATA_NOT_FOUND');
        }
        $info = [
            'class_id' => $classInfo['class_id'],
            'class_name' => $classInfo['class_name'],
            'description' => $classInfo['description'],
            'right' => [],
        ];

        // 二级分类
        $level = $classServ->classLevel($postData['class_id']);
        if ($level == 2) {
            $pInfo = $classServ->get($classInfo['parent_id']);
            if ($pInfo) {
                $info['parent_name'] = $pInfo['class_name'];
                $info['is_open'] = $classInfo['is_open'];
            }

            // 取权限
            $rightServ = new RightService();
            $right = $rightServ->getData(['class_id' => $postData['class_id']]);
            $info['right'] = $right;
        }

        $this->_result = $info;
    }
}
