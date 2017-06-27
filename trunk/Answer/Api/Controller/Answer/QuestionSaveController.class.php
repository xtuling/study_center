<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Api\Controller\Answer;

class QuestionSaveController extends \Api\Controller\AbstractController
{
    /**
     * QuestionSave
     * @author
     * @desc 提问保存接口
     * @param String question_title 提问标题（max:20）
     * @param String question_content 提问内容（max:500）
     * @param Int class_id 分类ID
     * @param Int integral 悬赏积分
     * @param String imgs.at_id 图片ID
     * @param String imgs.at_url 图片URL
     */
    public function Index_post()
    {
    }
}
