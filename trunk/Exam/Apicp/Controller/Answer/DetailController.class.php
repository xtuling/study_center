<?php
/**
 * 获取考试答卷详情
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-23 16:34:29
 * @version $Id$
 */

namespace Apicp\Controller\Answer;

use Common\Common\User;
use Common\Service\AnswerService;
use Common\Service\AnswerDetailService;
use Common\Service\PaperService;

class DetailController extends AbstractController
{
    /**
     * @var  PaperService  实例化答卷表对象
     */
    protected $paper_serv;

    /**
     * @var  AnswerService  实例化答卷表对象
     */
    protected $answer_serv;

    /**
     * @var  AnswerDetailService  实例化答卷详情表对象
     */
    protected $answer_detail_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        $this->paper_serv = new PaperService();
        $this->answer_serv = new AnswerService();
        $this->answer_detail_serv = new AnswerDetailService();

        return true;
    }

    public function Index_post()
    {
        $params = I('post.');
        $ea_id = rintval($params['ea_id']);

        // 答卷ID不能为空
        if (empty($ea_id)) {

            E('_EMPTY_EA_ID');

            return false;
        }

        // 获取答卷信息
        $answer = $this->answer_serv->get($ea_id);
        // 答卷不存在
        if (empty($answer)) {

            E('_ERR_ANSWER_NOT_FOUND');

            return false;
        }

        // 根据UID获取用户信息
        $user_serv = &User::instance();
        $user = $user_serv->getByUid($answer['uid']);

        // 获取答卷详情
        $answer_detail = $this->answer_detail_serv->list_by_conds(
            array('ea_id' => $answer['ea_id'])
        );

        // 获取试卷信息
        $paper = $this->paper_serv->get($answer['ep_id']);

        // 格式化答卷详情
        $answer_list = $this->answer_detail_serv->question_list_param(
            $answer_detail,
            AnswerService::KEYWORD_OPEN
        );

        // 组装返回数据
        $data = array(
            'ep_name' => $paper['ep_name'],
            'my_score' => $answer['my_score'],
            'username' => $user['memUsername'],
            'list' => $answer_list
        );

        $this->_result = $data;

        return true;

    }

}
