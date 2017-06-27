<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2016/12/20
 * Time: 11:29
 */
namespace Apicp\Controller\Contract;

use Common\Service\ContractService;
use Common\Common\User;
use VcySDK\Enterprise;
use VcySDK\Service;

class InfoController extends AbstractController
{

    /**
     * 【通讯录】合同信息详情
     * @author tangxingguo
     * @time 2016-12-20 15:56:33
     */
    public function Index_post()
    {
        $uid = I('post.uid', '', 'trim');
        if (empty($uid)) {
            E('_ERR_UID_IS_NULL');
        }

        // 用户信息(从缓存架构用户信息表中的数据获取用户信息,参数设为true越过缓存)
        $commUser = new User();
        $userInfo = $commUser->getByUid($uid);
        if (empty($userInfo)) {
            E('_ERR_DATA_IS_NULL');
        }

        // 合同信息
        $ContractServ = new ContractService();
        $contractInfo = $ContractServ->get_by_conds(['uid' => $uid]);

        // 初始化返回值
        $result = [
            'uid' => $uid,
            'username' => $userInfo['memUsername'],
            'dp_name' => $userInfo['dpName'],
            'identity_card' => $userInfo['memIdcard'],
            'join_time' => $userInfo['memJoinTime'],
            'user_type' => $userInfo['nature'],
            'job' => $userInfo['memJob'],
            'type' => $contractInfo['type'],
            'work_place' => $contractInfo['work_place'],
            'money' => $contractInfo['money'],
            'years' => $contractInfo['years'],
            'begin_time' => $contractInfo['begin_time'],
            'end_time' => $contractInfo['end_time'],
            'probation' => $contractInfo['probation'],
            'probation_money' => $contractInfo['probation_money'],
            'probation_begin_time' => $contractInfo['probation_begin_time'],
            'probation_end_time' => $contractInfo['probation_end_time'],
            'signing_time' => $contractInfo['signing_time'],
            'company' => $contractInfo['company'],
            'company_place' => $contractInfo['company_place'],
            'corporation' => $contractInfo['corporation'],
            'user_address' => $contractInfo['user_address'],
            'user_mobile' => $contractInfo['user_mobile'],
            'urgent_linkman' => $contractInfo['urgent_linkman'],
            'urgent_mobile' => $contractInfo['urgent_mobile'],
            'urgent_address' => $contractInfo['urgent_address']
        ];

        // 默认信息
        $enterpriseSdk = new Enterprise(Service::instance());
        $enterpriseInfo = $enterpriseSdk->detail();
        $defaultData = [
            // 默认为系统设置-账户信息内的公司名称
            'company' => $enterpriseInfo['epName'],
            // 默认为系统设置-详细地址
            'company_place' => $enterpriseInfo['epProvince']
                .$enterpriseInfo['epCity'].$enterpriseInfo['epArea'].$enterpriseInfo['epAddress'],
            // 默认员工手机号码
            'user_mobile' => $userInfo['memMobile']

        ];

        // 合同信息格式化
        $result = $ContractServ->disposeData($result, '', $defaultData);

        $this->_result = $result;
    }
}
