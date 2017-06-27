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
     * 根据新闻ID和权限数据计算数量
     * @author zhonglei
     * @param int $article_id 新闻ID
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
}
