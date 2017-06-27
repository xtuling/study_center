<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/20
 * Time: 11:21
 */
namespace Common\Model;

class ContractModel extends AbstractModel
{
    /**
     * 合同类型:劳动合同
     */
    const CONTRACT_TYPE_TYPE = 1;

    /**
     * 合同签订情况:未签订
     */
    const CONTRACT_SIGNING_TYPE_UNDONE = 1;

    /**
     * 合同签订情况:已签订
     */
    const CONTRACT_SIGNING_TYPE_DONE = 2;

    /**
     * 合同签订情况:已过期
     */
    const CONTRACT_SIGNING_TYPE_OVERDUE = 3;

    /**
     * 合同年限:1年
     */
    const CONTRACT_TYPE_YEARS = 1;

    /**
     * 试用期:无
     */
    const CONTRACT_TYPE_PROBATION = 0;

    // 构造方法
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 按合同签订状况取合同列表
     * @param int $signingType 合同签订状态，2 已签订未过期，3 已签订已过期
     * @param int $checkTime  13位时间戳，是否过期判定时间
     * @return array 合同信息列表
     */
    public function getListBySigningType($signingType, $checkTime)
    {
        $where = null;
        switch ($signingType) {
            // 合同签订情况选择签订,合同结束时间大于对比时间 OR 无固定合同+未设置合同结束时间
            case self::CONTRACT_SIGNING_TYPE_DONE:
                $where = " `end_time` > {$checkTime}  OR (`years` = 0 AND LENGTH(`end_time`) = 0 )";
                break;
            // 合同签订情况选择过期，非无固定合同+合同结束时间小于对比时间 OR 无固定合同+合同结束时间不为空
            case self::CONTRACT_SIGNING_TYPE_OVERDUE:
                $where = " (`end_time` <= {$checkTime} AND `years` != 0) OR ".
                    "(`years` = 0 AND LENGTH(`end_time`) > 0 AND `end_time` <= {$checkTime})";
                break;
            // 默认取出所有合同信息
            default:
                $where = " 1";
                break;
        }
        $sql = "SELECT * FROM __TABLE__ WHERE " . $where;

        return $this->_m->fetch_array($sql);
    }
}
