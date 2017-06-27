<?php
/**
 * Created by IntelliJ IDEA.
 * 勋章详情
 * User: zhoutao
 * Reader: zhoutao 2017-05-31 10:06:49
 * Date: 2017-05-24 16:34:22
 */

namespace Apicp\Controller\Medal;

use Com\Assert;
use Com\PackageValidate;
use Common\Common\Attach;
use Common\Model\MedalModel;
use Common\Service\MedalService;

class DetailController extends AbstractController
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
        $medalData = $medalServ->get($postData['im_id']);
        Assert::notEmpty($medalData, L('_ERR_EMPTY_DATA', ['name' => '勋章']));

        // 过滤字段
        $medalData = array_intersect_key_reserved($medalData, [
            'im_id',
            'icon',
            'icon_type',
            'name',
            'desc'
        ], true);
        // 获取用户上传的勋章图标URL
        if ($medalData['icon_type'] == MedalModel::ICON_TYPE_USER_UPLOAD) {
            $atServ = new Attach();
            $medalData['icon_url'] = $atServ->getAttachUrl($medalData['icon']);
        }

        $this->_result = $medalData;

        return true;
    }
}
