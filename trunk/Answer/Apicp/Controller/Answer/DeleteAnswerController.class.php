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

class DeleteAnswerController extends \Apicp\Controller\AbstractController
{
    /**
     * DeleteAnswer
     * @author
     * @desc 删除回答接口
     * @param Int answer_ids 回答ID（一维数组）
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'answer_ids' => 'require|array',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $answerIds = $validate->postData['answer_ids'];

        // 最佳答案检查
        $answerServ = new AnswerService();
        $count = $answerServ->count_by_conds(['answer_id' => $answerIds, 'is_best' => Constant::ANSWER_IS_BEST_TRUE]);
        if ($count > 0) {
            E('_ERR_ANSWER_BEST_NOT_ALLOW_DELETE');
        }

        // 删除问题
        $answerServ->delete($answerIds);
    }
}
