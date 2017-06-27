<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/12
 * Time: 10:46
 */
namespace Common\Service;

use Common\Model\StudyModel;

class StudyService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new StudyModel();
    }

    /**
     * 获取未学习人员UID
     * @param array $all_uids 可学习人员UID
     * @param array $study_uids 已学习人员UID
     * @return array
     */
    public function listUnstudyUids($all_uids, $study_uids)
    {
        if (!is_array($all_uids) || !is_array($study_uids)) {
            return [];
        }

        // 对比可学习人员、已学习人员差异
        $unstudy_uids = array_diff($all_uids, $study_uids);

        // 将对象处理为数组,返回
        return array_values($unstudy_uids);
    }
}
