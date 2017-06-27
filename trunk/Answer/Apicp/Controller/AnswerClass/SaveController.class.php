<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/6/21
 * Time: 16:28
 */
namespace Apicp\Controller\AnswerClass;

use Com\PackageValidate;
use Common\Common\Config;
use Common\Service\ClassService;
use Common\Service\ConfigService;

class SaveController extends \Apicp\Controller\AbstractController
{
    /**
     * Save
     * @author
     * @desc 保存、编辑分类
     * @param int    class_id 分类ID
     * @param string class_name:true 分类标题（max:20）
     * @param string description 分类描述（max:60）
     * @param string manager_id:true 负责人UID
     * @param string manager_name:true 负责人姓名
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'class_id' => 'integer',
            'class_name' => 'require|max:20',
            'description' => 'max:60',
            'manager_id' => 'require',
            'manager_name' => 'require',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 分类信息初始化
        $classInfo = [
            'class_name' => $postData['class_name'],
            'description' => isset($postData['description']) ? $postData['description'] : '',
            'manager_id' => $postData['manager_id'],
            'manager_name' => $postData['manager_name'],
        ];

        // 唯一分类名检查
        $classServ = new ClassService();
        if (!$classServ->uniqueName($classInfo['class_name'], $postData['class_id'])) {
            E('_ERR_CLASS_NAME_REPEAT');
        }

        // 负责人应用权限检查
        $this->_checkManagerRight($postData['manager_id']);

        // 保存分类
        if (!isset($postData['class_id']) || $postData['class_id'] == 0) {
            // 保存
            $classServ->insert($classInfo);
        } else {
            // 修改
            $count = $classServ->count($postData['class_id']);
            if ($count < 1) {
                E('_ERR_CLASS_DATA_NOT_FOUND');
            }
            $classServ->update($postData['class_id'], $classInfo);
        }
    }

    /**
     * @desc 检查负责人是否有应用使用权限
     * @author tangxingguo
     * @param string $uid 负责人UID
     */
    private function _checkManagerRight($uid)
    {
        // 权限数据获取
        $data =Config::instance()->getCacheData();
        // 根据权限数据获取权限数据内所有UID
        $configServ = new ConfigService();
        $uids = $configServ->getUidsByRight($data['rights']);
        if (!in_array($uid, $uids)) {
            E('_ERR_CLASS_MANAGER_NOT_RIGHT');
        }
    }
}
