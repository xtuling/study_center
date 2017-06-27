<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Apicp\Controller\Answer;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\AnswerService;

class ApproveAnswerController extends \Apicp\Controller\AbstractController
{
    /**
     * ApproveAnswer
     * @author
     * @desc 审批回答接口
     * @param Int type 审批类型（1=驳回；2=通过）
     * @param Int answer_id 回答ID
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'type' => 'require|integer|in:1,2',
            'answer_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $type = $validate->postData['type'];
        $answerId = $validate->postData['answer_id'];

        // 回答信息
        $answerServ = new AnswerService();
        $answerInfo = $answerServ->get($answerId);
        if (empty($answerInfo)) {
            E('_ERR_ANSWER_NOT_FOUND');
        }
        if ($answerInfo['check_status'] != Constant::ANSWER_CHECK_STATUS_WAIT) {
            // 回答已经被审批
            E('_ERR_ANSWER_IS_APPROVED');
        }

        // 审批
        $checkStatus = $type == Constant::QUESTION_CHECK_IS_FAIL ? Constant::ANSWER_CHECK_STATUS_FAIL : Constant::ANSWER_CHECK_STATUS_PASS;
        $data = [
            'checker_type' => Constant::CHECKER_IS_ADMIN,
            'check_time' => MILLI_TIME,
            'check_status' => $checkStatus,
        ];
        $answerServ->update($answerId, $data);
    }
}
