<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Api\Controller\Answer;

class AnswerInfoController extends \Api\Controller\AbstractController
{
    /**
     * AnswerInfo
     * @author
     * @desc 回答详情接口
     * @param Int answer_id 回答ID
     * @return array 回答详情
                    array(
                        'answer_id' => 2,
                        'user_type' => 2, // 回答人类型（1=用户；2=管理员）
                        'uid' => '0BD8C5557F00000171F5B2321A874407', // 人员ID
                        'username' => '张三', // 人员姓名
                        'face' => 'http://qy.vchangyi.com', // 人员头像
                        'answer_content' => '这个问题简单', // 回答内容
                        'imgs' => array(
                            array(
                                'at_id' => 'abcdefg', // 图片ID
                                'at_url' => 'http://qy.vchagyi.com', // 图片URL
                            ),
                        ),
                    )
     */
    public function Index_post()
    {
    }
}
