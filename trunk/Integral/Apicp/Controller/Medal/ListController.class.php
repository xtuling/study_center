<?php
/**
 * Created by IntelliJ IDEA.
 * 勋章列表
 * User: zhoutao
 * Reader: zhoutao 2017-05-31 10:07:35
 * Date: 2017-05-24 15:43:49
 */

namespace Apicp\Controller\Medal;

use Com\PackageValidate;
use Common\Service\MedalService;

class ListController extends AbstractController
{
    public function index()
    {
        $validate = new PackageValidate(
            [],
            [],
            [
                'im_id',
                'name'
            ]
        );
        $postData = $validate->postData;
        $conds = [];
        if (!empty($postData['im_id'])) {
            $conds['im_id'] = $postData['im_id'];
        }
        if (!empty($postData['name'])) {
            $conds['name LIKE ?'] = '%' . $postData['name'] . '%';
        }

        $medalServ = new MedalService();
        $medalArr = $medalServ->list_by_conds($conds);
        // 获取用户上传的图片ID
        $medalArr = $medalServ->replaceAtUrlWhereUserUpload($medalArr);

        $this->_result = [
            'page' => 1,
            'total' => count($medalArr),
            'list' => array_intersect_key_reserved(
                $medalArr,
                [
                    'im_id',
                    'icon',
                    'icon_type',
                    'name',
                    'desc'
                ]
            ),
        ];

        return true;
    }
}
