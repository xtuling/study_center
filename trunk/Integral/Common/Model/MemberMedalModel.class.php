<?php
/**
 * MemberMedalModel.class.php
 * 用户勋章表 Model
 */

namespace Common\Model;

class MemberMedalModel extends AbstractModel
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 增加人员获得勋章数
     * @param $imId
     * @param $uid
     * @return array|bool
     */
    public function addMedalTotal($imId, $uid)
    {
        if (empty($imId) || empty($uid)) {
            return false;
        }

        $sql = "UPDATE `oa_integral_member_medal`
                SET `im_total` = `im_total` + 1
                WHERE 
                    `im_id` = {$imId} AND
                    `mem_uid` = '{$uid}' AND 
                    `domain` = ? AND 
                    `status` < ?";

        return $this->_m->execsql($sql, [QY_DOMAIN, $this->get_st_delete()]);
    }
}
