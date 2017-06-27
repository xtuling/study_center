<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Apicp\Controller\Answer;

use Com\PackageValidate;
use Common\Service\AnswerService;
use Common\Service\QuestionService;

class DeleteQuestionController extends \Apicp\Controller\AbstractController
{
    /**
     * DeleteQuestion
     * @author
     * @desc 删除提问接口
     * @param Array question_ids 提问ID
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'question_ids' => 'require|array',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $questionIds = $validate->postData['question_ids'];

        // 删除提问
        $questionServ = new QuestionService();
        $questionServ->delete($questionIds);

        // 删除提问的回答
        $answerServ = new AnswerService();
        $answerServ->delete_by_conds(['question_id' => $questionIds]);
    }
}
