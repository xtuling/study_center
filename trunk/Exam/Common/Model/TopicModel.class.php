<?php
/**
 * 考试-题目表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-19 18:07:28
 * @version $Id$
 */

namespace Common\Model;

class TopicModel extends AbstractModel
{

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 查询总数
     * 
     * @param array $params 参数列表
     * 
     * @return int|mixed
     */
    public function count_by_where($params = array())
    {
        $where = $this->get_where($params);
        $sql = " SELECT COUNT(*) FROM __TABLE__  AS  t " . $where;

        return $this->_m->result($sql, array());
    }

    /**
     * 查询列表
     * 
     * @param array $params 条件参数
     * @param null $page_option 分页参数
     * @param array $order_option 排序参数
     * @param string $fields 查询的字段
     * 
     * @return array|bool
     */
    public function list_by_where($params = array(), $page_option = null, $order_option = array(), $fields = '*')
    {
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

        $where = $this->get_where($params);

        $sql = " SELECT {$fields} FROM __TABLE__  AS  t " . $where . " {$orderby}{$limit}";

        return $this->_m->fetch_array($sql, array());
    }

    /**
     * 组装sql语句
     * 
     * @param array $params 参数列表
     * 
     * @return string
     */
    protected function get_where($params = array())
    {
        $eb_id='';

        if($params['eb_id']){

           $eb_id=' AND t.eb_id ='. $params['eb_id'];
        }

        $where = ' WHERE t.status<' . self::ST_DELETE . ' AND t.domain= \'' . QY_DOMAIN . '\' '.$eb_id;

        if (!empty($params['attr_id'])) {

            $where .= ' AND t.et_id in (SELECT a.et_id FROM `oa_exam_topic_attr` a WHERE a.attr_id=' . $params['attr_id'] . ' AND a.status<' . self::ST_DELETE . ' AND a.domain=\'' . QY_DOMAIN . '\')';
        }

        if (!empty($params['et_type'])) {

            if(is_array($params['et_type'])){

                $where .= " AND t.et_type IN (".implode(',',$params['et_type']).")";
            }else{

                $where .= ' AND t.et_type = ' . $params['et_type'];
            }

        }

        if (!empty($params['title'])) {

            $where .= ' AND t.title like \'%' . $params['title'] . '%\'';
        }

        return $where;
    }

    /**
     * 给字段添加自动添加一个数
     * 
     * @param string $field
     * @param array $condition
     * @param int $step
     * 
     * @return boolean
     */
    public function setIncNum($field, $condition = array(), $step = 1)
    {
        $this->where($condition);

        return $this->setInc($field, $step);
    }
    /**
     * 给字段添加自动添加一个数
     * 
     * @autor 蔡建华
     * @param array $conds
     * 
     * @return boolean
     */
    public function AddIncNum($conds)
    {
        // 更新条件
        $params = array();
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

        return $this->_m->execsql('UPDATE __TABLE__ set `use_num`=`use_num`+1  WHERE ' . implode(' AND ', $wheres),
            $params);
    }
    /**
     * 根据条件，查询包含已删除的数据
     *
     * @param array $conds 条件数组
     * @param int|array $page_option 分页参数
     * @param array $order_option 排序
     * @param string $fields 读取字段
     *
     * @return array|bool
     */
    public function list_topic_contain_del($conds, $page_option = null, $order_option = array(), $fields = '*')
    {

        $params = array();
        // 条件
        $wheres = array();
        if (!$this->_parse_where($wheres, $params, $conds)) {
            return false;
        }
        // 企业标记
        $wheres[] = "`{$this->prefield}domain`=?";
        $params[] = QY_DOMAIN;

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
        return $this->_m->fetch_array("SELECT {$fields} FROM __TABLE__ WHERE " . implode(' AND ',
                $wheres) . "{$orderby}{$limit}", $params);
    }

    /**
     * 根据条件计算数量(包含已删除)
     *
     * @param array $conds
     * @param string $fields
     *
     * @throws service_exception
     * @return number
     */
    public function count_topic_contain_del($conds, $fields = '*')
    {

        $params = array();
        // 更新条件
        $wheres = array();
        if (!$this->_parse_where($wheres, $params, $conds)) {
            return false;
        }

        // 企业标记
        $wheres[] = "`{$this->prefield}domain`=?";
        $params[] = QY_DOMAIN;

        return $this->_m->result('SELECT COUNT(' . $fields . ') FROM __TABLE__ WHERE ' . implode(' AND ', $wheres),
            $params);
    }


}
