<?php
/**
 * 试卷-答卷表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 18:04:10
 * @version $Id$
 */

namespace Common\Model;

class AnswerModel extends AbstractModel
{

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *  考试排序取最大考试记录数据
     * @autor 蔡建华
     * @param array $data 试卷条件
     * array("paper_id"=>1)
     * @param string $fields 返回字段
     * @return array
     */
    function answer_all($data = array(), $fields = '*')
    {
        $where = ' status < ? AND domain = ? ';
        $params[] = AnswerModel::ST_DELETE;
        $params[] = QY_DOMAIN;
        if ($data['ep_id']) {
            $where .= ' AND ep_id = ? ';
            $params[] = $data['ep_id'];
        }
        $where .= ' AND my_time >? ';
        $params[] = 0;
        $sql = "select {$fields} from (select {$fields} from  __TABLE__ where {$where} order by my_score desc,created asc) as a group by a.uid order by my_score desc ";

        return $this->_m->fetch_array($sql, $params);
    }

    /** 查询一条数据
     * @autor 蔡建华
     * @param array $data 查询条件
     * @param string $fields 返还字段
     * @return array
     */
    public function  fetchOne($data = array(), $fields = '*')
    {
        $where = ' status < ? AND domain = ? ';
        $params[] = AnswerModel::ST_DELETE;
        $params[] = QY_DOMAIN;
        if ($data) {
            foreach ($data as $key => $val) {
                $where .= $key;

                $params[] = $val;
            }
        }
        $sql = "select {$fields} from  __TABLE__ where {$where}";

        return $this->_m->result($sql, $params);
    }

    /**
     * 获取考试参与人员列表
     * 
     * @author  houyingcai
     * @param array $conds 查询条件参数列表
     * @param array $page_option 分页参数
     * @param array $order_option 排序参数
     * @param string $fields 返回字段
     * 
     * @return array|bool
     */
    function get_mock_answer_list($conds, $page_option = null, $order_option = array(), $fields = '*')
    {

        $params = array();
        // 条件
        $wheres = array();
        if (!$this->_parse_where($wheres, $params, $conds)) {
            return false;
        }
        // 企业标记
        $wheres[] = '`domain`=?';
        $params[] = QY_DOMAIN;
        // 状态条件
        $wheres[] = '`status`<?';
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

        $sql = "SELECT {$fields},COUNT(uid) AS join_count,MAX(my_score) AS my_max_score FROM (SELECT {$fields} FROM  __TABLE__ WHERE " . implode(' AND ',
                $wheres) . " ) AS a GROUP BY a.uid {$orderby} {$limit}";
        
        return $this->_m->fetch_array($sql, $params);
    }

    /**
     * 统计考试参与人员总数
     *
     * @author  houyingcai
     * @param array $conds 查询条件参数列表
     * 
     * @return array|bool
     */
    function count_mock_answer($conds)
    {
        $params = array();
        // 条件
        $wheres = array();
        if (!$this->_parse_where($wheres, $params, $conds)) {
            return false;
        }
        // 企业标记
        $wheres[] = '`domain`=?';
        $params[] = QY_DOMAIN;
        // 状态条件
        $wheres[] = '`status`<?';
        $params[] = $this->get_st_delete();

        $sql = 'select count(*) from (select  * from __TABLE__ WHERE ' . implode(' AND ',
                $wheres) . ' GROUP BY uid) __TABLE__';

        return $this->_m->result($sql, $params);

    }

    /**
     * 根据条件读取数据
     *
     * @author  houyingcai
     * @param array $conds 查询条件数组
     * @param array $order_option 排序数组
     * @param String $fields 查询字段
     *
     * @return array|bool
     */
    public function get_by_conds($conds, $order_option = array(), $fields = '*')
    {

        $params = array();
        // 条件
        $wheres = array();
        if (!$this->_parse_where($wheres, $params, $conds)) {
            return false;
        }

        // 排序
        $orderby = '';
        if (!$this->_order_by($orderby, $order_option)) {
            return false;
        }

        // 企业标记
        $wheres[] = "`{$this->prefield}domain`=?";
        $params[] = QY_DOMAIN;
        // 状态条件
        $wheres[] = "`{$this->prefield}status`<?";
        $params[] = $this->get_st_delete();

        // 执行 SQL
        return $this->_m->fetch_row("SELECT {$fields} FROM __TABLE__ WHERE " . implode(' AND ',
                $wheres) . "{$orderby} LIMIT 1",
            $params);
    }
}