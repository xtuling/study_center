<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2016/12/20
 * Time: 15:51
 */
namespace Apicp\Controller\Contract;

use Common\Service\ContractService;

class SaveController extends AbstractController
{

    /**
     * 【通讯录】保存合同信息
     * @author tangxingguo
     * @time 2016-12-20 15:56:19
     */
    public function Index_post()
    {
        // 接收参数
        $uid = I('post.uid', '', 'trim');
        if (empty($uid)) {
            E('_ERR_UID_IS_NULL');
        }
        $params = [
            // 合同类型（1=劳动合同；2=劳务合同；3=非全日制合同）
            'type' => I('post.type', '', 'trim'),
            // 工作地点
            'work_place' => I('post.work_place', '', 'trim'),
            // 合同工资
            'money' => I('post.money', '', 'trim'),
            // 合同年限
            'years' => I('post.years', '', 'trim'),
            // 劳动合同开始日
            'begin_time' => I('post.begin_time', '', 'trim'),
            // 劳动合同结束日
            'probation' => I('post.probation', '', 'trim'),
            // 试用期
            'end_time' => I('post.end_time', '', 'trim'),
            // 试用期工资
            'probation_money' => I('post.probation_money', '', 'trim'),
            // 试用期开始日
            'probation_begin_time' => I('post.probation_begin_time', '', 'trim'),
            // 试用期结束日
            'probation_end_time' => I('post.probation_end_time', '', 'trim'),
            // 合同签订日期
            'signing_time' => I('post.signing_time', '', 'trim'),
            // 工作单位
            'company' => I('post.company', '', 'trim'),
            // 营业地点
            'company_place' => I('post.company_place', '', 'trim'),
            // 法定代表
            'corporation' => I('post.corporation', '', 'trim'),
            // 员工联系地址
            'user_address' => I('post.user_address', '', 'trim'),
            // 员工联系电话
            'user_mobile' => I('post.user_mobile', '', 'trim'),
            // 紧急联系人
            'urgent_linkman' => I('post.urgent_linkman', '', 'trim'),
            // 紧急联系人电话
            'urgent_mobile' => I('post.urgent_mobile', '', 'trim'),
            // 紧急联系人地址
            'urgent_address' => I('post.urgent_address', '', 'trim')
        ];

        $ContractServ = new ContractService();

        // 检查合同信息
        $params = $ContractServ->checkContractSaveInfo($uid, $params);

        // 操作合同信息
        $contractInfo = $ContractServ->get_by_conds(['uid' => $uid]);
        if (empty($contractInfo)) {
            // 添加
            $params['uid'] = $uid;
            $return = $ContractServ->insert($params);
        } else {
            // 修改
            $return = $ContractServ->update_by_conds(['uid' => $uid], $params);
        }

        // 数据写入失败
        if (!$return) {
            E('_ERR_INSERT_ERROR');
        }
    }
}
