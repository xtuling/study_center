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
use Common\Service\QuestionService;

class SaveAnswerController extends \Apicp\Controller\AbstractController
{
    /**
     * SaveAnswer
     * @author
     * @desc 保存回答接口
     * @param Int question_id 提问ID
     * @param Int answer_content 回答内容
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'question_id' => 'require|integer',
            'answer_content' => 'require',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $questionId = $validate->postData['question_id'];
        $answerContent = $validate->postData['answer_content'];

        // 提问检查
        $questionServ = new QuestionService();
        $questionInfo = $questionServ->get($questionId);
        if (empty($questionInfo)) {
            E('_ERR_ANSWER_QUESTION_NOT_FOUND');
        }
        if ($questionInfo['check_status'] != Constant::QUESTION_CHECK_STATUS_PASS) {
            // 提问通过审批才可以回答
            E('_ERR_ANSWER_QUESTION_NOT_PASS');
        }

        // 添加回答，审核为通过
        $data = [
            'question_id' => $questionId,
            'answer_content' => $answerContent,
            'class_id' => $questionInfo['class_id'],
            'user_type' => Constant::ANSWER_PERON_IS_ADMIN,
            'checker_type' => Constant::CHECKER_IS_ADMIN,
            'check_time' => MILLI_TIME,
            'check_status' => Constant::ANSWER_CHECK_STATUS_PASS,
        ];
        $answerServ = new AnswerService();
        $answerServ->insert($data);
    }
}
