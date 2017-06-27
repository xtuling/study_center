<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Apicp\Controller\Answer;

use Com\PackageValidate;
use Common\Common\AnswerHelper;
use Common\Common\Constant;
use Common\Service\ClassService;
use Common\Service\QuestionService;

class ApproveQuestionController extends \Apicp\Controller\AbstractController
{
    /**
     * ApproveQuestion
     * @author
     * @desc 审批提问接口
     * @param Int type:true 审批类型（1=驳回；2=通过）
     * @param Int question_id:true 问题ID
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'type' => 'require|integer|in:1,2',
            'question_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $type = $validate->postData['type'];
        $questionId = $validate->postData['question_id'];

        // 提问信息
        $questionServ = new QuestionService();
        $questionInfo = $questionServ->get($questionId);
        if (empty($questionInfo)) {
            E('_ERR_ANSWER_QUESTION_NOT_FOUND');
        }
        if ($questionInfo['check_status'] != Constant::QUESTION_CHECK_STATUS_WAIT) {
            // 提问已经被审批
            E('_ERR_ANSWER_QUESTION_IS_APPROVED');
        }

        // 审批
        if ($type == Constant::QUESTION_CHECK_IS_FAIL) {
            // 驳回
            $checkStatus = Constant::QUESTION_CHECK_STATUS_FAIL;
            if ($questionInfo['integral'] > 0) {
                // 有积分，退积分
                // TODO 唐兴国 2017-6-26 17:48:39 这里要研究公共积分操作的消息机制，后续研究完再补充
            } else {
                // 没积分，发消息
                AnswerHelper::instance()->failNoticeToQer($questionInfo['uid'], $questionInfo);
            }
        } else {
            // 通过
            $checkStatus = Constant::QUESTION_CHECK_STATUS_PASS;
            // 发消息给推送人
            AnswerHelper::instance()->passNoticeToQer($questionInfo['uid'], $questionInfo);
            // 发消息给其他人
            AnswerHelper::instance()->passNoticeToOther($questionInfo['uid'], $questionInfo);
        }
        $data = [
            'checker_type' => Constant::CHECKER_IS_ADMIN,
            'check_time' => MILLI_TIME,
            'check_status' => $checkStatus,
        ];
        $questionServ->update($questionId, $data);
    }
}
