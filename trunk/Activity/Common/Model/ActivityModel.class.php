<?php

namespace Common\Model;

use Common\Model\RightModel;

class ActivityModel extends AbstractModel
{
    // 活动数据状态：草稿
    const ACTIVITY_DRAFT = 0;
    // 活动数据状态：已发布
    const ACTIVITY_PUBLISH = 1;
    // 活动数据状态：提前终止
    const ACTIVITY_STOP = 2;
    // 推送消息
    const NOTICE_ON = 1;
    // 全公司
    const ACTIVITY_COMPANY_ALL = 1;


    // 活动综合状态 ：草稿
    const STATUS_DRAFT = 1;
    // 活动综合状态 ：未开始
    const STATUS_NOT_START = 2;
    // 活动综合状态 ：进行中
    const STATUS_ING = 3;
    // 活动综合状态 ：已结束
    const STATUS_END = 4;
    // 活动综合状态 ：已终止
    const STATUS_STOP = 5;
    //时间为空
    const TIME_EMPTY = 0;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 查询总数
     * @param string $where 条件sql，不包含where关键字
     * @return int|mixed
     */
    public function count_by_where($params = array())
    {

        $where = $this->get_where($params);

        $sql = 'SELECT COUNT(*) FROM __TABLE__ WHERE ' . $where;

        return $this->_m->result($sql, array());

    }


    public function get_where($params = array())
    {
        // 组装查询语句
        $where = ' status<' . ActivityModel::ST_DELETE . " AND domain= '" . QY_DOMAIN . "'";

        if (!empty($params['subject'])) {
            // 如果标题不为空
            $where .= " AND subject like '%" . trim($params['subject']) . "%' ";
        }

        // 活动时间范围
        if (!empty($params['begin_time']) && $params['begin_time'] != 'NaN' && $params['end_time'] != 'NaN') {
            $where .= ' AND ((end_time > ' . $params['begin_time'] . '&& begin_time < ' . $params['end_time'] . ')
            OR (begin_time<' . $params['end_time'] . '&& end_time = 0 ))';
        }
        if (!empty($params['last_begin_time'])) {
            $where .= ' AND last_time >' . $params['last_begin_time'];
        }

        if (!empty($params['last_end_time'])) {
            $where .= ' AND last_time <' . $params['last_end_time'];
        }

        if (!empty($params['activity_status'])) {

            switch ($params['activity_status']) {
                // 草稿
                case self::STATUS_DRAFT:
                    $where .= ' AND activity_status =' . ActivityModel::ACTIVITY_DRAFT;
                    break;
                // 未开始
                case self::STATUS_NOT_START:
                    $where .= ' AND activity_status =' . ActivityModel::ACTIVITY_PUBLISH . ' AND begin_time>' . MILLI_TIME;
                    break;
                // 进行中
                case self::STATUS_ING:
                    $where .= ' AND activity_status =' . ActivityModel::ACTIVITY_PUBLISH . ' AND begin_time<' . MILLI_TIME . ' AND (end_time>=' . MILLI_TIME . ' OR end_time=0)';
                    break;
                // 已结束
                case self::STATUS_END:
                    $where .= ' AND activity_status=' . ActivityModel::ACTIVITY_PUBLISH . ' AND end_time>' . self::TIME_EMPTY . ' AND  end_time<' . MILLI_TIME;
                    break;
                // 已终止
                case self::STATUS_STOP:
                    $where .= ' AND activity_status=' . ActivityModel::ACTIVITY_STOP;
                    break;

                default:
            }
        }
        return $where;
    }


    /**
     * 查询列表
     * @param $where  条件sql，不包含where关键字
     * @param null $page_option 分页参数
     * @param array $order_option 排序参数
     * @param string $fields 查询的字段
     * @return array|bool
     */
    public function list_by_where($params = array(), $page_option = null, $order_option = array(), $fields = '*')
    {
        $where = $this->get_where($params);
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

        $sql = "SELECT {$fields} FROM __TABLE__ WHERE " . $where . " {$orderby}{$limit}";

        // 读取记录
        return $this->_m->fetch_array($sql, array());

    }

    /**
     *  查询总数
     * @param $data array 查询条件
     * @return int|mixed
     */
    public function count_by_active($data = array())
    {
        list($where, $params) = $this->get_where_active($data);
        $sql = 'SELECT COUNT(*) FROM __TABLE__ WHERE ' . $where;
        return $this->_m->result($sql, $params);
    }

    /**
     * 查询列表
     * @param $data array 查询条件
     * @param null $page_option 分页参数
     * @param array $order_option 排序参数
     * @param string $fields 查询的字段
     * @return array|bool
     */
    public function list_by_active($data = '', $page_option = null, $order_option = array(), $fields = '*')
    {
        list($where, $params) = $this->get_where_active($data);
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
        $sql = "SELECT {$fields} FROM __TABLE__ WHERE " . $where . " {$orderby}{$limit} ";
        return $this->_m->fetch_array($sql, $params);
    }

    /**
     * 拼接Sql语句
     * @param array $data 查询条件
     * @return array
     */
    public function get_where_active($data = array())
    {
        // 组装查询语句
        $where = "status <? AND domain=?";

        // 操作状态和域名
        $params[] = ActivityModel::ST_DELETE;
        $params[] = QY_DOMAIN;

        // 活动状态
        $where .= " and activity_status > ? ";
        $params[] = ActivityModel::ACTIVITY_DRAFT;
        $params[] = ActivityModel::ACTIVITY_COMPANY_ALL;

        // 权限判断
        $rightModel = new RightModel();
        $table = $rightModel->get_tname();
        $right = $data['right'];

        if (!empty($right)) {

            $where_right = " 0 ";
            $params[] = ActivityModel::ST_DELETE;
            $params[] = QY_DOMAIN;
            if (!empty($right['memID'])) {

                $where_right .= " OR  uid =? ";
                $params[] = $right['memID'];
            }
            //部门
            if (!empty($right['dpIds'])) {

                $where_right .= " OR `dp_id` IN (?) ";
                $params[] = $right['dpIds'];
            }

            // 标签
            // if (!empty($right['tagIds'])) {
            //     $where_right .= "OR `tag_id`  IN (?) ";
            //     $params[] = $right['tagIds'];
            // }
            
            // 岗位
            if (!empty($right['jobIds'])) {

                $where_right .= "OR `job_id` IN (?) ";
                $params[] = $right['jobIds'];
            }

            // 角色
            if (!empty($right['roleIds'])) {
                
                $where_right .= "OR `role_id` IN (?) ";
                $params[] = $right['roleIds'];
            }

            $right_sql = " ac_id in( select distinct  ac_id from " . $table . " where  status <? AND domain= ? AND (" . $where_right . "))";
        }

        if ($right_sql) {

            $where .= " and (is_all =? OR $right_sql)";
        } else {

            $where .= " and (is_all =?)";
        }
        
        return array($where, $params);
    }
    
}
