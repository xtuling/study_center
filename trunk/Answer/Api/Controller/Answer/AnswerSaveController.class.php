<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Api\Controller\Answer;

class AnswerSaveController extends \Api\Controller\AbstractController
{
    /**
     * AnswerSave
     * @author
     * @desc 回答保存接口
     * @param Int question_id 提问ID
     * @param Int answer_content 回答内容（max:500）
     * @param String imgs.at_id 图片ID
     * @param String imgs.at_url 图片URL
     */
    public function Index_post()
    {
    }
}
