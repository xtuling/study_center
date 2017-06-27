<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/5/18
 * Time: 11:34
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
     * 获取用户可查阅、下载的目录列表
     * @author tangxingguo
     * @param array $rights 权限数据
     * @param int $right_type 权限类型（1=查阅权限；2=下载权限）
     * @return array
     */
    public function listByRight($rights, $right_type)
    {
        if (!is_array($rights) || empty($rights) || !in_array($right_type, [Constant::RIGHT_TYPE_IS_READ, Constant::RIGHT_TYPE_IS_DOWNLOAD])) {
            return [];
        }

        $conds = $this->buildConds($rights);

        $where = '`right_type` = ? and (' . implode(' OR ', $conds) . ') and `domain` = ? and status < ?';
        $sql = "SELECT `file_id` FROM __TABLE__ WHERE {$where}";

        $params = [
            $right_type,
            QY_DOMAIN,
            $this->get_st_delete(),
        ];

        return $this->_m->fetch_array($sql, $params);
    }

    /**
     * 根据目录ID和权限数据计算数量
     * @author tangxingguo
     * @param int $file_id 课程ID
     * @param array $rights 权限数据
     * @param int $right_type 权限类型（1=查阅权限；2=下载权限）
     * @return int
     */
    public function countByRight($file_id, $rights, $right_type)
    {
        $count = 0;

        if (!is_array($rights) || empty($rights)) {
            return $count;
        }

        $conds = $this->buildConds($rights);
        $where = '`file_id` = ? and `right_type` = ? and (' . implode(' OR ', $conds) . ') and `domain` = ? and status < ?';
        $sql = "SELECT COUNT(*) FROM __TABLE__ WHERE {$where}";

        $params = [
            $file_id,
            $right_type,
            QY_DOMAIN,
            $this->get_st_delete(),
        ];

        $count = $this->_m->result($sql, $params);
        return intval($count);
    }
}
