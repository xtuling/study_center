<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017-5-18
 * Time: 14:07:02
 */
namespace Apicp\Controller\Doc;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\FileService;
use Common\Service\RightService;

class MoveController extends \Apicp\Controller\AbstractController
{
    /**
     * Move
     * @author tangxingguo
     * @desc 文件移动接口
     * @param Array file_ids:true 待移动文件ID集合
     * @param Int file_id:true 目标文件夹ID
     * @return array
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'file_ids' => 'require|array',
            'file_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 待移动文件信息
        $fileServ = new FileService();
        $files = $fileServ->list_by_conds(['file_id in (?)' => $postData['file_ids']]);
        if (empty($files)) {
            E('_ERR_FILE_SELECT_IS_NULL');
        }

        // 目标文件夹信息
        $rightServ = new RightService();
        if ($postData['file_id'] != 0) {
            $targetInfo = $fileServ->get($postData['file_id']);
            if (empty($targetInfo)) {
                E('_ERR_FILE_TARGET_FOLDER_IS_NULL');
            }

            // 取目标文件夹阅读、下载权限
            $readRight = $rightServ->list_by_conds([
                'file_id' => $postData['file_id'],
                'right_type' => Constant::RIGHT_TYPE_IS_READ
            ]);
            $readRight = $this->_formatRight($readRight);

            if ($targetInfo['is_download'] == Constant::FILE_DOWNLOAD_RIGHT_ON) {
                // 已启用下载权限
                $downloadRight = $rightServ->list_by_conds([
                    'file_id' => $postData['file_id'],
                    'right_type' => Constant::RIGHT_TYPE_IS_DOWNLOAD
                ]);
                $downloadRight = $this->_formatRight($downloadRight);
            }
        } else {
            $readRight = ['is_all' => Constant::RIGHT_IS_ALL_TRUE];
        }

        // 取待移动文件夹、以及其子类文件夹（文件不能移动到自身或其子目录）
        $childIds = $fileServ->getChildIds($postData['file_ids']);
        if (in_array($postData['file_id'], $childIds)) {
            E('_ERR_FILE_TARGET_FOLDER_FAIL');
        }

        // 检查层级是否超过十级
        $leveNormal = $this->_checkLevel($postData['file_id'], $postData['file_ids'], $childIds);
        if ($leveNormal == false) {
            E('_ERR_FILE_LEVEL_OUT_RANGE');
        }

        // 文件移动
        foreach ($files as $k => $v) {
            // 检查文件名
            $fileServ->checkFileName($v['file_name'], $postData['file_id'], $v['file_type']);
            // 更新父级
            $fileServ->update($v['file_id'], ['parent_id' => $postData['file_id'], 'file_name' => $v['file_name']]);
        }
        // 移动的文件中存在文件夹
        if (!empty($childIds)) {
            // 继承阅读权限（子文件夹同时继承）
            foreach ($childIds as $id) {
                $readConds = [
                    'file_id' => $id,
                    'right_type' => Constant::RIGHT_TYPE_IS_READ
                ];
                $rightServ->saveData($readConds, $readRight);
            }


            // 继承下载权限
            if (isset($downloadRight)) {
                foreach ($childIds as $id) {
                    $downloadConds = [
                        'file_id' => $id,
                        'right_type' => Constant::RIGHT_TYPE_IS_DOWNLOAD
                    ];
                    $rightServ->saveData($downloadConds, $downloadRight);
                }
                $data = ['is_download' => Constant::FILE_DOWNLOAD_RIGHT_ON];
            } else {
                $data = ['is_download' => Constant::FILE_DOWNLOAD_RIGHT_OFF];
            }
            // 修改是否启用下载权限字段
            foreach ($childIds as $id) {
                $fileServ->update($id, $data);
            }
        }
    }

    /**
     * @desc 将取出的权限数据字段，统一为保存方法能识别的字段
     * @author tangxingguo
     * @param array $right 权限数据
     * @return array 修改字段后的权限数据
     */
    private function _formatRight($right)
    {
        $rightServ = new RightService();
        $right = $rightServ->formatDBData($right);
        $right = [
            'is_all' => isset($right[Constant::RIGHT_TYPE_ALL]) ? $right[Constant::RIGHT_TYPE_ALL] : Constant::RIGHT_IS_ALL_FALSE,
            'dp_ids' => isset($right[Constant::RIGHT_TYPE_DEPARTMENT]) ? $right[Constant::RIGHT_TYPE_DEPARTMENT] : [],
            'tag_ids' => isset($right[Constant::RIGHT_TYPE_TAG]) ? $right[Constant::RIGHT_TYPE_TAG] : [],
            'uids' => isset($right[Constant::RIGHT_TYPE_USER]) ? $right[Constant::RIGHT_TYPE_USER] : [],
            'job_ids' => isset($right[Constant::RIGHT_TYPE_JOB]) ? $right[Constant::RIGHT_TYPE_JOB] : [],
            'role_ids' => isset($right[Constant::RIGHT_TYPE_ROLE]) ? $right[Constant::RIGHT_TYPE_ROLE] : [],
        ];
        return $right;
    }

    /**
     * @desc 检查移动后文件目录是否超过十级
     * @author tangxingguo
     * @param int $targetId 目标文件夹
     * @param array $fileIds 被移动的文件
     * @param array $childIds 所有文件夹以及子文件夹
     * @return bool
     */
    private function _checkLevel($targetId, $fileIds, $childIds)
    {
        $levelNormal = true;
        $fileServ = new FileService();
        $files = $fileServ->list_all();
        $files = array_combine_by_key($files, 'file_id');

        // 将待移动的文件父级目录ID都替换为目标文件夹ID
        foreach ($files as $k => $v) {
            if (in_array($k, $fileIds)) {
                $files[$k]['parent_id'] = $targetId;
            }
        }

        // 遍历所有子文件夹新的目录层级，大于十级返回层级异常
        foreach ($childIds as $file_id) {
            $level = 1;
            while (isset($files[$file_id]) && $files[$file_id]['parent_id'] > 0) {
                $level++;
                $file_id = $files[$file_id]['parent_id'];
            }
            if ($level > 10) {
                $levelNormal = false;
                break;
            }
        }

        return $levelNormal;
    }
}
