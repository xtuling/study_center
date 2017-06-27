<?php
/**
 * PrizeModel.class.php
 * 奖品设置表 Model
 * @author: zhoutao
 * @version: $Id$
 * @copyright: vchangyi.com
 */

namespace Common\Model;

class PrizeModel extends AbstractModel
{
    /** 最多图片张数 */
    const MAX_PICTURE_NUMBER = 5;
    /** 每人限定兑换次数数据含义:不限制 */
    const MEAN_TIMES_NO_LIMIT = -1;
    /** 最长名称文字长度 */
    const MAX_NAME_COUNT = 50;
    /** 最长所需积分 */
    const MAX_INTEGRAL_LEN = 5;
    /** 奖品状态: 已上架 */
    const ON_SALE = 1;
    /** 奖品状态: 已下架 */
    const OFF_SALE = 2;
    /** 兑换范围是否全公司: 是 */
    const IS_ALL = 1;
    /** 兑换范围是否全公司: 否 */
    const NOT_IS_ALL = 2;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
    }

    /**
     * Apicp 奖品列表总数
     * @param $conds
     * @return array
     */
    public function countPrizeList($conds)
    {
        list($where, $params) = $this->getListWhere($conds);
        $sql = 'SELECT COUNT(*) FROM __TABLE__' . $where;

        return $this->_m->result($sql, $params);
    }

    /**
     * Apicp 查询奖品列表
     *
     * @param array $conds
     * @param array $pageOption
     * @param array $orderOption
     *
     * @return int 数量
     */
    public function getPrizeList($conds, $pageOption = null, $orderOption = array())
    {
        list($where, $params) = $this->getListWhere($conds);

        // 排序
        $orderby = '';
        if (!$this->_order_by($orderby, $orderOption)) {
            return false;
        }
        // 分页参数
        $limit = '';
        if (!$this->_limit($limit, $pageOption)) {
            return false;
        }

        // 查询已经兑换的次数
        $secondSql = 'SELECT COUNT(*) FROM oa_integral_convert AS c WHERE c.`ia_id`= p.`ia_id` AND `convert_status` = ' . ConvertModel::CONVERT_STATUS_AGREE;
        // 查询列表
        $sql = "SELECT *, ({$secondSql}) AS exchanged_times FROM __TABLE__ AS p" . $where . $orderby . $limit;

        return $this->_m->fetch_array($sql, $params);
    }

    /**
     * 查询列表的where语句
     *
     * @return array [where语句, 参数数组]
     */
    protected function getListWhere($conds)
    {
        $where = ' WHERE `status`<? AND `domain`=?';
        $params = array(
            self::ST_DELETE,
            QY_DOMAIN,
        );

        // 名称
        if (!empty($conds['name'])) {
            $where .= ' AND `name` LIKE ?';
            $params[] = '%' . $conds['name'] . '%';
        }
        // 上下架
        if (!empty($conds['on_sale'])) {
            $where .= ' AND `on_sale` = ?';
            $params[] = $conds['on_sale'];
        }

        return array($where, $params);
    }

    /**
     * 微信端查询奖品分页列表
     * @param $conds
     * @param null $pageOption
     * @param array $orderOption
     * @return array|bool
     */
    public function getWxPrizePageList($conds, $pageOption = null, $orderOption = array())
    {

        list($where, $params) = $this->getBaseWhere();

        $where .= ' AND on_sale = ' . self::ON_SALE;

        $where .= ' AND ( is_all = ' . self::IS_ALL;

        if (!empty($conds['rangeMem'])) {
            $where .= ' OR range_mem REGEXP (?)';
            $params[] = $conds['rangeMem'];
        }

        if (!empty($conds['rangeDep'])) {
            $where .= ' OR range_dep REGEXP(?) ';
            $params[] = $conds['rangeDep'];
        }

        $where .= ')';

        // 排序
        $orderby = 'ORDER BY `sequence`, `updated` DESC';

        // 分页参数
        $limit = '';
        if (!$this->_limit($limit, $pageOption)) {
            return false;
        }

        $sql = "SELECT * FROM __TABLE__ "  . $where . $orderby . $limit;

        return $this->_m->fetch_array($sql, $params);

    }


    /**
     * 微信端 查询符合条件的奖品记录总数
     * @param $conds
     * @return array
     */
    public function countWxPrize($conds)
    {
        list($where, $params) = $this->getBaseWhere();

        $where .= ' AND on_sale = ' . self::ON_SALE;

        $where .= ' AND ( is_all = ' . self::IS_ALL;

        if (!empty($conds['rangeMem'])) {
            $where .= ' OR range_mem REGEXP (?)';
            $params[] = $conds['rangeMem'];
        }

        if (!empty($conds['rangeDep'])) {
            $where .= ' OR range_dep REGEXP(?) ';
            $params[] = $conds['rangeDep'];
        }

        $where .= ')';

        $sql = 'SELECT COUNT(*) FROM __TABLE__' . $where;

        return $this->_m->result($sql, $params);
    }

    /**
     * 操作库存
     * @param int $id 主键ID
     * @param int $number 操作库存数
     * @return mixed
     */
    public function changeReserve($id, $number)
    {
        list($where, $params) = $this->getBaseWhere();

        // 更新库存
        array_unshift($params, $number);
        // 主键查询
        $where .= ' AND ia_id=?';
        $params[] = $id;
        // 防止过量减库存
        if ($number < 0) {
            $where .= ' AND reserve >= ?';
            $params[] = abs($number);
        }

        $sql = "UPDATE __TABLE__ SET `reserve` = `reserve` + ?" . $where;

        return $this->_m->execsql($sql, $params);
    }

    /**
     * 查询奖品 无视逻辑删除
     * @param int $iaId 奖品ID
     * @return mixed
     */
    public function getWithOutDeleted($iaId)
    {
        $sql = "SELECT * FROM __TABLE__ WHERE `" . $this->_m->getPk() . "`=? AND `{$this->prefield}domain`=?";

        return $this->_m->fetch_row($sql, [$iaId, QY_DOMAIN]);
    }
}
