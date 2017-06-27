<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Api\Controller\Answer;

class QuestionInfoController extends \Api\Controller\AbstractController
{
    /**
     * QuestionInfo
     * @author
     * @desc 提问详情接口
     * @param Int question_id 提问ID
     * @return array 提问详情
                    array(
                        'question_id' => 1, // 提问ID
                        'question_title' => '提问标题'，// 提问标题
                        'class_id' => 1, // 分类ID
                        'class_name' => '测试', // 分类名称
                        'integral' => 10, // 悬赏积分
                        'uid' => 10, // 用户UID
                        'username' => 10, // 用户姓名
                        'face' => 'http://qy.vchangyi.com', // 用户头像
                        'description' => 10, // 描述
                        'created' => 1234567890, // 提问时间
                        'smart_time' => 1, // 审核时间（1小时内显示XX分钟前，1天以内的显示XX个小时前，1~7天显示XX天前，超过7天显示具体年-月-日 时-分）
                        'check_status' => 1, // 审核状态（1=未审核；2=审核通过；3=审核未通过）
                        'is_solve' => 1, // 解决状态（1=未解决；2=已解决）
                        'imgs' => array(
                            array(
                                'at_id' => 'abcdefg', // 图片ID
                                'at_url' => 'http://qy.vchagyi.com', // 图片URL
                            ),
                        ),
                    );
     */
    public function Index_post()
    {
    }
}
