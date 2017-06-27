<?php
/**
 * 试卷表
 * @author: houyingcai
 * @email:     594609175@qq.com
 * @date :  2017-05-19 18:06:08
 * @version $Id$
 */

namespace Common\Model;

use Common\Model\RightModel;

class PaperModel extends AbstractModel
{

    // 全公司
    const EXAM_COMPANY_ALL = 1;

    // 分类权限
    const CATEGORY = 1;
    // 试卷权限
    const PAPER = 0;

    // 试卷综合状态 ：初始化
    const STATUS_INIT = 0;
    // 试卷综合状态 ：草稿
    const STATUS_DRAFT = 1;
    // 试卷综合状态 ：未开始
    const STATUS_NOT_START = 2;
    // 试卷综合状态 ：进行中
    const STATUS_ING = 3;
    // 试卷综合状态 ：已结束
    const STATUS_END = 4;
    // 试卷综合状态 ：已终止
    const STATUS_STOP = 5;

    // 试卷数据状态：草稿
    const PAPER_DRAFT = 1;
    // 试卷数据状态：已发布
    const PAPER_PUBLISH = 2;
    // 试卷数据状态：提前终止
    const PAPER_STOP = 3;

    // 试卷类状态：测评试卷
    const EVALUATION_STATUS_TYPE = 1;
    // 试卷类型：模拟试卷
    const SIMULATION_STATUS_TYPE = 2;

    // 试卷类型数据库状态：测评试卷
    const EVALUATION_PAPER_TYPE = 0;
    // 试卷类型数据库状态：模拟试卷
    const SIMULATION_PAPER_TYPE = 1;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *  查询总数
     * @author: 蔡建华
     * @param $data array 查询条件
     * @return int|mixed
     */
    public function count_by_paper($data = array())
    {
        list($where, $params) = $this->get_where_paper($data);
        $sql = 'SELECT COUNT(*) FROM __TABLE__ WHERE ' . $where;

        return $this->_m->result($sql, $params);
    }

