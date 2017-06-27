<?php
/**
 * 考试-答卷详情表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 18:03:52
 * @version $Id$
 */

namespace Common\Model;

class AnswerDetailModel extends AbstractModel
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /*** 查询答题记录个数
     * @author: 蔡建华
     * @param int $ep_id
     * @param string $uid 用户ID
     * @param string $fields 返回字段
     * @return array
     */
    public function answer_detail_record_num($ep_id = 0, $uid = '', $fields = "*")
    {
        $where = ' status < ? AND domain = ? ';
        $params[] = AnswerModel::ST_DELETE;
        $params[] = QY_DOMAIN;
        $AnswerModel = new AnswerModel();
        $table = $AnswerModel->get_tname();
        $sql = " AND ea_id in( select distinct  ea_id from " . $table . " where  status <? AND domain= ? AND ep_id=?  AND is_pass in(?) AND uid = ?)";
        $params[] = AnswerModel::ST_DELETE;
        $params[] = QY_DOMAIN;
        $params[] = $ep_id;
        $params[] = array(self::NO_MY_PASS, self::MY_PASS);
        $params[] = $uid;
        $sql = "select count( {$fields}) from  __TABLE__ where {$where}  $sql";

        return $this->_m->result($sql, $params);
    }

    /**  计算分数总和
     * @autor 蔡建华
     * @param int $ea_id
     * @return array
     */
    function get_score($ea_id = 0)
    {
        $where = ' status < ? AND domain = ? ';
        $params[] = AnswerModel::ST_DELETE;
        $params[] = QY_DOMAIN;
        $where .= ' AND ea_id = ? ';
        $params[] = $ea_id;
        $where .= ' AND is_pass = ? ';
        $params[] = self::MY_PASS;
        $sql = "select sum(my_score) from __TABLE__ where {$where}";
        return $this->_m->result($sql, $params);
    }
}