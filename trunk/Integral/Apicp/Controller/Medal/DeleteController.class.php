<?php
/**
 * Created by IntelliJ IDEA.
 * 勋章删除
 * User: zhoutao
 * Reader: zhoutao 2017-05-31 10:06:30
 * Date: 2017-05-24 16:34:22
 */

namespace Apicp\Controller\Medal;

use Com\PackageValidate;
use Common\Service\MedalService;

class DeleteController extends AbstractController
{
    public function index()
    {
        $validate = new PackageValidate(
            [
                'im_id' => 'require',
            ],
            [
                'im_id.require' => L('_ERR_PARAM_CAN_NOT_BE_EMPTY', ['name' => '勋章ID']),
            ],
            [
                'im_id'
            ]
        );
        $postData = $validate->postData;

        $medalServ = new MedalService();
        $medalServ->delete($postData['im_id']);

        return true;
    }
}
