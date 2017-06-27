<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/12/20
 * Time: 14:36
 */
namespace Apicp\Controller\Ssaf;

use Common\Model\SsafModel;
use Common\Service\SsafService;

class SaveController extends AbstractController
{

    /**
     * 通讯录-社保公积金保存
     * @author liyifei
     */
    public function Index_post()
    {
        // 接收参数
        $uid = I('post.uid', '', 'trim');
        $place = I('post.place', '', 'trim');
        $placeType = I('post.place_type');
        $ssType = I('post.ss_type');
        $ssPlace = I('post.ss_place', '', 'trim');
        $ssBase = I('post.ss_base');
        $ssBeginMonth = I('post.ss_begin_month', '', 'trim');
        $ssHandleMonth = I('post.ss_handle_month', '', 'trim');
        $afIsPay = I('post.af_is_pay');
        $afBase = I('post.af_base');
        $afBeginMonth = I('post.af_begin_month', '', 'trim');
        $afHandleMonth = I('post.af_handle_month', '', 'trim');
        $remarks = I('post.remarks', '', 'trim');
        if (empty($uid)) {
            E('_ERR_UID_IS_NULL');
        }

        // 社保类型选择"无"时,清空社保缴纳基数、社保缴纳地、社保起缴月份、社保办理月份
        if ($ssType == SsafModel::SS_TYPE_NONE) {
            $ssBase = SsafService::PAY_BASE;
            $ssPlace = $ssBeginMonth = $ssHandleMonth = '';
        }

        // 公积金缴纳类型设置为"否",清空公积金缴纳基数、公积金起缴月份、公积金办理月份
        if ($afIsPay == SsafModel::AF_IS_PAY_FALSE) {
            $afBase = SsafService::PAY_BASE;
            $afBeginMonth = $afHandleMonth = '';
        }

        // 保存参数
        $param = [
            'uid' => $uid,
            'place' => $place,
            'place_type' => $placeType,
            'ss_type' => $ssType,
            'ss_place' => $ssPlace,
            'ss_base' => $ssBase ? $ssBase : SsafService::PAY_BASE,
            'ss_begin_month' => $ssBeginMonth,
            'ss_handle_month' => $ssHandleMonth,
            'af_is_pay' => $afIsPay,
            'af_base' => $afBase ? $afBase : SsafService::PAY_BASE,
            'af_begin_month' => $afBeginMonth,
            'af_handle_month' => $afHandleMonth,
            'remarks' => $remarks,
        ];

        // 验证保存参数
        $ssafServ = new SsafService();
        $ssafServ->checkSaveParam($param);

        // 操作社保公积金
        $ssafInfo = $ssafServ->get_by_conds(['uid' => $uid]);
        if ($ssafInfo) {
            // 修改
            $result = $ssafServ->update_by_conds(['uid' => $uid], $param);

        } else {
            // 添加
            $result = $ssafServ->insert($param);
        }
        if (!$result) {
            E('_ERR_INSERT_ERROR');
        }
    }
}
