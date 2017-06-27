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

class FolderInfoController extends \Apicp\Controller\AbstractController
{
    /**
     * FolderInfo
     * @author tangxingguo
     * @desc 文件夹详情接口
     * @param Int file_id:true 文件夹ID
     * @return array
                array(
                    'parent_id' => 2, // 文件夹父级ID
                    'file_id' => 3, // 文件夹ID
                    'file_name' => '第一个文件夹', // 文件夹名称
                    'is_download' => 2, //是否启用下载权限（1=不启用；2=启用）
                    'read_right' => array( // 阅读权限
                        'is_all' => 1, // 是否全公司（1=否，2=是）
                        'user_list' => array( // 人员信息
                            'uid' => '0E19B0B47F0000012652058BA42EEEDE', // 人员ID
                            'username' => '张三', // 人员姓名
                            'face' => 'http://qy.vchangyi.com', // 人员头像
                        ),
                        'tag_list' => array( // 标签信息
                            'tag_id' => '0E19B0B47F0000012652058BA42EEEDE', // 标签ID
                            'tag_name' => '吃货', // 标签名称
                        ),
                        'dp_list' => array( // 部门信息
                            'dp_id' => '0E19B0B47F0000012652058BA42EEEDE', // 部门ID
                            'dp_name' => '技术部', // 部门名称
                        ),
                        'job_list' => array(// 职位
                            array(
                                'job_id' => '62C316437F0000017AE8E6ACC7EFAC22',// 职位ID
                                'job_name' => '攻城狮',// 职位名称
                            ),
                        ),
                        'role_list' => array(// 角色
                            array(
                                'role_id' => '62C354B97F0000017AE8E6AC4FD6F429',// 角色ID
                                'role_name' => '国家元首',// 角色名称
                            ),
                        ),
                    ),
                    'download_right' => array( // 下载权限
                        'is_all' => 1, // 是否全公司（1=否，2=是）
                        'user_list' => array( // 人员信息
                            'uid' => '0E19B0B47F0000012652058BA42EEEDE', // 人员ID
                            'username' => '张三', // 人员姓名
                            'face' => 'http://qy.vchangyi.com', // 人员头像
                        ),
                        'tag_list' => array( // 标签信息
                            'tag_id' => '0E19B0B47F0000012652058BA42EEEDE', // 标签ID
                            'tag_name' => '吃货', // 标签名称
                        ),
                        'dp_list' => array( // 部门信息
                            'dp_id' => '0E19B0B47F0000012652058BA42EEEDE', // 部门ID
                            'dp_name' => '技术部', // 部门名称
                        ),
                        'job_list' => array(// 职位
                            array(
                                'job_id' => '62C316437F0000017AE8E6ACC7EFAC22',// 职位ID
                                'job_name' => '攻城狮',// 职位名称
                            ),
                        ),
                        'role_list' => array(// 角色
                            array(
                                'role_id' => '62C354B97F0000017AE8E6AC4FD6F429',// 角色ID
                                'role_name' => '国家元首',// 角色名称
                            ),
                        ),
                    )
                );
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'file_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;
        $fileId = $postData['file_id'];

        // 文件夹信息
        $fileServ = new FileService();
        $fileInfo = $fileServ->get($fileId);
        if (empty($fileInfo)) {
            E('_ERR_FILE_FOLDER_IS_NULL');
        }

        // 阅读权限
        $rightServ = new RightService();
        $readRight = $rightServ->getData(['file_id' => $fileId, 'right_type' => Constant::RIGHT_TYPE_IS_READ]);
        if (empty($readRight)) {
            E('_ERR_FILE_READ_RIGHT_IS_NULL');
        }
        $fileInfo['read_right'] = $readRight;

        // 下载权限
        if ($fileInfo['is_download'] == Constant::FILE_DOWNLOAD_RIGHT_ON) {
            $downloadRight = $rightServ->getData(['file_id' => $fileId, 'right_type' => Constant::RIGHT_TYPE_IS_DOWNLOAD]);
        }
        $fileInfo['download_right'] = isset($downloadRight) ? $downloadRight : [];

        $this->_result = $fileInfo;
    }
}
