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

class SaveFolderController extends \Apicp\Controller\AbstractController
{
    /**
     * SaveFolder
     * @author
     * @desc 保存文件夹接口
     * @param Int parent_id:true 父级文件夹ID
     * @param Int file_id 文件夹ID
     * @param String file_name:true 文件夹名称（最大长度30字符）
     * @param Array read_right:true 查阅范围
     * @param Int read_right.is_all 是否全公司（1=否；2=是）
     * @param Array read_right.uids 人员ID
     * @param Array read_right.dp_ids 部门ID
     * @param Array read_right.tag_ids 标签ID
     * @param Array read_right.job_ids 职位ID
     * @param Array read_right.role_ids 角色ID
     * @param Int is_download:true 是否启用下载权限（1=不启用；2=启用）
     * @param Array download_right 下载范围（启用下载权限时必要）
     * @param Int download_right.is_all 是否全公司（1=否；2=是）
     * @param Array download_right.uids 人员ID
     * @param Array download_right.dp_ids 部门ID
     * @param Array download_right.tag_ids 标签ID
     * @param Array download_right.job_ids 职位ID
     * @param Array download_right.role_ids 角色ID
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'parent_id' => 'require|integer',
            'file_id' => 'integer',
            'is_download' => 'require|integer',
            'file_name' => 'require|max:30',
            'read_right' => 'array',
            'download_right' => 'array',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 父级目录检查
        $fileServ = new FileService();
        if ($postData['parent_id'] > 0) {
            $parentInfo = $fileServ->get($postData['parent_id']);
            if (empty($parentInfo)) {
                E('_ERR_FILE_PARENT_IS_NULL');
            }
            // 目录层级最多十级
            $parentLevel = $fileServ->getLevel($postData['parent_id']);
            if ($parentLevel > 10) {
                E('_ERR_FILE_FOLDER_OVER_RANGE');
            }
        }

        // 文件夹数据初始化
        $folderInfo = [
            'parent_id' => $postData['parent_id'],
            'file_name' => $postData['file_name'],
            'is_download' => $postData['is_download'],
            'file_type' => Constant::FILE_TYPE_IS_FOLDER,
            'update_time' => MILLI_TIME,
            'file_status' => Constant::FILE_STATUS_NORMAL,
        ];

        // 文件夹名称检查
        $file_id = isset($postData['file_id']) ? $postData['file_id'] : 0;
        $fileServ->checkFileName($folderInfo['file_name'], $folderInfo['parent_id'], $folderInfo['file_type'], $file_id);

        // 查阅权限
        $rightServ = new RightService();
        $read_right = $postData['read_right'];
        $format_right = $rightServ->formatPostData($read_right);
        if (empty($format_right)) {
            E('_ERR_FILE_READ_RIGHT_EMPTY');
        }

        // 下载权限
        if ($postData['is_download'] == Constant::FILE_DOWNLOAD_RIGHT_ON) {
            $download_right = $postData['download_right'];
            $format_right = $rightServ->formatPostData($download_right);
            if (empty($format_right)) {
                E('_ERR_FILE_DOWNLOAD_RIGHT_EMPTY');
            }

            // 下载权限不能超过查阅权限
            $compareRight = $this->_checkDownloadRight($read_right, $download_right);
            if ($compareRight) {
                E('_ERR_FILE_DOWNLOAD_RIGHT_FALL');
            }
        }

        // 当前文件夹权限不能超过父级文件夹权限
        $parentRight = $this->_checkParentRight($postData['parent_id'], $read_right);
        if ($parentRight) {
            E('_ERR_FILE_RIGHT_RANGE_FALL');
        }

        // 保存文件夹
        if (!isset($postData['file_id']) || empty($postData['file_id'])) {
            // 保存
            $postData['file_id'] = $fileServ->insert($folderInfo);
        } else {
            // 修改
            $fileServ->update($postData['file_id'], $folderInfo);
        }

        // 保存权限
        $rightServ = new RightService();
        $readConds = [
            'file_id' => $postData['file_id'],
            'right_type' => Constant::RIGHT_TYPE_IS_READ,
        ];
        $rightServ->saveData($readConds, $read_right);
        if (isset($download_right)) {
            $downloadConds = [
                'file_id' => $postData['file_id'],
                'right_type' => Constant::RIGHT_TYPE_IS_DOWNLOAD,
            ];
            $rightServ->saveData($downloadConds, $download_right);
        }
    }

    /**
     * @desc 检查下载权限，下载权限超过查阅权限返回true
     * @author tangxingguo
     * @param array $read_right 用户输入的阅读权限数据
     * @param array $download_right 用户输入的现在权限数据
     * @return bool
     */
    private function _checkDownloadRight($read_right, $download_right)
    {
        // 下载权限为全公司，查阅权限不为全公司
        $download_right['is_all'] = isset($download_right['is_all']) ? $download_right['is_all'] : Constant::RIGHT_IS_ALL_FALSE;
        $read_right['is_all'] = isset($read_right['is_all']) ? $read_right['is_all'] : Constant::RIGHT_IS_ALL_FALSE;
        if ($download_right['is_all'] > $read_right['is_all']) {
            return true;
        }

        // 格式化权限数据
        $rightServ = new RightService();
        $read_right = $rightServ->formatPostData($read_right);
        $download_right = $rightServ->formatPostData($download_right);

        return $this->_diffRight($read_right, $download_right);
    }

    /**
     * @desc 检查父级目录权限，查阅权限超过父级目录权限返回true
     * @author tangxingguo
     * @param int $parent_id 父级目录ID
     * @param array $read_right 查阅权限
     * @return bool
     */
    private function _checkParentRight($parent_id, $read_right)
    {
        // 取父级权限数据
        $rightServ = new RightService();
        if ($parent_id == 0) {
            $parentRight = ['is_all' => Constant::RIGHT_IS_ALL_TRUE];
            $parentRight = $rightServ->formatPostData($parentRight);
        } else {
            $parentRight = $rightServ->list_by_conds(['file_id' => $parent_id, 'right_type' => Constant::RIGHT_TYPE_IS_READ]);
            $parentRight = $rightServ->formatDBData($parentRight);
        }

        // 格式化阅读权限数据
        $read_right = $rightServ->formatPostData($read_right);

        return $this->_diffRight($parentRight, $read_right);
    }

    /**
     * @desc 对比两个权限，权限异常返回true
     * @author tangxingguo
     * @param array $right_max 较大范围的权限
     * @param array $right_min 较小范围的权限
     * @return bool
     */
    private function _diffRight($right_max, $right_min)
    {
        // 获取权限数据内的所有用户
        $rightServ = new RightService();
        $max_uids = $rightServ->getUidsByRight($right_max);
        $min_uids = $rightServ->getUidsByRight($right_min);

        // 比较权限
        $overflow = array_diff($min_uids, $max_uids);
        if (empty($overflow)) {
            return false;
        } else {
            return true;
        }
    }
}
