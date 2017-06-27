<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/12/20
 * Time: 14:29
 */
namespace Apicp\Controller\Ssaf;

use Common\Common\User;
use Common\Model\SsafModel;
use Common\Service\SsafService;

class InfoController extends AbstractController
{

    /**
     * 通讯录-社保公积金详情
     * @author liyifei
     */
    public function Index_post()
    {
        // 接收参数
        $uid = I('post.uid', '', 'trim');

        // 用户信息
        $userServ = new User();
        $userInfo = $userServ->getByUid($uid);
        if (empty($userInfo)) {
            E('_ERR_USER_UNDEFINED');
        }

        // 社保公积金信息
        $ssafServ = new SsafService();
        $ssafInfo = $ssafServ->get_by_conds(['uid' => $uid]);

        // 初始化返回值
        $result = [
            'uid' => $uid,
            'username' => $userInfo['memUsername'],
            'dp_name' => $userInfo['dpName'],
            "identity_card" => $userInfo['memIdcard'],
            "join_time" => $userInfo['memJoinTime'],
            "user_type" => $userInfo['nature'],
            "job" => $userInfo['memJob'],
            "place" => '',
            "place_type" => SsafModel::PLACE_TYPE_CITY,
            "ss_type" => SsafModel::SS_TYPE_NONE,
            "ss_place" => '',
            "ss_base" => '',
            "ss_begin_month" => '',
            "ss_handle_month" => '',
            "af_is_pay" => SsafModel::AF_IS_PAY_FALSE,
            "af_base" => '',
            "af_begin_month" => '',
            "af_handle_month" => '',
            "remarks" => '',
        ];

        // 用户社保公积金信息存在
        if (!empty($ssafInfo)) {
            $result['place'] = $ssafInfo['place'];
            $result['place_type'] = intval($ssafInfo['place_type']);
            $result['ss_type'] = intval($ssafInfo['ss_type']);
            $result['ss_place'] = $ssafInfo['ss_place'];
            $result['ss_base'] = $ssafInfo['ss_base'];
            $result['ss_begin_month'] = $ssafInfo['ss_begin_month'];
            $result['ss_handle_month'] = $ssafInfo['ss_handle_month'];
            $result['af_is_pay'] = $ssafInfo['af_begin_month'] == '' ? SsafModel::AF_IS_PAY_FALSE : SsafModel::AF_IS_PAY_TRUE;
            $result['af_base'] = $ssafInfo['af_base'];
            $result['af_begin_month'] = $ssafInfo['af_begin_month'];
            $result['af_handle_month'] = $ssafInfo['af_handle_month'];
            $result['remarks'] = $ssafInfo['remarks'];
        }

        $this->_result = $result;
    }
}
