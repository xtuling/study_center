<?php
/**
 * 考试-激励表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 18:04:33
 * @version $Id$
 */

namespace Common\Model;

class MedalModel extends AbstractModel
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 查询列表
     * @param $data array 查询条件
     * @param null $page_option 分页参数
     * @param array $order_option 排序参数
     * @param string $fields 查询的字段
     * @return array|bool
     */
    public function fetch_all_medal($data, $fields = '*')
    {
        list($where, $params) = $this->get_where($data);
        // 排序
        $sql = "SELECT {$fields} FROM __TABLE__ WHERE " . $where;

        return $this->_m->fetch_array($sql, $params);
    }

    /**
     * 拼接Sql语句
     * @param array $data 查询条件
     * @return array
     */
    public function get_where($data = array())
    {
        // 组装查询语句
        $where = "status <? AND domain=?";
        // 操作状态和域名
        $params[] = self::ST_DELETE;
        $params[] = QY_DOMAIN;
        $params[] = 1;
        // 权限判断
        $rightModel = new RightModel();
        $table = $rightModel->get_tname();
        $right = $data['right'];
        if (!empty($right)) {
            $where_right = " 0 ";
            $params[] = PaperModel::ST_DELETE;
            $params[] = QY_DOMAIN;
            $params[] = $data['er_type'];
            if (!empty($right['memID'])) {
                $where_right .= " OR  uid =? ";
                $params[] = $right['memID'];
            }
            //部门
            if (!empty($right['dpIds'])) {
                $where_right .= " OR `cd_id` IN (?) ";
                $params[] = $right['dpIds'];
            }
            // 标签
            if (!empty($right['tagIds'])) {
                $where_right .= "OR `tag_id`  IN (?) ";
                $params[] = $right['tagIds'];
            }
            // 岗位
            if (!empty($right['jobIds'])) {
                $where_right .= "OR `job_id` IN (?) ";
                $params[] = $right['jobIds'];
            }
            $right_sql = " em_id in( select distinct  epc_id from " . $table . " where  status <? AND domain= ? AND `er_type`=?  AND (" . $where_right . "))";
        }
        $where .= " and  (is_all =? OR $right_sql)";

        return array($where, $params);
    }
}