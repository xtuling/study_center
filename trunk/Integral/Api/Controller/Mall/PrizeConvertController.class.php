<?php
/**
 * Created by IntelliJ IDEA.
 * 奖品兑换
 * User: zhoutao
 * Date: 2016/12/07
 * Time: 上午14:27
 */

namespace Api\Controller\Mall;

use Common\Model\ConvertModel;
use Common\Model\PrizeModel;
use Common\Service\ConvertProcessService;
use Common\Service\ConvertService;
use Common\Service\PrizeService;
use VcySDK\Integral;
use VcySDK\Message;
use VcySDK\Service;

class PrizeConvertController extends AbstractController
{
    /** 单个兑换库存操作数 */
    const SINGLE_EXC_NUMBE = -1;
    /** @var array 奖品数据 */
    protected $prizeData = [];

    public function before_action($action = '')
    {
        // 开启自动获取
        $this->autoGetData = true;
        $this->field = [
            'ia_id' => [
                'require' => true,
                'verify' => 'intval',
                'cn' => '奖品ID',
            ],
            'applicant_phone' => [
                'require' => true,
                'verify' => 'intval',
                'cn' => '手机号',
                'regexp' => '/^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/'
            ],
            'applicant_email' => [
                'require' => true,
                'verify' => 'strval',
                'cn' => '邮箱',
                'regexp' => '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/'
            ],
            'applicant_mark' => [
                'require' => false,
                'verify' => 'strval',
                'cn' => '备注',
                'maxLength' => 60
            ],
        ];

        return parent::before_action($action);
    }

    public function Index()
    {
        // 业务逻辑验证
        $this->verifyBusinessLogic();

        $prizeServ = new PrizeService();
        $convertServ = new ConvertService();
        $convertProcessServ = new ConvertProcessService();
        $integralSdk = new Integral(Service::instance());

        // 兑换编号
        $number = $this->makeNumber();
        // 申请表ID
        $ic_id = 0;

        // 开启事务
        $prizeServ->start_trans();
        $convertServ->start_trans();
        $convertProcessServ->start_trans();

        try {
            // 更改库存
            $changeReserve = $prizeServ->changeReserve($this->data['ia_id'], self::SINGLE_EXC_NUMBE);
            // 没有库存了
            if ($changeReserve === 0) {
                E('_ERR_PRIZE_CONVERT_HAVENT_RESERVE');
                return false;
            }

            // 写入申请表
            $ic_id = $convertServ->insert([
                'uid' => $this->uid,
                'ia_id' => $this->data['ia_id'],
                'number' => $number,
                'integral' => $this->prizeData['integral'],
                'applicant_phone' => $this->data['applicant_phone'],
                'applicant_email' => $this->data['applicant_email'],
                'applicant_mark' => empty($this->data['applicant_mark']) ? '' : $this->data['applicant_mark'],
            ]);

            // 发送消息
            $smgSdk = new Message(Service::instance());
            $msgParam = [
                'toUser' => $this->uid,
                'articles' => [
                    [
                        'title' => '您已成功发起奖品兑换申请，请等待后台管理员审核',
                        'description' => "奖品名称:" . $this->prizeData['name']
                            . "\n兑换编号:" . $number
                            . "\n申请时间:" . rgmdate(NOW_TIME, 'Y-m-d H:i'),
                        'url' => frontUrl('/app/page/integral/apply-detail', ['ic_id' => $ic_id]),
                    ]
                ],
            ];
            $smgSdk->sendNews($msgParam);

            // 积分修改
            $ucOperate = $integralSdk->integralExchange([
                'memUid' => $this->uid,
                'integral' => $this->prizeData['integral'],
                'milOptType' => Integral::MANUAL_EXCHAGE_INTEGRAL,
                'businessId' => $ic_id,
                'prizeName' => $this->prizeData['name'],
                'remark' => '【申请兑换】' . $this->prizeData['name'],
                'milCreateMemUid' => $this->uid,
                'milCreateMemUsername' => $this->_login->user['memUsername'],
                'msgIdentifier' => APP_IDENTIFIER
            ]);

            // 更新申请表数据
            $convertServ->update($ic_id, [
                'ucintegral_id' => $ucOperate['milId'],
                // 在末尾加上主键ID 为了唯一
                'number' => $number . settype($ic_id, 'string')
            ]);

            // 写入申请进度表
            $convertProcessServ->insert([
                'ic_id' => $ic_id,
                'uid' => $this->uid,
                'operating_time' => MILLI_TIME,
                // 扣减积分
                'integral' => $this->prizeData['integral'] * -1,
                'mark' => empty($this->data['applicant_mark']) ? '' : $this->data['applicant_mark'],
            ]);

            // 提交事务
            $prizeServ->commit();
            $convertServ->commit();
            $convertProcessServ->commit();

        } catch (\Exception $e) {
            \Think\Log::record(
                '兑换奖品失败!' . var_export($e, true)
                . '提交信息:' . var_export($this->data, true)
                . '奖品信息:' . var_export($this->prizeData, true));

            // 数据库回滚
            $prizeServ->rollback();
            $convertServ->rollback();
            $convertProcessServ->rollback();

            // 积分冲正
            if (isset($ucOperate['milId'])) {
                $integralSdk->integralExchange([
                    'memUid' => $this->uid,
                    'integral' => $this->prizeData['integral'],
                    'milOptType' => Integral::MANUAL_EXCHAGE_BACK_INTEGRAL,
                    'isAdmin' => Integral::IS_ADMIN_FALSE,
                    'businessId' => $ic_id,
                    'prizeName' => $this->prizeData['name'],
                    'remark' => '【兑换出错】' . $this->prizeData['name'],
                    'milCreateMemUid' => $this->uid,
                    'milCreateMemUsername' => $this->_login->user['memUsername'],
                    'msgIdentifier' => APP_IDENTIFIER
                ]);
            }

            E($e->getMessage());
            return false;
        }

        // 返回成功后的申请ID
        $this->_result = [
            'ic_id' => $ic_id
        ];
        return true;
    }

