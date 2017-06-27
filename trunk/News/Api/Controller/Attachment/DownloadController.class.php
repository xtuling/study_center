<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/19
 * Time: 15:24
 */
namespace Api\Controller\Attachment;

use Common\Common\Constant;
use Common\Service\ArticleService;
use Common\Service\AttachService;
use VcySDK\Service;
use VcySDK\Message;

class DownloadController extends \Api\Controller\AbstractController
{
    /**

     * Download

     * @author liyifei

     * @desc 下载附件

     * @param int at_id:true 附件ID

     * @return mixed

     */
    public function Index()
    {
        $at_id = I("post.at_id", '', 'trim');
        if (empty($at_id)) {
            E('_ERR_ATTACH_ID_EMPTY');
        }

        // 附件信息
        $attachServ = new AttachService();
        $attach = $attachServ->get_by_conds([
            'at_id' => $at_id,
            'at_type' => Constant::ATTACH_TYPE_FILE
        ]);
        if (!$attach) {
            E('_ERR_ATTACH_NOT_FOUND');
        }

        // 附件大小是否超限
        if ($attach['at_size'] > Constant::DOWNLOAD_FILE_MAX_SIZE) {
            E('_ERR_ATTACH_FILE_SIZE_OVERRUN');
        }

        // 新闻附件是否可下载
        $articleServ = new ArticleService();
        $article = $articleServ->get_by_conds([
            'article_id' => $attach['article_id'],
            'is_download' => Constant::NEWS_IS_DOWNLOAD_TRUE
        ]);
        if (!$article) {
            E('_ERR_ATTACH_IS_DOWNLOAD_FALSE');
        }

        // 当前登录用户
        $service = &Service::instance();
        $uid = $this->_login->user["memUid"];

        // 推送文件到应用主页面
        $messageServ = new Message($service);
        $messageServ->sendFile([
            "toUser" => $uid,
            "msgtype" => "file",
            "atId" => $at_id
        ]);
    }
}
