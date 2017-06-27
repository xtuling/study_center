<?php
/**
 * ConvertModel.class.php
 * 奖品申请表 Model
 * @author: zhoutao
 * @version: $Id$
 * @copyright: vchangyi.com
 */

namespace Common\Model;

class ConvertModel extends AbstractModel
{
    /** 奖品申请状态:待处理 */
    const CONVERT_STATUS_ING = 1;
    /** 奖品申请状态:已同意 */
    const CONVERT_STATUS_AGREE = 2;
    /** 奖品申请状态:已拒绝 */
    const CONVERT_STATUS_DEFUSE = 3;
    /** 奖品申请状态:已取消 */
    const CONVERT_STATUS_CANCEL = 4;
    /** @var array 奖品申请状态 */
    protected $convertStatus = [
        self::CONVERT_STATUS_ING => '待处理',
        self::CONVERT_STATUS_AGREE => '已同意',
        self::CONVERT_STATUS_DEFUSE => '已拒绝',
        self::CONVERT_STATUS_CANCEL => '已取消',
    ];

    // 构造方法
    public function __construct()
    {

        parent::__construct();
    }


    /**
     * 微信端查询符合条件的奖品兑换记录总数
     * @param $conds
     * @return array|bool
     */
    public function countWxPrizeConvert($conds)
    {

        if (empty($conds['memUid'])) {
            return false;
        }

        $where = ' WHERE oic.`domain`=?';
        $params = array(
            QY_DOMAIN,
        );

        $where .= ' AND oic.`uid` = ?';
        $params[] = $conds['memUid'];

        $sql = 'SELECT count(*) FROM oa_integral_convert oic
                LEFT JOIN oa_integral_prize oip ON oic.ia_id=oip.ia_id ' . $where;

        return $this->_m->result($sql, $params);

    }

    /**
     * 管理平台查询符合条件的奖品兑换记录总数
     * @param $conds
     * @return array|bool
     */
    public function countPrizeConvert($conds)
    {

        $where = ' WHERE oic.`domain`=?';
        $params = array(
            QY_DOMAIN,
        );

        // 奖品ID
        if (!empty($conds['ia_id'])) {
            $where .= ' AND oic.`ia_id` = ?';
            $params[] = $conds['ia_id'];
        }

        // 兑换状态
        if (!empty($conds['convert_status'])) {
            $where .= ' AND oic.`convert_status` = ?';
            $params[] = $conds['convert_status'];
        }

        // 奖品名称
        if (!empty($conds['name'])) {
            $where .= ' AND oip.`name` LIKE ?';
            $params[] = '%' . $conds['name'] . '%';
        }

        // 手机
        if (!empty($conds['applicant_phone'])) {
            $where .= ' AND oic.`applicant_phone` LIKE ?';
            $params[] = '%' . $conds['applicant_phone'] . '%';
        }

        // 兑换编号
        if (!empty($conds['number'])) {
            $where .= ' AND oic.`number` LIKE ?';
            $params[] = '%' . $conds['number'] . '%';
        }

        // 申请开始时间
        if (!empty($conds['startTime'])) {
            $where .= ' AND oic.`created` >= ?';
            $params[] = $conds['startTime'];
        }

        // 申请结束时间
        if (!empty($conds['endTime'])) {
            $where .= ' AND oic.`created` <= ?';
            $params[] = $conds['endTime'];
        }

        // 如果按部门或者用户查询
        if(!empty($conds['uids'])){
            $where .= ' AND oic.`uid` in (?)';
            $params[] = $conds['uids'];
        }

        $sql = 'SELECT count(*) FROM oa_integral_convert oic
                LEFT JOIN oa_integral_prize oip ON oic.ia_id=oip.ia_id ' . $where;

        return $this->_m->result($sql, $params);

    }

