<?php
/**
 * PrizeService.class.php
 * 奖品申请表
 * @author: zhoutao
 * @version: $Id$
 * @copyright: vchangyi.com
 */
namespace Common\Service;

use Common\Model\ConvertModel;
use Common\Model\ConvertProcessModel;
use Common\Model\PrizeModel;
use VcySDK\Service;
use VcySDK\Message;
use VcySDK\Integral;

class ConvertService extends AbstractService
{

    // 库存+1
    const STOCK_PLUS_ONE = 1;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_d = new ConvertModel();
    }

    /**
     * 微信端查询符合条件的奖品兑换记录总数
     * @param $conds
     * @return array|bool
     */
    public function countWxPrizeConvert($conds)
    {
        return $this->_d->countWxPrizeConvert($conds);
    }

    /**
     * 微信端查询奖品兑换分页列表
     * @param $conds
     * @param null $pageOption
     * @param array $orderOption
     * @return mixed
     */
    public function getWxPrizeConvertPageList($conds, $pageOption = null, $orderOption = array())
    {

        return $this->_d->getWxPrizeConvertPageList($conds, $pageOption, $orderOption);
    }

    /**
     * 管理平台查询符合条件的奖品兑换记录总数
     * @param $conds
     * @return array|bool
     */
    public function countPrizeConvert($conds)
    {
        return $this->_d->countPrizeConvert($conds);
    }

    /**
     * 管理平台查询奖品兑换分页列表
     * @param $conds
     * @param null $pageOption
     * @param array $orderOption
     * @return mixed
     */
    public function getPrizeConvertPageList($conds, $pageOption = null, $orderOption = array())
    {
        return $this->_d->getPrizeConvertPageList($conds, $pageOption, $orderOption);
    }

    /**
     * 微信端查询奖品兑换详情
     * @param $conds
     * @return mixed
     */
    public function getWxPrizeConvertDetailByParams($conds) {
        return $this->_d->getWxPrizeConvertDetailByParams($conds);
    }


    /**
     * 取消奖品兑换
     * @param $reqData
     * @return bool
     */
    public function prizeConvertCanel($reqData) {

        $nowDateTime = MILLI_TIME;
        $nowFormatDateTime = rgmdate(NOW_TIME, 'Y-m-d H:i');

        $memUid = $reqData['memUid'];

        $params = array(
            'uid' => $memUid,
            'ic_id' => $reqData['ic_id']
        );

        $prizeConvertInfo = $this->_d->get_by_conds($params);

        if(empty($prizeConvertInfo)) {
            $this->_set_error("_ERR_CONVERT_NOT_EXIST_ERROR");
            return false;
        }

        // 取消兑换的记录已被管理员处理 获取 已取消
        if($prizeConvertInfo['convert_status'] != ConvertModel::CONVERT_STATUS_ING) {
            if (in_array($prizeConvertInfo['convert_status'], [ConvertModel::CONVERT_STATUS_AGREE, ConvertModel::CONVERT_STATUS_DEFUSE])) {
                $this->_set_error('_ERR_CANEL_RECORD_ALREADY_PROCESSED_ERROR');
            } elseif ($prizeConvertInfo['convert_status'] == ConvertModel::CONVERT_STATUS_CANCEL) {
                $this->_set_error('_ERR_CANEL_RECORD_ALREADY_CANELED');
            }
            return false;
        }

        $PrizeModel = new PrizeModel();
        $ConvertProcessModel = new ConvertProcessModel();

        $prizeInfo = $PrizeModel->getWithOutDeleted($prizeConvertInfo['ia_id']);

        $this->_d->start_trans();
        $PrizeModel->start_trans();
        $ConvertProcessModel->start_trans();

        try {

            // 库存退回
            $PrizeModel->changeReserve($prizeConvertInfo['ia_id'], self::STOCK_PLUS_ONE);

            // 发送取消兑换申请成功消息
            $this->__sendCanelSuccessMsg($memUid, $prizeInfo, $prizeConvertInfo, $nowFormatDateTime);

            // 更新兑换申请记录状态: 已取消
            $this->__modifyConvertStatus($prizeConvertInfo);

            // 插入申请进度记录表
            $this->__insertConvertProcess($reqData, $memUid, $nowDateTime, $prizeConvertInfo, $ConvertProcessModel);

            // 调用UC积分兑换退回接口
            $this->__integralReturn($reqData, $memUid, $prizeConvertInfo, $prizeInfo);

            $this->_d->commit();
            $PrizeModel->commit();
            $ConvertProcessModel->commit();

        } catch (\Exception $e) {
            \Think\Log::record("ic_id : " . $reqData['ic_id']);
            \Think\Log::record("取消兑换申请失败" . var_export($e, true));

            $this->_d->rollback();
            $PrizeModel->rollback();
            $ConvertProcessModel->rollback();

            $this->__sendCanelFailedMsg($memUid, $prizeInfo, $prizeConvertInfo, $nowFormatDateTime);

            $this->_set_error('_ERR_CANEL_FAILED_ERROR');

            return false;
        }

        return true;
    }

    /**
     * 发送取消兑换申请成功通知
     * @param $memUid
     * @param $prizeInfo
     * @param $prizeConvertInfo
     * @param $nowDateTime
     */
    private function __sendCanelSuccessMsg($memUid, $prizeInfo, $prizeConvertInfo, $nowDateTime)
    {
        $smgSdk = new Message(Service::instance());
        $msgParam = [
            'toUser' => $memUid,
            'articles' => [
                [
                    'title' => '您已成功取消奖品兑换申请',
                    'description' => "奖品名称:" . $prizeInfo['name']
                        . "\n兑换编号:" . $prizeConvertInfo['number']
                        . "\n取消时间:" . $nowDateTime,
                    'url' => frontUrl('/app/page/integral/apply-detail', ['ic_id' => $prizeConvertInfo['ic_id']])
                ]
            ],
        ];
        $smgSdk->sendNews($msgParam);
    }

    /**
     * 取消失败通知
     * @param $memUid
     * @param $prizeInfo
     * @param $prizeConvertInfo
     * @param $nowDateTime
     * @internal param $nowDateTimeq
     */
    private function __sendCanelFailedMsg($memUid, $prizeInfo, $prizeConvertInfo, $nowDateTime)
    {
        $smgSdk = new Message(Service::instance());
        $msgParam = [
            'toUser' => $memUid,
            'articles' => [
                [
                    'title' => '由于系统原因，您的奖品兑换申请取消失败，如需继续取消，请再次操作',
                    'description' => "奖品名称:" . $prizeInfo['name']
                        . "\n兑换编号:" . $prizeConvertInfo['number']
                        . "\n取消时间:" . $nowDateTime,
                    'url' => frontUrl('/app/page/integral/apply-detail', ['ic_id' => $prizeConvertInfo['ic_id']])
                ]
            ],
        ];

        $smgSdk->sendNews($msgParam);
    }

    /**
     * 积分退回
     * @param $reqData
     * @param $memUid
     * @param $prizeConvertInfo
     * @param $prizeInfo
     */
    private function __integralReturn($reqData, $memUid, $prizeConvertInfo, $prizeInfo)
    {

        $integralSdk = new Integral(Service::instance());
        $ucResult = $integralSdk->integralExchange([
            'memUid' => $memUid,
            'integral' => $prizeConvertInfo['integral'],
            'businessId' => $prizeConvertInfo['ic_id'],
            'milOptType' => Integral::MANUAL_EXCHAGE_BACK_INTEGRAL,
            'isAdmin' => Integral::IS_ADMIN_FALSE,
            'prizeName' => $prizeInfo['name'],
            'remark' => '【取消兑换】' . $prizeInfo['name'],
            'milCreateMemUid' => $memUid,
            'milCreateMemUsername' => $reqData['memUsername'],
            'msgIdentifier' => APP_IDENTIFIER
        ]);

        \Think\Log::record("积分退回 ucResult : " . var_export($ucResult, true), \Think\Log::DEBUG);
    }

    /**
     * 更新兑换申请记录状态
     * @param $prizeConvertInfo
     */
    private function __modifyConvertStatus($prizeConvertInfo)
    {
        $convertUpadteData = array(
            'convert_status' => ConvertModel::CONVERT_STATUS_CANCEL
        );
        $this->update($prizeConvertInfo['ic_id'], $convertUpadteData);
    }

    /**
     * 插入申请进度记录表
     * @param $reqData
     * @param $memUid
     * @param $nowDateTime
     * @param $prizeConvertInfo
     * @param $ConvertProcessModel
     */
    private function __insertConvertProcess($reqData, $memUid, $nowDateTime, $prizeConvertInfo, $ConvertProcessModel)
    {
        $processData = array(
            'uid' => $memUid,
            'ic_id' => $reqData['ic_id'],
            'operate' => ConvertProcessModel::CONVERT_STATUS_CANCEL,
            'operating_time' => $nowDateTime,
            'integral' => $prizeConvertInfo['integral'],
            'mark' => empty($reqData['mark']) ? '' : $reqData['mark'],
        );

        $ConvertProcessModel->insert($processData);
    }

}
