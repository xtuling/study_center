<?php
/**
 * 获取模拟试卷已参与记录列表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-23 16:32:33
 * @version $Id$
 */

namespace Apicp\Controller\Answer;

use Common\Service\AnswerService;
use Common\Common\User;

class JoinRecordController extends AbstractController
{
    /**
     * @var  AnswerService  实例化答卷表对象
     */
    protected $answer_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        $this->answer_serv = new AnswerService();

        return true;
    }

    public function Index_post()
    {
        $params = I('post.');

        $uid = $params['uid'];

        // 根据用户UID获取用户信息
        $user = User::instance();
        $user_info = $user->getByUid($uid);

        // 模拟记录列表
        $list = $this->answer_serv->test_record_list($params, $uid);
        // 组装返回数据
        $list['username'] = $user_info['memUsername'];
        $list['avatar'] = $user_info['memFace'];

        unset(
            $list['ep_status'],
            $list['exam_times'],
            $list['pass_times'],
            $list['answer_num']
        );

        $this->_result = $list;

        return true;
    }

}
