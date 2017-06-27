<?php
/**
 * 试卷题目对应关系表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 18:05:58
 * @version $Id$
 */

namespace Common\Model;

class PaperTempModel extends AbstractModel
{

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据条件查询备选试题列表
     * @author daijun
     * @param $conds 查询条件
     * @param null $page_option 分页参数
     * @param array $order_option 排序参数
     * @param string $fields 查询字段
     * @return array|bool
     */
    public function list_by_where($conds, $page_option = null, $order_option = array(), $fields = '*')
    {
        $params = array();
        // 条件
        $wheres = array();
        if (!$this->_parse_where($wheres, $params, $conds)) {
            return false;
        }
        // 企业标记
        $wheres[] = "a.domain=?";
        $params[] = QY_DOMAIN;
        // 状态条件
        $wheres[] = "a.status<?";
        $params[] = $this->get_st_delete();

        // 企业标记
        $wheres[] = "b.domain=?";
        $params[] = QY_DOMAIN;
        // 状态条件
        $wheres[] = "b.status<?";
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
        $sql = "SELECT {$fields} FROM __TABLE__ AS a LEFT JOIN oa_exam_topic AS b ON a.et_id=b.et_id WHERE " . implode(' AND ',
                $wheres) . "{$orderby}{$limit}";

        // 读取记录
        return $this->_m->fetch_array($sql, $params);

    }

    /**
     * 根据条件查询备选试题总数
     * @author daijun
     * @param $conds 查询条件
     * @param string $fields
     * @return array|bool
     */
    public function count_by_where($conds, $fields = '*')
    {
        $params = array();
        // 更新条件
        $wheres = array();
        if (!$this->_parse_where($wheres, $params, $conds)) {
            return false;
        }

        // 企业标记
        $wheres[] = "a.domain=?";
        $params[] = QY_DOMAIN;
        // 状态条件
        $wheres[] = "a.status<?";
        $params[] = $this->get_st_delete();

        // 企业标记
        $wheres[] = "b.domain=?";
        $params[] = QY_DOMAIN;
        // 状态条件
        $wheres[] = "b.status<?";
        $params[] = $this->get_st_delete();
        $sql = "SELECT COUNT(" . $fields . ") FROM __TABLE__ AS a LEFT JOIN oa_exam_topic AS b ON a.et_id=b.et_id WHERE " . implode(" AND ",
                $wheres);

        return $this->_m->result($sql, $params);
    }
}