<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/12/20
 * Time: 15:45
 */
namespace Common\Service;

use Common\Model\SsafModel;

class SsafService extends AbstractService
{

    /**
     * 社保是否办理：是
     */
    const SS_IS_PAY_TURE = 1;

    /**
     * 社保是否办理：否
     */
    const SS_IS_PAY_FALSE = 0;

    /**
     * 默认缴费基数
     */
    const PAY_BASE = null;

    /**
     * 缴费基数位数上限
     */
    const PAY_BASE_DIGIT_MOST = 10;

    /**
     * 备忘字符长度上限
     */
    const REMARKS_LENGTH_MOST = 200;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_d = new SsafModel();
    }

    /**
     * 保存社保公积金信息时参数验证
     * @author liyifei
     * @param array $param
     *      + string uid 人员UID
     *      + string place 户籍地
     *      + int place_type 户籍类型
     *      + int ss_type 社保类型(1=无; 2=五险; 3=三险)
     *      + string ss_place 社保缴纳地
     *      + int ss_base 社保缴纳基数（单位“元”)
     *      + string ss_begin_month 社保起缴月份(空=未填写；非空=毫秒级时间戳)
     *      + string ss_handle_month 社保办理月份(空=未填写；非空=毫秒级时间戳)
     *      + int af_is_pay 公积金是否缴纳(0=未缴纳；1=缴纳)
     *      + int af_base 公积金基数(单位“元”)
     *      + string af_begin_month 公积金起缴月份(空=未填写；非空=毫秒级时间戳)
     *      + string af_handle_month 公积金办理月份(空=未填写；非空=毫秒级时间戳)
     *      + string remarks 备注
     * @return mixed
     */
    public function checkSaveParam($param)
    {
        // 户籍性质是否在范围内
        if (!in_array($param['place_type'], [SsafModel::PLACE_TYPE_CITY, SsafModel::PLACE_TYPE_COUNTRYSIDE])) {
            E('_ERR_PLACE_TYPE_UNDEFINED');
        }

        // 社保类型是否在范围内
        if (!in_array($param['ss_type'], [SsafModel::SS_TYPE_NONE, SsafModel::SS_TYPE_FIVE_RISK, SsafModel::SS_TYPE_THREE_RISK])) {
            E('_ERR_SS_TYPE_UNDEFINED');
        }

        // 公积金缴纳与否是否在范围内
        if (!in_array($param['af_is_pay'], [SsafModel::AF_IS_PAY_TRUE, SsafModel::AF_IS_PAY_FALSE])) {
            E('_ERR_AF_IS_PAY_UNDEFINED');
        }

        // 社保缴纳基数、公积金缴纳基数,最长10位
        $ssBase = explode('.', $param['ss_base']);
        if (strlen($ssBase[0]) > self::PAY_BASE_DIGIT_MOST) {
            E('_ERR_MONEY_TOO_MUCH');
        }
        $afBase = explode('.', $param['af_base']);
        if (strlen($afBase[0]) > self::PAY_BASE_DIGIT_MOST) {
            E('_ERR_MONEY_TOO_MUCH');
        }

        // 验证时间戳类型参数(是否为数字即可)
        if (strlen($param['ss_begin_month']) > 0 && !is_numeric($param['ss_begin_month'])) {
            E('_ERR_TIMESTAMP_FORMAT');
        }
        if (strlen($param['ss_handle_month']) > 0 && !is_numeric($param['ss_handle_month'])) {
            E('_ERR_TIMESTAMP_FORMAT');
        }
        if (strlen($param['af_begin_month']) > 0 && !is_numeric($param['af_begin_month'])) {
            E('_ERR_TIMESTAMP_FORMAT');
        }
        if (strlen($param['af_handle_month']) > 0 && !is_numeric($param['af_handle_month'])) {
            E('_ERR_TIMESTAMP_FORMAT');
        }

        // 备注参数,最长200位
        if (mb_strlen($param['remarks'], 'utf-8') > self::REMARKS_LENGTH_MOST) {
            E('_ERR_REMARKS_TOO_LONG');
        }

        return true;
    }

    /**
     * 根据条件查找公积金列表
     * @param $uids array 用户uid
     * @param $ss_is_pay int 是否办理社保
     * @param $af_is_pay int 是否缴纳公积金
     * @return array
     */
    public function listSsafByConds($uids, $ss_is_pay, $af_is_pay)
    {
        $conds = '';
        if (!empty($uids)) {
            $conds .= " AND `uid` in ('".implode("','", $uids)."')";
        }

        // 防止变量是string的1
        $ss_is_pay = $ss_is_pay !== '' ? intval($ss_is_pay) : '';
        $af_is_pay = $af_is_pay !== '' ? intval($af_is_pay) : '';

        if ($ss_is_pay !== SsafService::SS_IS_PAY_TURE && $af_is_pay !== SsafModel::AF_IS_PAY_TRUE) {
            // 表示需要的是未办理和未缴纳的，需要排除已缴纳或已办理的
            if ($ss_is_pay === SsafService::SS_IS_PAY_FALSE) {
                $conds .= " AND `ss_begin_month` != ''";
            }
            if ($af_is_pay === SsafModel::AF_IS_PAY_FALSE) {
                $conds .= " AND `af_begin_month` != ''";
            }
        } else {
            // 表示需要已缴纳的或已办理的，需要范围
            if ($ss_is_pay === SsafService::SS_IS_PAY_TURE) {
                $conds .= " AND `ss_begin_month` != ''";
            } elseif ($ss_is_pay === SsafService::SS_IS_PAY_FALSE) {
                $conds .= " AND `ss_begin_month` = ''";
            }

            // 已缴纳公积金
            if ($af_is_pay === SsafModel::AF_IS_PAY_TRUE) {
                $conds .= " AND `af_begin_month` != ''";
            } elseif ($af_is_pay === SsafModel::AF_IS_PAY_FALSE) {
                $conds .= " AND `af_begin_month` = ''";
            }
        }
        $list = $this->_d->listSsafByConds($conds);
        return $list;
    }
}
