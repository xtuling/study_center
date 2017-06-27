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

class ExcListController extends AbstractController
{
    protected $_require_login = false;

    public function before_action($action = '')
    {
        // 开启自动获取
        $this->autoGetData = true;
        $this->field = [
            'ia_id' => [
                'require' => false,
                'verify' => 'intval',
                'cn' => '奖品ID',
            ],
            'name' => [
                'require' => false,
                'verify' => 'strval',
                'cn' => '奖品名称',
            ],
            'memUserName' => [
                'require' => false,
                'verify' => 'strval',
                'cn' => '姓名',
            ],
            'convert_status' => [
                'require' => false,
                'verify' => 'intval',
                'cn' => '兑奖状态',
                'area' => [ConvertModel::CONVERT_STATUS_ING, ConvertModel::CONVERT_STATUS_AGREE, ConvertModel::CONVERT_STATUS_DEFUSE, ConvertModel::CONVERT_STATUS_CANCEL],
            ],
            'dep' => [
                'require' => false,
                'verify' => 'strval',
                'cn' => '部门ID',
            ],
            'number' => [
                'require' => false,
                'verify' => 'strval',
                'cn' => '兑换编码',
            ],
            'applicant_phone' => [
                'require' => false,
                'verify' => 'strval',
                'cn' => '手机号',
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

        // 过滤用户
        $uidArray = array();
        // 根据用户名查询
        $memUserName = $this->data['memUserName'];
        // 按部门查询
        $dpId = $this->data['dep'];
        $innserParams = array();

        if(!empty($memUserName) || !empty($dpId)){
            $memServ = new User();
            $innserParams['dpId'] = $dpId;
            $innserParams['memUsername'] = $memUserName;
            $userData = $memServ->listByConds($innserParams, $start, $perpage);
            if(!empty($userData) && $userData['total'] > 0){
                $uidArray = array_column($userData['list'], 'memUid');
            }

            if(empty($uidArray)){
                $this->_result['list'] = array();
                $this->_result['total'] = 0;
                return true;
            }
            $this->data['uids'] = $uidArray;
        }

        // 兑奖列表
        $this->_result['list'] = $convertServ->getPrizeConvertPageList($this->data, [$start, $perpage]);
        $this->_result['total'] = $convertServ->countPrizeConvert($this->data);
        // 处理返回值
        $this->dealResult();

        return true;
    }


    /**
     * 处理返回值
     * @return bool
     */
    protected function dealResult()
    {
        $memServ = new User();
        foreach ($this->_result['list'] as &$item) {
            // 获取用户
            $member = $memServ->getByUid($item['uid']);
            if(!empty($member)){
                $item['memUserName'] = $member['memUsername'];
                // 获取部门名称
                $depName = array_column($member['dpName'], 'dpName');
                $item['dpName'] = implode(';', $depName);

            }
        }

        return true;
    }
}
