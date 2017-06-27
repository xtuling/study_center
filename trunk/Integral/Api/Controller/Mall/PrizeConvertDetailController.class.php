<?php
/**
 * Created by IntelliJ IDEA.
 * 微信端奖品兑换详情
 * User: zs_anything
 * Date: 2016/12/09
 * Time: 上午11:27
 */

namespace Api\Controller\Mall;

use Common\Common\User;
use Common\Model\ConvertModel;
use Common\Service\ConvertProcessService;
use Common\Service\ConvertService;
use Common\Common\Attach;
use VcySDK\Adminer;
use VcySDK\Service;

class PrizeConvertDetailController extends AbstractController
{

    public function Index()
    {

        $reqParams = I('post.');

        // 兑换ID为空
        if (empty($reqParams['ic_id'])) {
            $this->_set_error('_ERR_ICID_NULL_ERROR');
            return false;
        }

        $loginUserInfo = $this->_login->user;

        $params = array(
            'memUid' => $loginUserInfo['memUid'],
            'ic_id' => $reqParams['ic_id']
        );

        $convertService = new ConvertService();
        $data = $convertService->getWxPrizeConvertDetailByParams($params);

        if (empty($data)) {
            $this->_result = array();
            return true;
        }

        $this->formatAttrUrl($data);

        $this->_result = array(
            'ic_id' => $data['ic_id'],
            'prizeStatus' => $data['prize_status'],
            'ia_id' => $data['ia_id'],
            'convertInfo' => $this->formatConvertInfo($data),
            'convertUserInfo' => $this->formatConvertUserInfo($data, $loginUserInfo['memUsername']),
            'convertProcess' => $this->formatConverProcess($loginUserInfo['memUid'], $data['ic_id'], $loginUserInfo['memUsername'])
        );

        return true;
    }

    /**
     * 封装奖品图片url
     * @param $data
     * @return mixed
     */
    private function formatAttrUrl(&$data)
    {

        $attIdArr = explode(',', $data['picture']);

        $attachUtil = new Attach();
        $attrUrl = $attachUtil->getAttachUrl($attIdArr[0]);

        if (empty($attrUrl)) {
            $data['picture'] = '';
        } else {
            $data['picture'] = $attrUrl;
        }

        return $data;
    }

    /**
     * 封装兑换信息
     * @param $data
     * @return array
     */
    public function formatConvertInfo($data)
    {
        $convertInfo = array(
            'convert_status' => $data['convert_status'],
            'name' => $data['name'],
            'integral' => $data['integral'],
            'picture' => $data['picture'],
            'number' => $data['number'],
        );
        return $convertInfo;
    }

    /**
     * 封装兑换人信息
     * @param $data
     * @param $memUsername
     * @return array
     */
    public function formatConvertUserInfo($data, $memUsername)
    {

        $convertUserInfo = array(
            'memUid' => $data['uid'],
            'memUsername' => $memUsername,
            'applicant_phone' => $data['applicant_phone'],
            'applicant_email' => $data['applicant_email'],
            'applicant_mark' => empty($data['applicant_mark']) ? '' : $data['applicant_mark']
        );
        return $convertUserInfo;
    }

    /**
     * 封装审批进程
     * @param $memUid      兑换人id
     * @param $ic_id       兑换id
     * @param $memUsername 兑换人姓名
     * @return array|bool
     */
    public function formatConverProcess($memUid, $ic_id, $memUsername)
    {
        $conds = array(
            'uid' => $memUid,
            'ic_id' => $ic_id
        );

        $convertProcessService = new ConvertProcessService();
        $convertProcess = $convertProcessService->list_by_conds($conds, null, ['created' => 'ASC']);

        $userInfo = [];
        $adminSdk = new Adminer(Service::instance());

        foreach ($convertProcess as &$obj) {

            // 待处理
            if ($obj['operate'] == ConvertModel::CONVERT_STATUS_ING) {
                $obj['userName'] = $memUsername;
            } else if ($obj['operate'] == ConvertModel::CONVERT_STATUS_CANCEL) {
                // 已取消
                $obj['userName'] = $memUsername;
            } else {
                $userInfo = $adminSdk->fetch(['eaId' => $obj['operator']]);
                $obj['userName'] = $userInfo['eaRealname'];
                $obj['userPhone'] = $userInfo['eaMobile'];
            }
        }

        unset($userInfo);

        return $convertProcess;
    }

}
