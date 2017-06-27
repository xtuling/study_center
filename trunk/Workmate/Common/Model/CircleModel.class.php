<?php
/**
 * 同事圈信息表
 * User: 代军
 * Date: 2017-04-24
 */
namespace Common\Model;

class CircleModel extends AbstractModel
{

    // 有附件
    const ATTACH = 1;

    // 无附件
    const NOT_ATTACH = 0;

    // 话题标识
    const CIRCLE_PID = 0;


    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取帖子列表的评论数据
     * @param $ids 帖子ID集合
     * @param $audit_state 评论数据的状态
     * @param null $page_option 分页数据
     * @param array $order_option 排序数据
     * @return array|bool
     */
    public function get_comment_num($ids, $audit_state, $page_option = null, $order_option = array())
    {
        $where = ' domain=? AND  status<? AND pid in(?) AND audit_state=? ';
        $params = array(QY_DOMAIN, self::ST_DELETE, $ids, $audit_state);

        $sql = 'SELECT pid, COUNT( * ) as total FROM __TABLE__  WHERE ' . $where . " GROUP BY pid ";

        // 分页参数
        $limit = '';
        if (!$this->_limit($limit, $page_option)) {
            return false;
        }

        // 排序
        $order_by = '';
        if (!$this->_order_by($order_by, $order_option)) {
            return false;
        }

        return $this->_m->fetch_array($sql . $order_by . $limit, $params);
    }

    /**
     * 【微信端】 获取评论列表根据点赞倒序
     * @param array $conditions 查询条件
     * @param array $page_option 分页参数
     * @param array $order_by 排序字段
     * @param string $file 默认搜索字段
     * @return mixed
     */
    public function list_by_comment($conditions = array(), $page_option = array(), $order_option = array(), $file = '*')
    {

        $sql = "SELECT {$file},(SELECT COUNT(*) from oa_workmate_like WHERE cid=`id` AND  domain=? AND  status<? ) as like_total FROM __TABLE__  WHERE ";

        // 初始化like_total条件
        $params[] = QY_DOMAIN;
        $params[] = self::ST_DELETE;

        // 如果状态存在
        if (is_numeric($conditions['audit_state'])) {

            $where[] = 'audit_state=?';
            $params[] = $conditions['audit_state'];
        }

        $where[] = 'pid=?';
        $params[] = $conditions['pid'];
        $where[] = 'domain=?';
        $where[] = 'status<?';
        $params[] = QY_DOMAIN;
        $params[] = self::ST_DELETE;

        // 分页参数
        $limit = '';
        if (!$this->_limit($limit, $page_option)) {
            return false;
        }

        // 默认排序
        $order = array(
            'like_total' => 'DESC',
            'audit_time' => 'DESC',
            'created' => 'DESC'
        );

        // 如果有其他排序
        if (!empty($order_option)) {

            $order = array_merge($order, $order_option);
        }

        // 排序
        $order_by = '';
        if (!$this->_order_by($order_by, $order)) {
            return false;
        }

        return $this->_m->fetch_array($sql . implode(' AND ', $where) . $order_by . $limit, $params);

    }
}

