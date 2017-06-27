<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 16/9/29
 * Time: 15:38
 */

namespace Frontend\Controller\Index;

use Com\QRcode;

class InviteQrcodeController extends AbstractController
{

    protected $_require_login = false;

    /**
     * 邀请二维码
     * @author zhonglei
     */
    public function Index()
    {

        $link_id = I('get.link_id', '', 'trim');

        if (empty($link_id)) {
            E('_ERR_UID_IS_NULL');
        }

        $url = oaUrl('Frontend/Index/InviteScan/Index', ['link_id' => $link_id]);
        $qrCode = QRcode::png($url, false, QR_ECLEVEL_L, 12, 1);
        header('Content-Type: image/png');
        imagepng($qrCode);
        exit;
    }
}
