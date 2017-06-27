<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/21
 * Time: 10:19
 */
namespace Api\Controller\Doc;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\FileService;
use Common\Service\RightService;
use VcySDK\Service;
use VcySDK\Message;

class DownloadController extends \Api\Controller\AbstractController
{
    /**
     * Download
     * @author liyifei
     * @desc 文件下载接口
     * @param Int file_id:true 文件ID
     * @return mixed
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

        // 当前登录用户
        $user = $this->_login->user;

        // 文件详情
        $fileServ = new FileService();
        $file = $fileServ->get($postData['file_id']);
        if (empty($file)) {
            E('_ERR_FILE_DATA_IS_NULL');
        }

        // 检查文件大小
        $suffix = '';
        $fileNames = explode('.', $file['file_name']);
        if (count($fileNames) > 1) {
            $suffix = strtoupper(end($fileNames));
        }
        if (in_array($suffix, Constant::ALLOW_UPLOAD_FILE_TYPE) && $file['at_size'] > Constant::DOWNLOAD_FILE_MAX_SIZE) {
            E('_ERR_ATTACH_FILE_SIZE_OVERRUN');
        }
        if (in_array($suffix, Constant::ALLOW_UPLOAD_IMAGE_TYPE) && $file['at_size'] > Constant::DOWNLOAD_IMAGE_MAX_SIZE) {
            E('_ERR_ATTACH_IMAGE_SIZE_OVERRUN');
        }

        // 顶级目录文件默认可下载
        if ($file['parent_id'] != 0) {
            // 文件是否开启下载
            $folder = $fileServ->get_by_conds([
                'file_id' => $file['parent_id'],
                'is_show' => Constant::FILE_STATUS_IS_SHOW,
            ]);
            if (empty($folder)) {
                E('_ERR_FILE_DATA_IS_NULL');
            }
            if ($folder['is_download'] != Constant::FILE_DOWNLOAD_RIGHT_ON) {
                E('_ERR_FILE_DOWNLOAD_RIGHT');
            }

            // 校验当前用户是否有下载权限
            $rightServ = new RightService();
            $myRight = $rightServ->checkDownloadRight($user, $file['parent_id']);
            if (!$myRight) {
                E('_ERR_FILE_DOWNLOAD_RIGHT');
            }
        }

        // 推送文件到应用主页面
        $service = &Service::instance();
        $messageServ = new Message($service);
        $messageServ->sendFile([
            "toUser" => $user['memUid'],
            "msgtype" => "file",
            "atId" => $file['at_id'],
        ]);
    }
}
