<?php
/**
 * Created by PhpStorm.
 * author: tony
 * time：2016-12-20 11:26:36
 */
namespace Apicp\Controller\Ssaf;

use Common\Common\User;
use Common\Model\SsafModel;
use Common\Service\SsafService;

class ListController extends AbstractController
{

    /**
     * 通讯录，社保公积金列表
     * @author tony
     */
    public function Index_post()
    {
        // 接收参数
        $name = I('post.keyword');
        $dpids = I('post.dp_ids');
        // 要判断值是否为空，所以不能设默认值
        $ss_is_pay = I('post.ss_is_pay');
        $af_is_pay = I('post.af_is_pay');
        $page = I('post.page', 1, 'intval');
        $limit = I('post.limit', 10, 'intval');

        // 如果ss_is_pay和af_is_pay不为空，就从本地数据库中获取uid，然后看是排除还是包含
        $ssafServ = new SsafService();
        // uids是范围（in）还是排除（ex)
        $in_or_ex = '';
        // 公积金列表结果
        $ssafList = null;
        // 公积金表中查的的uid
        $uids = null;
        $list = null;

        $ssafList = $ssafServ->listSsafByConds('', $ss_is_pay, $af_is_pay);
        $uids = empty($ssafList) ? [] : array_column($ssafList, 'uid');
        if ($ss_is_pay !== '' || $af_is_pay !== '') {
            if ($ss_is_pay == SsafService::SS_IS_PAY_TURE || $af_is_pay == SsafModel::AF_IS_PAY_TRUE) {
                // ssaf数据表中的是范围，如果为空就返回空列表，如果不为空就表示uid范围
                $in_or_ex = 'in';
            } else {
                $in_or_ex = 'ex';
            }
        }

        // 没有找到数据
        if ($in_or_ex == 'in' && empty($uids)) {
            $list = [];
        } else {
            // 从架构拉数据
            $conds = [];


            if ($name !== '') {
                $conds['memUsername'] = $name;
            }
            if (!empty($dpids)) {
                $conds['dpIdList'] = $dpids;
            }
            if (!empty($uids)) {
                // 判断是范围还是排除
                switch ($in_or_ex) {
                    case 'in':
                        $conds['memUids'] = $uids;
                        break;
                    case 'ex':
                        $conds['excludeMemuids'] = $uids;
                        break;
                }
            }

            if (!empty($ssafList)) {
                $ssafList = array_combine($uids, $ssafList);
            }
            $memberServ = &User::instance();
            // UC排序规则
            $order = [
                'memIndex' => 'ASC',
            ];
            $memlist = $memberServ->listByConds($conds, $page, $limit, $order);
            // 组合数据
            foreach ($memlist['list'] as $mem) {
                $dpname = [];
                foreach ($mem['dpName'] as $d) {
                    $dpname[] = [
                        'dpId' => $d['dpId'],
                        'dpName' => $d['dpName'],
                    ];
                }
                $ssafInfo = empty($ssafList[$mem['memUid']]) ? [] : $ssafList[$mem['memUid']];
                $list[] = [
                    'uid' => $mem['memUid'],
                    'username' => $mem['memUsername'],
                    'dp_name' => $dpname,
                    'identity_card' => $mem['memIdcard'],
                    'place' => empty($ssafInfo['place']) ? '' : $ssafInfo['place'],
                    'place_type' => empty($ssafInfo['place_type']) ? 0 : $ssafInfo['place_type'],
                    'ss_type' => empty($ssafInfo['ss_type']) ? SsafModel::SS_TYPE_NONE : $ssafInfo['ss_type'],
                    'ss_base' => $ssafInfo['ss_base'],
                    'ss_is_pay' => $ssafInfo['ss_begin_month'] == '' ? SsafService::SS_IS_PAY_FALSE : SsafService::SS_IS_PAY_TURE,
                    'af_base' => $ssafInfo['af_base'],
                    'af_is_pay' => $ssafInfo['af_begin_month'] == '' ? SsafModel::AF_IS_PAY_FALSE : SsafModel::AF_IS_PAY_TRUE,
                ];
            }
        }

        $result = [
            'page' => $page,
            'limit' => empty($memlist) ? $limit : $memlist['pageSize'],
            'total' => empty($memlist) ? 0 : $memlist['total'],
            'list' => $list,
        ];
        $this->_result = $result;
    }
}
