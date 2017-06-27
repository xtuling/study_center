<?php
/**
 * 考试-标签信息表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 18:06:47
 * @version $Id$
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

    /**
     *  根据答卷ID进行分组查询点赞记录
     * @autor 蔡建华
     * @param array $data 查询条件
     * @param string $fields 返回字段
     * @return array
     */
    public function getLikeCount($data = array(), $fields = "*")
    {
        // 组装查询语句
        $where = "status <? AND domain=?";
        // 操作状态和域名
        $params[] = LikeModel::ST_DELETE;
        $params[] = QY_DOMAIN;

        if (!empty($data)) {
            $where .= " and ea_id in(?)";
            $params[] = $data;
        }
        $sql = "SELECT {$fields} FROM __TABLE__ WHERE " . $where . "GROUP BY ea_id ";

        return $this->_m->fetch_array($sql, $params);
    }
}