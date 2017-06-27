<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:42
 */
namespace Apicp\Controller\CourseClass;

use Com\PackageValidate;
use Common\Service\ClassService;
use Common\Service\RightService;

class SaveController extends \Apicp\Controller\AbstractController
{
    /**
     * Save
     * @author tangxingguo
     * @desc 保存分类
     * @param int    parent_id 上级分类ID（二级、三级分类保存时必填）
     * @param int    class_id 分类ID
     * @param string class_name:true 分类标题
     * @param string description 分类描述
     * @param int    is_open 是否启用分类（1=不启用；2=启用；二级、三级分类保存时必填）
     * @param Array right 适用范围
     * @param String right.is_all 是否全公司(任意值,有值即为全公司)
     * @param Array right.uids 人员ID
     * @param Array right.dp_ids 部门ID
     * @param Array right.tag_ids 标签ID
     * @param Array right.job_ids 职位ID
     * @param Array right.role_ids 角色ID
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'parent_id' => 'integer',
            'class_id' => 'integer',
            'class_name' => 'require|max:20',
            'description' => 'max:120',
            'is_open' => 'integer|between:1,2',
            'right' => 'array',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 分类信息初始化
        $classInfo = [
            'class_name' => $postData['class_name'],
            'description' => isset($postData['description']) ? $postData['description'] : '',
        ];

        // 唯一分类名检查
        $classServ = new ClassService();
        if (!$classServ->uniqueName($classInfo['class_name'], $postData['class_id'])) {
            E('_ERR_CLASS_NAME_REPEAT');
        }

        // 二级、三级分类信息初始化
        $rightServ = new RightService();
        if (isset($postData['parent_id'])) {
            // 是否启用参数检查
            if (!isset($postData['is_open'])) {
                E('_ERR_CLASS_IS_OPEN_EMPTY');
            }

            $classInfo['parent_id'] = $postData['parent_id'];
            $count = $classServ->count($postData['parent_id']);
            if (empty($count)) {
                E('_ERR_CLASS_PARENT_ID_NOT_FOUND');
            }
            $classInfo['is_open'] = $postData['is_open'];

            // 权限数据
            if (isset($postData['right'])) {
                if (empty($rightServ->formatPostData($postData['right']))) {
                    E('_ERR_CLASS_RIGHT_EMPTY');
                }
                $rightInfo = $postData['right'];
            }
        }

        // 保存分类
        if (!isset($postData['class_id']) || empty($postData['class_id'])) {
            // 保存
            $postData['class_id'] = $classServ->insert($classInfo);
        } else {
            // 修改
            $classServ->update($postData['class_id'], $classInfo);
        }

        // 保存权限
        if (isset($rightInfo)) {
            $rightServ->saveData(['class_id' => $postData['class_id']], $rightInfo);
        }
    }
}
