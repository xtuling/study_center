<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/18
 * Time: 11:51
 */
namespace Api\Controller\User;

use Common\Common\User;
use Common\Service\CardService;
use VcySDK\Enterprise;
use VcySDK\Service;

class CardController extends AbstractController
{

    // 无需登录,内外部成员均可访问
    protected $_require_login = false;

    /**
     * 【通讯录】名片详情
     * @author liyifei
     * @time 2016-09-18 11:54:21
     */
    public function Index_post()
    {
        $uid = I('post.uid', '', 'trim');
        $isShow = I('post.is_show', 2, 'intval');
        if (empty($uid)) {
            E('_ERR_UID_IS_NULL');
        }

        $newUser = new User();
        $epServ = new Enterprise(Service::instance());
        // 用户信息(从缓存架构用户信息表中的数据获取用户信息,参数设为true越过缓存)
        $userInfo = $newUser->getByUid($uid);

        // 企业信息
        $ep = $epServ->detail();

        // 实例化
        $cardServ = new CardService();
        $list = $cardServ->getCardByUid($uid, $userInfo, $isShow);

        // 初始化返回值
        $cardInfo = [
            'qy_name' => $ep['corpName'],
            'name' => $userInfo['memUsername'],
            'sex' => $userInfo['memGender'],
            'title' => $userInfo['memJob'],
            'face' => $userInfo['memFace'],
            'qr_code' => oaUrl('Frontend/Index/ContactQrcode/index', ['uid' => $uid]),
            'list' => $list,
        ];

        $this->_result = $cardInfo;
    }
}