    /**
     * 查询列表
     * @author: 蔡建华
     * @param $data array 查询条件
     * @param null $page_option 分页参数
     * @param array $order_option 排序参数
     * @param string $fields 查询的字段
     * @return array|bool
     */
    public function list_by_paper($data, $page_option = null, $order_option = array(), $fields = '*')
    {
        list($where, $params) = $this->get_where_paper($data);
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
     * @author: 蔡建华
     * @param array $data 查询条件
     * @return array
     */
    public function get_where_paper($data = array())
    {
        // 组装查询语句
        $where = "status <? AND domain=?";
        // 操作状态和域名
        $params[] = PaperModel::ST_DELETE;
        $params[] = QY_DOMAIN;

        $where .= " and cate_status = ? ";
        $params[] = self::EC_OPEN_STATES;
        if ($data['ec_id'] > 0) {
            $where .= " and ec_id = ? ";
            $params[] = $data['ec_id'];
        }
        // 活动状态
        $where .= " and exam_status > ? ";
        $params[] = PaperModel::STATUS_DRAFT;
        $params[] = PaperModel::EXAM_COMPANY_ALL;
        // 权限判断
        $rightModel = new RightModel();
        $table = $rightModel->get_tname();
        $right = $data['right'];
        if (!empty($right)) {
            $where_right = " 0 ";
            $params[] = PaperModel::ST_DELETE;
            $params[] = QY_DOMAIN;
            $params[] = self::PAPER;
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
            // 角色
            if (!empty($right['roleIds'])) {
                $where_right .= "OR `role_id` IN (?) ";
                $params[] = $right['roleIds'];
            }
            $right_sql = " ep_id in( select distinct  epc_id from " . $table . " where  status <? AND domain= ? AND `er_type`=?  AND (" . $where_right . "))";
        }
        $where .= " and  (is_all =? OR $right_sql)";

        return array($where, $params);
    }

    /**
     * 拼装 试卷管理列表|考试统计列表 where语句
     * @author: 候英才
     * @param array $params 查询条件参数
     * @param int $type 查询列表类型：0=试卷管理列表，1=考试统计列表
     * @return string
     */
    public function get_search_where($params = array(), $type = 0)
    {
        // 组装查询语句
        if (!$type) {
            $where = ' status<' . self::ST_DELETE . " AND domain= '" . QY_DOMAIN . "' AND exam_status > 0 ";
        } else {
            $where = ' status<' . self::ST_DELETE . " AND domain= '" . QY_DOMAIN . "' AND exam_status > 1 AND begin_time < " . MILLI_TIME;
        }

        // 如果标题不为空
        if (!empty($params['ep_name'])) {

            $where .= " AND ep_name like '%" . trim($params['ep_name']) . "%' ";
        }

        // 考试时间范围
        if (!empty($params['begin_time']) && $params['begin_time'] != 'NaN' && $params['end_time'] != 'NaN') {

            $where .= ' AND ((end_time > ' . $params['begin_time'] . '&& begin_time < ' . $params['end_time'] . ') OR (begin_time<' . $params['end_time'] . '&& end_time = 0 ))';

            // $where .= ' AND updated >= ' .$params['begin_time'] . ' AND updated <= ' . $params['end_time'];
        }

        // 分类不为空
        if (!empty($params['ec_id'])) {

            $where .= ' AND ec_id=' . $params['ec_id'];
        }

        // 试卷使用类型
        if (!empty($params['paper_type'])) {

            if (self::EVALUATION_STATUS_TYPE == $params['paper_type']) {

                $where .= ' AND paper_type=' . self::EVALUATION_PAPER_TYPE;
            }

            if (self::SIMULATION_STATUS_TYPE == $params['paper_type']) {

                $where .= ' AND paper_type=' . self::SIMULATION_PAPER_TYPE;
            }
        }

        // 试卷类型
        if (!empty($params['ep_type'])) {

            $where .= ' AND ep_type=' . $params['ep_type'];
        }

        // 考试状态不为空
        if (!empty($params['ep_status'])) {

            switch ($params['ep_status']) {

                // 草稿
                case self::STATUS_DRAFT:
                    $where .= ' AND exam_status =' . self::PAPER_DRAFT;
                    break;
                // 未开始
                case self::STATUS_NOT_START:
                    $where .= ' AND begin_time >' . MILLI_TIME . ' AND exam_status =' . self::PAPER_PUBLISH;
                    break;
                // 已开始
                case self::STATUS_ING:
                    $where .= ' AND exam_status =' . self::PAPER_PUBLISH . ' AND begin_time<' . MILLI_TIME . ' AND (end_time>=' . MILLI_TIME . ' OR end_time=0)';
                    break;
                // 已结束
                case self::STATUS_END:
                    $where .= ' AND exam_status =' . self::PAPER_PUBLISH . ' AND end_time> 0 AND  end_time<' . MILLI_TIME;
                    break;
                // 已终止
                case self::STATUS_STOP:
                    $where .= ' AND exam_status =' . self::PAPER_STOP;
                    break;

                default:
            }
        }

        return $where;
    }

    /**
     * 统计考试统计列表总数|获取试卷管理列表总数
     * @author: 候英才
     * @param array $params 查询条件参数
     * @return int|mixed
     */
    public function count_search_where($params = array())
    {

        $where = $this->get_search_where($params, $params['search_type']);

        $sql = 'SELECT COUNT(*) FROM __TABLE__ WHERE ' . $where;

        return $this->_m->result($sql, array());
    }

    /**
     * 查询考试统计列表|试卷管理列表
     * @author: 候英才
     * @param array $params 查询条件参数
     * @param null $page_option 分页参数
     * @param array $order_option 排序参数
     * @param string $fields 查询的字段
     * @return array|bool
     */
    public function list_search_where($params = array(), $page_option = null, $order_option = array(), $fields = '*')
    {
        $where = $this->get_search_where($params, $params['search_type']);
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
     * 根据条件更新数据
     * @param array $conds 条件数组
     * @param array $data 数据数组
     * @return array|bool
     */
    public function update_by_paper($conds = array(), $data = array())
    {
        $params = array();
        // 更新时 SET 数据
        $sets = array();
        if (!$this->_parse_set($sets, $params, $data)) {
            return false;
        }

        // 更新条件
        $wheres = array();
        if (!$this->_parse_where($wheres, $params, $conds)) {
            return false;
        }

        // 企业标记
        $wheres[] = "`{$this->prefield}domain`=?";
        $params[] = QY_DOMAIN;
        // 状态条件
        $wheres[] = "`{$this->prefield}status`<?";
        $params[] = $this->get_st_delete();

        return $this->_m->execsql("UPDATE __TABLE__ SET " . implode(',', $sets) . " WHERE " . implode(' AND ', $wheres),
            $params);
    }
}
