<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/12
 * Time: 10:46
 */
namespace Common\Service;

use Common\Model\StudyRecordModel;

class StudyRecordService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new StudyRecordModel();
    }

    /**
     * 根据课程ID获取谁在学人员总数
     * @author tangxingguo
     * @param int $article_id 课程ID
     * @return int 谁在学人员总数
     */
    public function getUserCount($article_id)
    {
        $list = $this->list_by_conds(['article_id' => $article_id]);
        if (!empty($list)) {
            $uids = array_unique(array_column($list, 'uid'));
        }
        return isset($uids) ? count($uids) : 0;
    }
}
