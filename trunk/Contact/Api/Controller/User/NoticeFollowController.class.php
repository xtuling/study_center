<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 2016-11-17
 * Time: 11:16:40
 */
namespace Api\Controller\User;

use Common\Common\User;
use Common\Service\NoticeService;
use VcySDK\Service;
use VcySDK\Enterprise;

class NoticeFollowController extends AbstractController
{

    protected $_require_login = false;

    /**
     * 提醒关注页面
     * @author tony
     */
    public function Index_post()
    {
        $notice_id = I('post.notice_id', 0, 'intval');
        if (empty($notice_id)) {
            E('_ERR_PARAM_IS_NULL');
        }

        // 需要的数据是企业名称，关注二维码，以及邀请人的电话
        $noticeServ = new NoticeService();
        $detail = $noticeServ->get($notice_id);
        if (empty($detail)) {
            E('_ERR_INVITE_DATA_IS_NULL');
        }

        // 获取用户的性别
        $userServ = new User();
        $gender = ['', '先生', '女士'];
        $userInfo = $userServ->getByUid($detail['uid']);

        // 获取企业信息
        $epServ = new Enterprise(Service::instance());
        $ep = $epServ->detail();

        $reuslt = [
            'adminer_mobile' => $detail['adminer_mobile'],
            'user_name' => $detail['user_name'],
            'gender' => $gender[$userInfo['memGender']],
            'qy_name' => $ep['corpName'],
            'qy_qrcode' => $ep['corpWxqrcode'],
        ];

        $this->_result = $reuslt;
    }
}
