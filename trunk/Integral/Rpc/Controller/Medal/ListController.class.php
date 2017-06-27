<?php
/**
 * Created by PhpStorm.
 * 勋章列表
 * User: zhoutao
 * Date: 2017-05-24 19:33:01
 */

/**
 * 使用须知
 *
 * 调用方法:
 * $rpcURL = call_user_func_array('sprintf', [
 *   '%s://%s/%s/Integral/Rpc/Medal/List',
 *   $_SERVER['REQUEST_SCHEME'],
 *   $_SERVER['HTTP_HOST'],
 *   QY_DOMAIN
 * ]);
 * $rpc = \Com\Rpc::phprpc($rpcUrl);
 * $list = call_user_func(array($rpc, 'Index'));
 *
 * 请求值: 无
 *
 * 返回值预览:
 *  [
 *   {
 *      // 勋章ID
 *      "im_id":"1",
 *      // 图标路径 icon_type 为1时 返回的是 UC at_id对应的附件URL 为2时 返回的是前端对应的路径
 *      "icon":"http://t-rep.vchangyi.com/image/20170524/2XYU13AUn52gG1he8l7MXIJ7r_Xv3-QSautNrIcKuAOGhI_rjkunh7ut6NslgTqe.jpg?atId=3932A71D7F00000137AEDDF6BB4767B4",
 *      // 图标来源 1: 用户上传 2: 系统预设
 *      "icon_type":"1",
 *      // 勋章名称
 *      "name":"勋章1",
 *      // 勋章描述
 *      "desc":"这是一个勋章"
 *    }
 *  ]
 */

namespace Rpc\Controller\Medal;

use Common\Service\MedalService;
use VcySDK\Service;

class ListController extends AbstractController
{
    public function index($imId = 0, $name = '')
    {
        // 初始化SDK
        $config = array(
            'apiUrl' => cfg('UC_APIURL'),
            'enumber' => QY_DOMAIN,
            'pluginIdentifier' => APP_IDENTIFIER,
            'thirdIdentifier' => cfg('SDK_THIRD_IDENTIFIER'),
            'logPath' => RUNTIME_PATH . '/Logs/VcySDK/',
            'apiSecret' => cfg('API_SECRET'),
            'apiSigExpire' => cfg('API_SIG_EXPIRE'),
            'fileConvertApiUrl' => cfg('FILE_CONVERT_API_URL')
        );
        $service = &Service::instance();
        $service->initSdk($config);

        $conds = [];
        if (!empty($imId)) {
            $conds['im_id'] = $imId;
        }
        if (!empty($name)) {
            $conds['name LIKE ?'] = '%' . $name . '%';
        }
        $medalServ = new MedalService();
        $medalArr = $medalServ->list_by_conds($conds);
        // 获取用户上传的图片ID
        $medalArr = $medalServ->replaceAtUrlWhereUserUpload($medalArr);

        return array_intersect_key_reserved($medalArr, [
                'im_id',
                'icon',
                'icon_type',
                'name',
                'desc'
            ]);
    }
}
