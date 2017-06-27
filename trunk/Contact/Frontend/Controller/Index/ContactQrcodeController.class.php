<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/10/11
 * Time: 11:54
 */

namespace Frontend\Controller\Index;

use Com\QRcode;
use VcySDK\Service;
use VcySDK\Enterprise;
use Common\Common\User;

class ContactQrcodeController extends AbstractController
{

    protected $_require_login = false;

    /**
     * 保存通讯录信息的二维码
     * @author liyifei
     */
    public function Index()
    {

        $uid = I('get.uid', '', 'trim');
        $getMobile = I('get.mobile', '', 'trim');
        $getEmail = I('get.email', '', 'trim');

        // 用户信息(从缓存架构用户信息表中的数据获取用户信息,参数设为true越过缓存)
        $userServ = new User();
        $user = $userServ->getByUid($uid);

        // 姓名
        $username = $user['memUsername'];
        // 移动电话
        $mobile = empty($getMobile) ? $user['memMobile'] : $getMobile;
        // 电子邮箱
        $email = empty($getEmail) ? $user['memEmail'] : $getEmail;
        // 头像
        $face = $user['memFace'];

        // 企业信息
        $epServ = new Enterprise(Service::instance());
        $ep = $epServ->detail();

        // 组织
        $appName = $ep['corpName'];

        // url地址组装(固定格式)
        $url = "BEGIN:VCARD
                    N:{$username}
                    ORG:{$appName}
                    TEL;CELL:{$mobile}
                    EMAIL;WORK:{$email}
                END:VCARD";

        // 生成二维码
        $qrcode = QRcode::png($url, false, QR_ECLEVEL_L, 12, 1);
        header('Content-Type: image/png');
        imagepng($qrcode);

        exit();
    }
}
