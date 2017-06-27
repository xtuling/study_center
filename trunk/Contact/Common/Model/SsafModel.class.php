<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/12/20
 * Time: 15:44
 */
namespace Common\Model;

use Common\Service\SsafService;

class SsafModel extends AbstractModel
{

    /**
     * 户籍性质:城镇
     */
    const PLACE_TYPE_CITY = 1;

    /**
     * 户籍性质:农村
     */
    const PLACE_TYPE_COUNTRYSIDE = 2;

    /**
     * 社保类型：无
     */
    const SS_TYPE_NONE = 1;

    /**
     * 社保类型：五险
     */
    const SS_TYPE_FIVE_RISK = 2;

    /**
     * 社保类型：三险
     */
    const SS_TYPE_THREE_RISK = 3;

    /**
     * 公积金是否缴纳:是
     */
    const AF_IS_PAY_TRUE = 1;

    /**
     * 公积金是否缴纳:否
     */
    const AF_IS_PAY_FALSE = 0;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
    }

    /**
     * 根据条件查找公积金列表
     * @param $conds string 条件数组
     * @return array
     */
    public function listSsafByConds($conds)
    {
        $sql = "SELECT ssaf_id, uid, place, place_type, ss_type, ss_place,"
            . " ss_base, ss_begin_month, ss_handle_month, af_is_pay, af_base,"
            . " af_begin_month, af_handle_month, af_handle_month"
            . " FROM __TABLE__"
            . " WHERE `domain` = ? {$conds} AND status < ? ";
        $param = [
            QY_DOMAIN,
            self::ST_DELETE,
        ];

        return $this->_m->fetch_array($sql, $param);
    }
}
