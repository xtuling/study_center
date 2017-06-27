<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/5/5
 * Time: 10:23
 */
namespace Api\Controller\Course;

use Com\PackageValidate;
use Common\Service\UserService;

class StudyTimeController extends \Api\Controller\AbstractController
{
    /**
     * StudyTime
     * @author tangxingguo
     * @param int type 请求标识，等于1是间隔10s的请求，其他或者不请求为60s间隔（产品的需求）
     * @desc 学习时长接口(每分钟调用一次)
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'type' => 'integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        $user = $this->_login->user;
        $userServ = new UserService();
        $studyInfo = $userServ->get_by_conds(['uid' => $user['memUid']]);
        if (empty($studyInfo)) {
            // 新增
            $userServ->insert(['uid' => $user['memUid'], 'username' => $user['memUsername'], 'time_total' => 1]);
        } else {
            if (isset($postData['type']) && $postData['type'] == 1) {
                // 学习时长增加第一次进入课程内容页10s计一次时，使用8秒过滤
                $space = 8;
            } else {
                // 其他60s计一次，使用50秒过滤
                $space = 50;
            }
            // 累计（两次请求在间隔时间以上才进行累计）
            if ((MILLI_TIME - $studyInfo['updated']) > $space * 1000) {
                $userServ->update($studyInfo['user_id'], ['time_total = time_total + ?' => 1]);
            }
        }
    }
}
