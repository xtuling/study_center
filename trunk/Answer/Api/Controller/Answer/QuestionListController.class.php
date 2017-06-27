<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Api\Controller\Answer;

use Com\PackageValidate;
use Common\Common\Constant;

class QuestionListController extends \Api\Controller\AbstractController
{
    /**
     * QuestionList
     * @author
     * @desc 提问列表接口
     * @param Int page:1 当前页
     * @param Int limit:20 每页数据总数
     * @param Int my_question 是否筛选我的提问（1=否，2=是）
     * @param String question_title 提问标题（发起提问页，前20条，排序为点赞>审核时间）
     * @return array 提问列表
                    array(
                        'page' => 1, // 当前页码
                        'limit' => 20, // 每页数据数
                        'total' => 3, // 数据总数
                        'list' => array( // 提问列表
                            'question_id' => 1, // 提问ID
                            'question_title' => '第一个提问', // 提问标题
                            'class_id' => 1, // 分类ID
                            'class_name' => '分类', // 分类名称
                            'username' => '张三', // 提问人姓名
                            'dp_name' => '技术部', // 提问人所属部门
                            'is_solve' => 1, // 解决状态（1=未解决；2=已解决）
                            'answer_pass_total' => 10, // 回答审核通过数
                            'answer_wait_total' => 10, // 回答等等审核数
                            'created' => 1491897290000, // 提问时间
                            'check_time' => 1491897290000, // 审批时间
                            'imgs' => array(
                                array(
                                    'at_id' => 'abcdefg', // 图片ID
                                    'at_url' => 'http://qy.vchagyi.com', // 图片URL
                                ),
                            ),
                        )
                    );
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'page' => 'integer',
            'limit' => 'integer',
            'question_title' => 'max:20',
            'my_question' => 'integer|in:1,2',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 分页默认值
        $postData['page'] = isset($postData['page']) ? $postData['page'] : Constant::PAGING_DEFAULT_PAGE;
        $postData['limit'] = isset($postData['limit']) ? $postData['limit'] : Constant::PAGING_DEFAULT_LIMIT;
        list($start, $perpage) = page_limit($postData['page'], $postData['limit']);

        // 默认排序：审核通过时间倒叙
        $order_option = ['check_time' => 'desc'];

        // 条件
        $conds = ['check_status' => Constant::QUESTION_CHECK_STATUS_PASS];
        if (isset($postData['my_question']) && $postData['my_question'] == 2) {
            // 筛选我的提问（各个审核状态）
            $conds = ['uid' => $this->uid];
            // 排序：发起时间倒叙
            $order_option = ['created' => 'desc'];
        }
        if (isset($postData['question_title'])) {
            // 标题筛选
            $conds['question_title like ?'] = '%' . $postData['question_title'] . '%';
            // 排序：解决状态 > 已审核回答总数 > 审核通过时间
            $order_option = ['is_solve' => 'desc', 'answer_pass_total' => 'desc', 'check_time' => 'desc'];
        }
    }
}
