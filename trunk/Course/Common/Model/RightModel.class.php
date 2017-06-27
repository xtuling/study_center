<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/12
 * Time: 10:33
 */
namespace Common\Model;

use Common\Common\Constant;

class RightModel extends AbstractModel
{

    // 构造方法
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据权限数据构建查询条件
     * @author zhonglei
     * @param array $rights 权限数据
     * @return array
     */
    public function buildConds($rights)
    {
        $conds = [];

        if (!is_array($rights) || empty($rights)) {
            return $conds;
        }

        foreach ($rights as $k => $v) {
            switch ($k) {
                // 全公司
                case Constant::RIGHT_TYPE_ALL:
                    $conds[] = "`obj_type` = {$k}";
                    break;

                // 部门、标签、人员、职位、角色
                default:
                    $obj_id = implode("','", $v);
                    $conds[] = "(`obj_type` = {$k} and `obj_id` in ('{$obj_id}'))";
            }
        }

        return $conds;
    }

    /**
     * 根据课程ID和权限数据计算数量
     * @author zhonglei
     * @param int $article_id 课程ID
     * @param array $rights 权限数据
     * @return int
     */
    public function countByRight($article_id, $rights)
    {
        $count = 0;

        if (!is_array($rights) || empty($rights)) {
            return $count;
        }

        $conds = $this->buildConds($rights);
        $where = '`article_id` = ? and (' . implode(' OR ', $conds) . ') and `domain` = ? and status < ?';
        $sql = "SELECT COUNT(*) FROM __TABLE__ WHERE {$where}";

        $params = [
            $article_id,
            QY_DOMAIN,
            $this->get_st_delete(),
        ];

        $count = $this->_m->result($sql, $params);
        return intval($count);
    }

    /**
     * 根据需要的数据类型和权限数据获取数据
     * @author liyifei
     * @param array $rights 权限数据
     * @param string $select_data 要查询的数据（article_id、class_id）
     * @return array
     */
    public function listByRight($rights, $select_data)
    {
        if (!is_array($rights) || empty($rights) || !in_array($select_data, ['article_id', 'class_id', 'award_id'])) {
            return [];
        }

        $conds = $this->buildConds($rights);

        $where = '`' . $select_data .'` > ? and (' . implode(' OR ', $conds) . ') and `domain` = ? and status < ?';
        $sql = "SELECT $select_data FROM __TABLE__ WHERE {$where}";

        $params = [
            0,
            QY_DOMAIN,
            $this->get_st_delete(),
        ];

        return $this->_m->fetch_array($sql, $params);
    }
}
