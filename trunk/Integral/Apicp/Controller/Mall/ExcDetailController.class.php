<?php
/**
 * Created by IntelliJ IDEA.
 * 兑奖详情
 * User: zhoutao
 * Date: 2016-12-12
 */

namespace Apicp\Controller\Mall;

use Common\Common\User;
use Common\Model\ConvertProcessModel;
use Common\Service\ConvertProcessService;
use Common\Service\ConvertService;
use Common\Service\PrizeService;
use VcySDK\Adminer;
use VcySDK\Service;

class ExcDetailController extends AbstractController
{
    public function before_action($action = '')
    {
        // 开启自动获取
        $this->autoGetData = true;
        $this->field = [
            'ic_id' => [
                'require' => true,
                'verify' => 'intval',
                'cn' => '兑换ID',
            ],
        ];

        return parent::before_action($action);
    }

    public function Index()
    {
        // 申请数据
        $convertServ = new ConvertService();
        $convertData = $convertServ->get($this->data['ic_id']);
        if (empty($convertData)) {
            E('_ERR_CONVERT_NOT_EXIST_ERROR');
            return false;
        }

        // 奖品数据
        $prizeServ = new PrizeService();
        $prizeData = $prizeServ->getWithOutDeleted($convertData['ia_id']);
        $this->_result['prize'] = [
            'ia_id' => $prizeData['ia_id'],
            'name' => $prizeData['name'],
            'integral' => $convertData['integral'],
            'number' => $convertData['number'],
            'convert_status' => $convertData['convert_status'],
            'status' => $prizeData['status'],
        ];

        // 成员信息
        $member = new User();
        $user = $member->getByUid($convertData['uid']);
        $this->_result['member'] = [
            'memUserName' => $user['memUsername'],
            'phone' => $convertData['applicant_phone'],
            'email' => $convertData['applicant_email'],
            'mark' => $convertData['applicant_mark'],
        ];

        // 申请进度
        $convertProceServ = new ConvertProcessService();
        $adminerSdk = new Adminer(Service::instance());
        $processList = $convertProceServ->list_by_conds(['ic_id' => $this->data['ic_id']]);
        // 现在总体进度 最多两个, 在foreach里查询不影响, 如果后面流程多了就得 优化成先查询人员信息
        foreach ($processList as $item) {
            $processData = [
                'operate' => $item['operate'],
                'operatingTime' => $item['operating_time'],
                'integral' => $item['integral'],
                'mark' => $item['mark']
            ];

            // 如果是待处理 或者取消 那么操作人姓名 成员信息里就有
            if (in_array($item['operate'], [ConvertProcessModel::CONVERT_STATUS_ING, ConvertProcessModel::CONVERT_STATUS_CANCEL])) {
                $processData['userName'] = $user['memUsername'];
            }
            // 如果是同意 或者拒绝 那么操作人姓名就是管理员
            if (in_array($item['operate'], [ConvertProcessModel::CONVERT_STATUS_AGREE, ConvertProcessModel::CONVERT_STATUS_DEFUSE])) {
                $adminerData = $adminerSdk->fetch([
                    'eaId' => $item['operator']
                ]);
                $processData['userName'] = $adminerData['eaRealname'];
            }

            $this->_result['process'][] = $processData;
        }

        return true;
    }
}
