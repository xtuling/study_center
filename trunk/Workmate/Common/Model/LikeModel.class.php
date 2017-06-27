<?php
/**
 * 同事圈以及评论点赞信息表
 * User: 代军
 * Date: 2017-04-24
 */
namespace Common\Model;

class LikeModel extends AbstractModel
{

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function get_like_total($ids, $page_option = null, $order_option = array())
    {

        $where = ' domain=? AND  status<? AND cid in(?) ';
        $params = array(QY_DOMAIN, self::ST_DELETE, $ids);


        $sql = 'SELECT cid, COUNT( * ) as total FROM __TABLE__  WHERE ' . $where . " GROUP BY cid ";

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
}

