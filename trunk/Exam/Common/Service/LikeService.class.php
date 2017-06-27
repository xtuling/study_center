<?php
/**
 * 考试-标签信息表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 17:44:12
 * @version $Id$
 */

namespace Common\Service;

use Common\Model\LikeModel;

class LikeService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        $this->_d = new LikeModel();

        parent::__construct();
    }

    /** 点赞
     * @author: 蔡建华
     * @param int $ea_id 答卷ID
     * @param string $uid 用户ID
     * @return bool
     */
    function add_like_data($ea_id = 0, $uid = '')
    {
        if (!$ea_id) {
            E('_EMPTY_EA_ID');
            return false;
        }
        if (!$uid) {
            E('_EMPTY_UID');
            return false;
        }
        // 查询点赞记录
        $data = array(
            'uid' => $uid,
            'ea_id' => $ea_id
        );
        $count = $this->count_by_conds($data);
        // 已点赞
        if ($count) {
            E('_ERR_AC_LIKE_END');
            return false;
        }
        $rel = $this->_d->insert($data);
        if ($rel) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * 取消点赞接口
     * @author: 蔡建华
     * @param int $ea_id 答卷ID
     * @param string $uid 用户ID
     * @return bool
     */
    public function del_like_data($ea_id = 0, $uid = '')
    {
        $type = intval($param['type']);
        $cid = intval($param['cid']);

        if (!$ea_id) {
            E('_EMPTY_EA_ID');
            return false;
        }
        if (!$uid) {
            E('_EMPTY_UID');
            return false;
        }
        // 查询点赞记录
        $data = array(
            'uid' => $uid,
            'ea_id' => $ea_id
        );
        $count = $this->count_by_conds($data);
        // 没有点赞记录
        if (!$count) {
            E('_ERR_EA_UNLIKE_END');
            return false;
        }
        $rel = $this->_d->delete_by_conds($data);
        if ($rel) {
            return true;
        } else {
            return false;
        }
    }
}
