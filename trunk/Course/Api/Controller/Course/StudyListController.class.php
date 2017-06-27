<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/5/5
 * Time: 10:23
 */

namespace Api\Controller\Course;

use Com\PackageValidate;
use Common\Common\User;
use Common\Service\StudyRecordService;

class StudyListController extends \Api\Controller\AbstractController
{
    /**
     * 是否必须登录
     */
    protected $_require_login = false;
    /**
     * StudyList
     * @author tangxingguo
     * @desc 谁在学列表接口
     * @param int    article_id:true 课程ID
     * @return array 学生列表
                  array(
                    'uid' => '469C4B0A7F0000016F4D5C189632297B', // 人员ID
                    'username' => '张三', // 人员姓名
                    'face' => 'http://shp.qpic.cn/bizmp/gdZUibR6BHrmiar6pZ6pLfRyZSVaXJicn2CsvKRdI9gccXRfP2NrDvJ8A/', // 头像
                    )
     */

    public function Index_post()
    {
        // 验证规则
        $rules = [
            'article_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 人员列表
        $tudyRecordServ = new StudyRecordService();
        $studyList = $tudyRecordServ->list_by_conds(['article_id' => $postData['article_id']]);

        if ($studyList) {
            // 取人员UID并去重
            $uids = array_values(array_unique(array_column($studyList, 'uid')));

            // 人员姓名使用学习库内数据，防止人员被删除数据丢失
            $usernames = array_column($studyList, 'username', 'uid');

            // 取头像
            $userServ = &User::instance();
            $userList = $userServ->listAll(['memUids' => $uids]);
            $userList = array_combine_by_key($userList, 'memUid');
            foreach ($uids as $v) {
                $userInfo = [
                    'uid' => $v,
                    'username' => $usernames[$v],
                    'face' => isset($userList[$v]) ? $userList[$v]['memFace'] : '',
                ];
                $result[] = $userInfo;
            }
        }
        $this->_result = isset($result) ? $result : [];
    }
}
