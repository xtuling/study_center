<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 16/9/29
 * Time: 20:44
 */

namespace Common\Model;

class InviteUserModel extends AbstractModel
{

    // 用户信息(手机号|邮箱|微信号)已存在
    const MEM_INFO_EXIST = 1;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
    }

    /**
     * 根据帐户数据（手机号、微信号、邮箱）获取邀请数据
     * @author zhonglei
     * @param array $data
     * @param bool  $ignoreRefuse 忽略拒绝信息
     * @return array
     */
    public function getByAccount($data, $ignoreRefuse = false)
    {

        $conds = [];

        foreach ($data as $k => $v) {
            $conds[] = "`{$k}` = '{$v}'";
        }
        $refuseWhere = '';
        if ($ignoreRefuse) {
            $refuseWhere = " AND `check_status` in (1, 2)";
        }

        $where = implode(' or ', $conds);
        $sql = "SELECT * FROM __TABLE__ WHERE ({$where}){$refuseWhere} AND `domain` = ? AND status < ? order by invite_id desc";

        $param = [
            QY_DOMAIN,
            self::ST_DELETE,
        ];

        return $this->_m->fetch_row($sql, $param);
    }

    /**
     * 根据条件读取数据数组
     *
     * @param array     $condition    条件数组
     * @param int|array $page_option  分页参数
     * @param array     $order_option 排序
     * @param string    $fields       读取字段
     *
     * @return array|bool
     */
    public function listByRight($condition, $page_option = null, $order_option = array(), $fields = '*')
    {

        $params = array();
        // 条件
        $wheres = array();
        if (!$this->_parse_where($wheres, $params, $condition)) {
            return false;
        }

        // 企业标记
        $wheres[] = "`u`.`{$this->prefield}domain`=?";
        $params[] = QY_DOMAIN;
        // 状态条件
        $wheres[] = "`u`.`{$this->prefield}status`<?";
        $params[] = $this->get_st_delete();

        // 排序
        $orderby = '';
        if (!$this->_order_by($orderby, $order_option)) {
            return false;
        }

        // 分页参数
        $limit = '';
        if (!$this->_limit($limit, $page_option)) {
            return false;
        }

        // 读取记录
        $list = $this->_m->fetch_array("SELECT {$fields} FROM {$this->_tb_rightTable} AS r 
                LEFT JOIN __TABLE__ AS u ON r.invite_id=u.invite_id 
                WHERE " . implode(' AND ', $wheres) . "{$orderby}{$limit}", $params);
        $count = $this->_m->fetch_array("SELECT COUNT(*) FROM {$this->_tb_rightTable} AS r 
                LEFT JOIN __TABLE__ AS u ON r.invite_id=u.invite_id 
                WHERE " . implode(' AND ', $wheres) . "{$orderby}{$limit}", $params);

        return array($list, $count);
    }


    /**
     * 根据条件删除邀请记录
     * @param $condition
     * @return bool
     */
    public function delInviteUserRecord($condition) {

        if (empty($condition['mobile'])) {
            return false;
        }

        $whereOr = ['mobile in (' . $condition['mobile'] . ')'];

        if (!empty($condition['email'])) {
            $whereOr[] = 'email in (' . $condition['email'] . ')';
        }

        if (!empty($condition['weixin'])) {
            $whereOr[] = 'weixin in (' . $condition['weixin'] . ')';
        }

        $whereOr = implode(" or ", $whereOr);

        $sql = "UPDATE __TABLE__ SET `status` = ? WHERE `domain` = ? and `status` < ? AND ({$whereOr})";

        return $this->_m->execsql($sql, [
            self::ST_DELETE,
            QY_DOMAIN,
            self::ST_DELETE,
        ]);
    }

}
