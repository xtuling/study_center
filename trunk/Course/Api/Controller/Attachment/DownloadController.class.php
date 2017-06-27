<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/19
 * Time: 15:24
 */
namespace Api\Controller\Attachment;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Common\Attach;
use Common\Service\SourceService;
use Common\Service\SourceAttachService;
use VcySDK\Service;
use VcySDK\Message;

class DownloadController extends \Api\Controller\AbstractController
{
    /**
     * Download
     * @author liyifei
     * @desc 下载附件
     * @param Int source_id:true 素材ID
     * @param Int at_id:true 附件ID
     * @return mixed
     */
    public function Index()
    {
        // 验证规则
        $rules = [
            'source_id' => 'require|integer',
            'at_id' => 'require|max:32',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 素材详情(附件是否支持下载)
        $sourceServ = new SourceService();
        $source = $sourceServ->get_by_conds([
            'source_id' => $postData['source_id'],
            'is_download' => Constant::ATTACH_IS_DOWNLOAD_TRUE,
        ]);
        if (empty($source)) {
            E('_ERR_SOURCE_NOT_FOUND');
        }

        // 该附件在UC是否存在
        $attachServ = &Attach::instance();
        $attach = $attachServ->getAttachDetail($postData['at_id']);
        if (!$attach) {
            E('_ERR_DOWNLOAD_FILE_DELETED');
        }

        // 附件详情(附件是否存在)
        $saServ = new SourceAttachService();
        $sa = $saServ->get_by_conds([
            'source_id' => $postData['source_id'],
            'at_id' => $postData['at_id'],
        ]);
        if (empty($sa)) {
            E('_ERR_ATTACH_NOT_FOUND');
        }
        if ($sa['at_type'] != Constant::ATTACH_TYPE_FILE) {
            E('_ERR_DOWNLOAD_FILE_ONLY');
        }

        // 检查文件大小
        if ($sa['at_size'] > Constant::DOWNLOAD_FILE_MAX_SIZE) {
            E('_ERR_ATTACH_FILE_SIZE_OVERRUN');
        }

        // 当前登录用户
        $uid = $this->_login->user["memUid"];

        // 推送文件到应用主页面
        $service = &Service::instance();
        $messageServ = new Message($service);
        $messageServ->sendFile([
            "toUser" => $uid,
            "msgtype" => "file",
            "atId" => $postData['at_id'],
        ]);
    }
}
