<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Api\Controller\Answer;

class AnswerLikeController extends \Api\Controller\AbstractController
{
    /**
     * AnswerLike
     * @author
     * @desc 回答点赞、取消点赞接口
     * @param Int answer_id:true 回答ID
     * @param Int type:true 操作类型（1=取消收藏，2=收藏）
     */
    public function Index_post()
    {
    }
}