    /**
     * 业务逻辑 判断
     * @return bool
     */
    protected function verifyBusinessLogic()
    {
        // 获取奖品数据
        $prizeServ = new PrizeService();
        $this->prizeData = $prizeServ->get($this->data['ia_id']);
        // 已删除
        if (empty($this->prizeData)) {
            E('_ERR_PRIZE_CONVERT_IS_DELETED');
            return false;
        }
        // 已下架
        if ($this->prizeData['on_sale'] == PrizeModel::OFF_SALE) {
            E('_ERR_PRIZE_CONVERT_IS_OFFSALE');
            return false;
        }
        // 没库存
        if ($this->prizeData['reserve'] <= 0) {
            E('_ERR_PRIZE_CONVERT_HAVENT_RESERVE');
            return false;
        }
        // 兑换范围
        if (!$this->verifyArea($this->prizeData)) {
            E('_ERR_PRIZE_CONVERT_NOT_IN_AREA');
            return false;
        };

        // 如果有次数限制
        if ($this->prizeData['times'] != PrizeModel::MEAN_TIMES_NO_LIMIT) {
            // 获取已经兑换的数据
            $convertServ = new ConvertService();
            $excTotal = $convertServ->count_by_conds([
                'ia_id' => $this->data['ia_id'],
                'uid' => $this->uid,
                'convert_status' => [
                    ConvertModel::CONVERT_STATUS_ING,
                    ConvertModel::CONVERT_STATUS_AGREE,
                ]
            ]);
            // 超出次数限制
            if ($excTotal >= $this->prizeData['times']) {
                E('_ERR_OVER_PER_EXC_TIMES');
                return false;
            }
        }

        // 判断积分
        $integralSdk = new Integral(Service::instance());
        $userIntegral = $integralSdk->detail(['memUid' => $this->uid]);
        if (empty($userIntegral['available']) || $userIntegral['available'] < $this->prizeData['integral']) {
            E('_ERR_PRIZE_CONVERT_INTEGRAL_NOT_ENOUGH');
            return false;
        }

        return true;
    }

    /**
     * 生成兑换编号
     * @return mixed
     */
    protected function makeNumber()
    {
        $number = rgmdate(NOW_TIME, 'YmdHis') . $this->randNumber();

        return $number;
    }

    /**
     * 随机生成数字串
     * @param int $length 长度
     * @return int|string
     */
    protected function randNumber($length = 5)
    {
        $numberStr = '';
        for ($i = 0; $i < $length; $i ++) {
            $numberStr += rand(0, 9);
        }

        return $numberStr;
    }
}