    /**
     * 管理平台查询奖品兑换分页列表
     * @param $conds
     * @param null $pageOption
     * @param array $orderOption
     * @return array|bool
     */
    public function getPrizeConvertPageList($conds, $pageOption = null, $orderOption = array())
    {

        $where = ' WHERE oic.`domain`=?';
        $params = array(
            QY_DOMAIN,
        );

        // 奖品ID
        if (!empty($conds['ia_id'])) {
            $where .= ' AND oic.`ia_id` = ?';
            $params[] = $conds['ia_id'];
        }

        // 兑换状态
        if (!empty($conds['convert_status'])) {
            $where .= ' AND oic.`convert_status` = ?';
            $params[] = $conds['convert_status'];
        }

        // 奖品名称
        if (!empty($conds['name'])) {
            $where .= ' AND oip.`name` LIKE ?';
            $params[] = '%' . $conds['name'] . '%';
        }

        // 手机
        if (!empty($conds['applicant_phone'])) {
            $where .= ' AND oic.`applicant_phone` LIKE ?';
            $params[] = '%' . $conds['applicant_phone'] . '%';
        }

        // 兑换编号
        if (!empty($conds['number'])) {
            $where .= ' AND oic.`number` LIKE ?';
            $params[] = '%' . $conds['number'] . '%';
        }

        // 申请开始时间
       if (!empty($conds['startTime'])) {
            $where .= ' AND oic.`created` >= ?';
            $params[] = $conds['startTime'];
        }

        // 申请结束时间
        if (!empty($conds['endTime'])) {
            $where .= ' AND oic.`created` <= ?';
            $params[] = $conds['endTime'];
        }

        // 如果按部门或者用户查询
        if(!empty($conds['uids'])){
            $where .= ' AND oic.`uid` in (?)';
            $params[] = $conds['uids'];
        }

        // 排序
        $orderby = ' ORDER BY oic.`created` DESC';

        // 分页参数
        $limit = '';
        if (!$this->_limit($limit, $pageOption)) {
            return false;
        }

        $sql = "SELECT oic.`ic_id`, oic.`created` AS apply_time, oic.`updated` AS operate_time, oic.`convert_status`, oip.`name`, oic.`number`, oic.`operator`, oic.`integral`, oic.`uid`, oic.`applicant_phone`
                FROM oa_integral_convert oic
                LEFT JOIN oa_integral_prize oip ON oic.ia_id=oip.ia_id "  . $where . $orderby . $limit;

        return $this->_m->fetch_array($sql, $params);

    }


    /**
     * 微信端查询奖品兑换分页列表
     * @param $conds
     * @param null $pageOption
     * @param array $orderOption
     * @return array|bool
     */
    public function getWxPrizeConvertPageList($conds, $pageOption = null, $orderOption = array())
    {

        if (empty($conds['memUid'])) {
            return false;
        }

        $where = ' WHERE oic.`domain`=?';
        $params = array(
            QY_DOMAIN,
        );

        $where .= ' AND oic.`uid` = ?';
        $params[] = $conds['memUid'];

        // 排序
        $orderby = 'ORDER BY oic.`created` DESC';

        // 分页参数
        $limit = '';
        if (!$this->_limit($limit, $pageOption)) {
            return false;
        }

        $sql = "SELECT oic.`ic_id`, oic.`created` AS apply_time, oic.`convert_status`, oip.`name`, oip.`picture`
                FROM oa_integral_convert oic
                LEFT JOIN oa_integral_prize oip ON oic.ia_id=oip.ia_id "  . $where . $orderby . $limit;

        return $this->_m->fetch_array($sql, $params);

    }

    public function getWxPrizeConvertDetailByParams($conds) {

        $where = ' WHERE oic.`status`<? AND oic.`domain`=?';
        $params = array(
            self::ST_DELETE,
            QY_DOMAIN,
        );

        $where .= ' AND oic.`uid` = ?';
        $params[] = $conds['memUid'];

        if(!empty($conds['ic_id'])) {
            $where .= "AND oic.`ic_id` = ? ";
            $params[] = $conds['ic_id'];
        }

        if(!empty($conds['ucintegral_id'])) {
            $where .= "AND oic.`ucintegral_id` = ? ";
            $params[] = $conds['ucintegral_id'];
        }

        $sql = "SELECT
                    oic.`ic_id`,
                    oip.`status` AS `prize_status`,
                    oic.`ia_id`,
                    oic.`operator`,
                    oic.`ucintegral_id`,
                    oip.`picture`,
                    oic.`convert_status`,
                    oip.`name`,
                    oic.`integral`,
                    oic.`number`,
                    oic.`uid`,
                    oic.`applicant_phone`,
                    oic.`applicant_email`,
                    oic.`applicant_mark`
                FROM
                    oa_integral_convert oic
                LEFT JOIN oa_integral_prize oip ON oic.`ia_id` = oip.`ia_id` " . $where;

        return $this->_m->fetch_row($sql, $params);
    }
}
