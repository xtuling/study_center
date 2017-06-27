<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Api\Controller\Answer;

class AnswerListController extends \Api\Controller\AbstractController
{
    /**
     * AnswerList
     * @author
     * @desc 回答列表接口
     * @param Int page:1 当前页
     * @param Int limit:20 每页数据总数
     * @return array 回答列表
                    array(
                        'total' => 1, // 数据总数
                        'list' => array( // 回答列表
                            'answer_id' => 2, // 回答ID
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
                            'is_best' => 1, // 是否是最佳答案（1=否；2=是）
                            'like_total' => 10, // 点赞总数
                            'smart_time' => 1, // 审核时间（1小时内显示XX分钟前，1天以内的显示XX个小时前，1~7天显示XX天前，超过7天显示具体年-月-日 时-分）
                            'my_is_like' => 1, // 我是否已点赞（1=否，2=是）
                        )
                    );
     */
    public function Index_post()
    {
    }
}
