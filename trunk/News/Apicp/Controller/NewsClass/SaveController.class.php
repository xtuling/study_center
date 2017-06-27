<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:42
 */
namespace Apicp\Controller\NewsClass;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\ArticleService;
use Common\Service\ClassService;
use Common\Service\RightService;

class SaveController extends \Apicp\Controller\AbstractController
{
    /**
     * Save
     * @author tangxingguo
     * @desc 保存分类
     * @param Int    parent_id 上级分类ID（二级分类保存时必填）
     * @param Int    class_id 分类ID
     * @param String class_name:true 分类标题
     * @param String description 分类描述
     * @param Int    is_open 是否启用分类（1=不启用，2=启用；二级分类保存时必填）
     * @param Array right:true 权限范围
     * @param Int   right.is_all 是否全公司（1=否；2=是）
     * @param Array right.uids 人员ID
     * @param Array right.dp_ids 部门ID
     * @param Array right.tag_ids 标签ID
     * @param Array right.job_ids 职位ID
     * @param Array right.role_ids 角色ID
     * @return array
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'parent_id' => 'integer',
            'class_id' => 'integer',
            'class_name' => 'require|max:20',
            'description' => 'max:120',
            'is_open' => 'integer',
            'right' => 'array',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 分类信息初始化
        $classInfo = [];
        $classServ = new ClassService();
        $classInfo['class_name'] = $postData['class_name'];
        $classInfo['description'] = isset($postData['description']) ? $postData['description'] : '';

        // 唯一分类名检查
        if (!$classServ->uniqueName($classInfo['class_name'], $postData['class_id'])) {
            E('_ERR_CLASS_NAME_REPEAT');
        }

        // 二级分类操作
        if (isset($postData['parent_id'])) {
            if (!isset($postData['parent_id']) || empty($postData['parent_id'])) {
                E('_ERR_CLASS_PARENT_ID_EMPTY');
            }
            if (!isset($postData['is_open']) || empty($postData['is_open'])) {
                E('_ERR_CLASS_IS_OPEN_EMPTY');
            }
            $classInfo['parent_id'] = $postData['parent_id'];
            $classInfo['is_open'] = $postData['is_open'];

            // 权限数据
            $rightServ = new RightService();
            $formatRights = $rightServ->formatPostData($postData['right']);
            if (empty($formatRights)) {
                E('_ERR_CLASS_RIGHT_EMPTY');
            }

            // 分类名改变，修改article内分类名称
            if (isset($postData['class_id'])) {
                $class = $classServ->get($postData['class_id']);
                if ($class && $class['class_name'] != $postData['class_name']) {
                    $articleServ = new ArticleService();
                    $articleServ->update_by_conds(['class_id' => $postData['class_id']], ['class_name' => $postData['class_name']]);
                }
            }
        }

        if (empty($postData['class_id'])) {
            // 保存
            $postData['class_id'] = $classServ->insert($classInfo);
        } else {
            // 修改
            $classServ->update($postData['class_id'], $classInfo);
        }

        // 保存权限
        $rightServ = new RightService();
        $rightServ->saveData(['class_id' => $postData['class_id']], $postData['right']);
    }
}
