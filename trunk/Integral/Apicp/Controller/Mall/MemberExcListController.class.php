<?php
/**
 * Created by IntelliJ IDEA.
 * 兑奖列表
 * User: gaoyaqiu
 * Date: 2016-12-09
 */

namespace Apicp\Controller\Mall;

use Common\Common\Attach;
use Common\Model\ConvertModel;
use Common\Service\ConvertService;
use Common\Common\Department;
use Common\Common\User;
use VcySDK\Adminer;
use VcySDK\Service;

class MemberExcListController extends AbstractController
{
    protected $_require_login = false;

    public function before_action($action = '')
    {
        // 开启自动获取
        $this->autoGetData = true;
        $this->field = [
            'uid' => [
                'require' => true,
                'verify' => 'strval',
                'cn' => '人员ID',
            ],
            'convert_status' => [
                'require' => false,
                'verify' => 'intval',
                'cn' => '兑换状态',
                'area' => [ConvertModel::CONVERT_STATUS_ING, ConvertModel::CONVERT_STATUS_AGREE, ConvertModel::CONVERT_STATUS_DEFUSE, ConvertModel::CONVERT_STATUS_CANCEL],
            ],
            'startTime' => [
                'require' => false,
                'verify' => 'intval',
                'cn' => '申请开始时间',
            ],
            'endTime' => [
                'require' => false,
                'verify' => 'intval',
                'cn' => '申请结束时间',
            ],
            'page' => [
                'require' => false,
                'default' => 1,
                'verify' => 'intval',
            ],
            'limit' => [
                'require' => false,
                'default' => 15,
                'verify' => 'intval',
            ],
        ];

        return parent::before_action($action);
    }

    public function Index()
    {

        // 分页参数
        list($start, $perpage, $this->_result['page']) = $this->getPageOption('page', 'limit');
        $convertServ = new ConvertService();

        // 获取兑换列表
        $this->data['uids'] = $this->data['uid'];
        unset($this->data['uid']);
        $this->_result['list'] = $convertServ->getPrizeConvertPageList($this->data, [$start, $perpage], ['created' => 'DESC']);
        // 总数
        $countConds = [
            'uid' => $this->data['uids'],
        ];
        if (!empty($this->data['startTime'])) {
            $countConds['created<?'] = $this->data['startTime'];
        }
        if (!empty($this->data['startTime'])) {
            $countConds['created>?'] = $this->data['endTime'];
        }
        if (!empty($this->data['convert_status'])) {
            $countConds['convert_status'] = $this->data['convert_status'];
        }
        $this->_result['total'] = $convertServ->count_by_conds($countConds);

        // 处理返回数据
        $this->dealResult();

        return true;
    }

    /**
     * 处理返回数据
     * @return bool
     */
    protected function dealResult()
    {
        // 获取需要查询操作时间的兑换记录
        $operatorArr = [];
        $uidArr = [];
        foreach ($this->_result['list'] as &$item) {
            // 获取管理员信息的情况
            if (in_array($item['convert_status'], [ConvertModel::CONVERT_STATUS_AGREE, ConvertModel::CONVERT_STATUS_DEFUSE])) {
                $operatorArr[] = $item['operator'];
            // 获取人员信息的情况
            } elseif (in_array($item['convert_status'], [ConvertModel::CONVERT_STATUS_CANCEL])) {
                $uidArr[] = $item['uid'];
            }

            unset($item['applicant_phone'], $item['number']);
        }

        // 获取操作人员信息
        $this->getOperatorInfo($operatorArr);
        // 获取人员信息
        $this->getUidInfo($uidArr);

        return true;
    }

    /**
     * 获取操作人员信息
     * @param $operatorArr
     * @return bool
     */
    protected function getOperatorInfo($operatorArr)
    {
        if (empty($operatorArr)) {
            return true;
        }

        // 获取操作人ID
        $adminer = new Adminer(Service::instance());
        foreach ($operatorArr as $eaId) {
            $operatorList[] = $adminer->fetch(['eaId' => $eaId]);
        }
        // 合并操作人员信息
        $operatorList = array_combine_by_key($operatorList, 'eaId');
        foreach ($this->_result['list'] as &$item) {
            if (!empty($item['operator'])) {
                $item['operatorName'] = $operatorList[$item['operator']]['eaRealname'];
                $item['eaMobile'] = $operatorList[$item['operator']]['eaMobile'];
            }
        }

        return true;
    }

    /**
     * 获取人员信息
     * @param $uidArr
     * @return bool
     */
    protected function getUidInfo($uidArr)
    {
        if (empty($uidArr)) {
            return true;
        }

        $user = new User();
        $userList = $user->listByUid($uidArr);
        foreach ($this->_result['list'] as &$item) {
            if (in_array($item['convert_status'], [ConvertModel::CONVERT_STATUS_CANCEL])) {
                $item['operatorName'] = $userList[$item['uid']]['memUsername'];
            }
        }

        return true;
    }
}
