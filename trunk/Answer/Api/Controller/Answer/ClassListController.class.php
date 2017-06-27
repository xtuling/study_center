<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Api\Controller\Answer;

class ClassListController extends \Api\Controller\AbstractController
{
    /**
     * ClassList
     * @author
     * @desc 分类列表
     * @param Int need_integral 是否需要当前用户积分（1=不需要，2=需要）
     * @return array 提问详情
                array(
                    'class_id' => 1, // 分类ID
                    'class_name' => '第一个分类', // 分类名称
                    'integral' => 1, // 悬赏积分
                )
     */
    public function Index_post()
    {
    }
}
