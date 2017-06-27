<?php
/**
 * Created by IntelliJ IDEA.
 * 奖品操作(同意/拒绝)
 * User: zhoutao
 * Date: 2016-12-12
 * Time: 10:00:26
 */

namespace Apicp\Controller\Mall;

use Common\Model\ConvertModel;
use Common\Model\ConvertProcessModel;
use Common\Service\ConvertProcessService;
use Common\Service\ConvertService;
use Common\Service\PrizeService;
use VcySDK\Integral;
use VcySDK\Message;
use VcySDK\Service;

class PrizeOperateController extends AbstractController
{
    /** 统一操作 */
    const TYPE_AGREE = 1;
    /** 拒绝操作 */
    const TYPE_DISAGREE = 2;
    /** 同意 */
    const TYPE_AGREE_CN = '同意';
    /** 拒绝 */
    const TYPE_DISAGREE_CN = '拒绝';

    public function before_action($action = '')
    {
        // 开启自动获取
        $this->autoGetData = true;
        $this->field = [
            'ic_id' => [
                'require' => true,
                'cn' => '要操作的奖品ID',
            ],
            'type' => [
                'require' => true,
                'verify' => 'intval',
                'cn' => '操作类型',
                'area' => [
                    self::TYPE_AGREE,
                    self::TYPE_DISAGREE
                ]
            ],
        ];

        return parent::before_action($action);
    }

    public function Index()
    {
        $this->getMark();
        settype($this->data['ic_id'], 'array');

        // 为了返回哪些审批出错了, 这里得遍历操作
        $convertServ = new ConvertService();
        $convertProcessServ = new ConvertProcessService();
        $prizeServ = new PrizeService();

        $errorIcId = [];
        foreach ($this->data['ic_id'] as $icId) {
            try {
                $convertServ->start_trans();
                $convertProcessServ->start_trans();

                $convertData = $convertServ->get($icId);
                if (empty($convertData)) {

                    continue;
                }
                // 已经被处理
                if ($convertData['convert_status'] != ConvertModel::CONVERT_STATUS_ING) {
                    // 如果只有单条操作
                    if (count($this->data['ic_id']) == 1) {
                        // 申请人已经取消
                        if ($convertData['convert_status'] == ConvertModel::CONVERT_STATUS_CANCEL) {
                            E('_ERR_CANEL_RECORD_APPLICANT_ALREADY_CANELED');
                        }
                        E('_ERR_PRIZE_OPERATE_WAS_PROCESSED');
                        return false;
                    }
                    // 多条操作
                    $errorIcId[] = $icId;
                    continue;
                }

                // 查询奖品
                $prizeData = $prizeServ->getWithOutDeleted($convertData['ia_id']);
                if (empty($prizeData)) {
                    continue;
                }

                // 更新申请主表
                $convertServ->update($icId, [
                    'operator' => $this->_login->user['eaId'],
                    'convert_status' => $this->data['type'] == self::TYPE_AGREE ?
                        ConvertModel::CONVERT_STATUS_AGREE : ConvertModel::CONVERT_STATUS_DEFUSE,
                ]);

                // 写入进度表
                $convertProcessServ->insert([
                    'ic_id' => $icId,
                    'uid' => $convertData['uid'],
                    'integral' => $this->data['type'] == self::TYPE_AGREE ? $convertData['integral'] * -1 : $convertData['integral'],
                    'operator' => $this->_login->user['eaId'],
                    'operate' => $this->data['type'] == self::TYPE_AGREE ?
                        ConvertProcessModel::OPERATE_STATUS_AGREE : ConvertProcessModel::OPERATE_STATUS_DEFUSE,
                    'operating_time' => MILLI_TIME,
                    'mark' => empty($this->data['mark']) ? '' : $this->data['mark'],
                ]);

                // 发送消息
                $smgSdk = new Message(Service::instance());
                $msgParam = [
                    'toUser' => $convertData['uid'],
                    'articles' => [
                        [
                            'title' => '管理员' . ($this->data['type'] == self::TYPE_AGREE ? self::TYPE_AGREE_CN : self::TYPE_DISAGREE_CN) . '了您的奖品兑换申请',
                            'description' => "奖品名称:" . $prizeData['name']
                                . "\n兑换编号:" . $convertData['number']
                                . "\n申请时间:" . rgmdate(NOW_TIME, 'Y-m-d H:i'),
                            'url' => frontUrl('/app/page/integral/apply-detail', ['ic_id' => $icId]),
                        ]
                    ],
                ];
                $smgSdk->sendNews($msgParam);

                // 如果拒绝
                if ($this->data['type'] == self::TYPE_DISAGREE) {
                    // 冲正 积分
                    $integralSdk = new Integral(Service::instance());
                    $integralSdk->integralExchange([
                        'memUid' => $convertData['uid'],
                        'integral' => $convertData['integral'],
                        'businessId' => $convertData['ic_id'],
                        'milOptType' => Integral::MANUAL_EXCHAGE_BACK_INTEGRAL,
                        'isAdmin' => Integral::IS_ADMIN_TRUE,
                        'prizeName' => $prizeData['name'],
                        'remark' => '【拒绝兑换】' . $prizeData['name'],
                        'milCreateMemUid' => $this->_login->user['eaId'],
                        'milCreateMemUsername' => $this->_login->user['eaRealname'],
                        'msgIdentifier' => APP_IDENTIFIER
                    ]);

                    // 冲正 商品库存
                    $prizeServ->changeReserve($convertData['ia_id'], 1);
                }

                $convertServ->commit();
                $convertProcessServ->commit();
            } catch (\Exception $e) {
                \Think\Log::record('操作失败:IC_ID' . $this->data['ic_id']);
                \Think\Log::record('操作失败:' . var_export($e, true));

                $convertServ->rollback();
                $convertProcessServ->rollback();

                E($e->getMessage());
                return false;
            }
        }

        // 出错的编号
        if (!empty($errorIcId)) {
            if (count($errorIcId) == $this->data['ic_id']) {
                E(L('_ERR_BATCH_OPERATE_ERROR_PLS_RETRY', ['operate' => $this->data['type'] == self::TYPE_AGREE ? self::TYPE_AGREE_CN : self::TYPE_DISAGREE_CN]));
                return false;
            }
            $errorIcIdData = $convertServ->list_by_conds(['ic_id' => $errorIcId]);
            $errorNumberArr = implode(',', array_column($errorIcIdData, 'number'));
            E(L('_ERR_BATCH_OPERATE_ERROR', ['ids' => $errorNumberArr]));
        }

        return true;
    }

    /**
     * 获取mark
     * @return bool
     */
    protected function getMark()
    {
        $this->field['mark'] = [
            'require' => $this->data['type'] == self::TYPE_AGREE ? false : true,
            'verify' => 'strval',
            'cn' => $this->data['type'] == self::TYPE_AGREE ? '同意说明' : '拒绝理由',
            'maxLength' => ConvertProcessModel::MARK_MAX_LENGTH,
        ];
        $this->getData();
        $this->verifyData();

        return true;
    }
}
