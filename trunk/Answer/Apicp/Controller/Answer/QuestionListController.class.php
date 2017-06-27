<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Apicp\Controller\Answer;

use Com\PackageValidate;
use Common\Common\AnswerHelper;
use Common\Common\Constant;
use Common\Common\User;
use Common\Service\ClassService;
use Common\Service\QuestionService;

class QuestionListController extends \Apicp\Controller\AbstractController
{
    /**
     * QuestionList
     * @author
     * @desc 提问列表
     * @param Int page:1 当前页
     * @param Int limit:20 每页数据总数
     * @param String question_title 提问标题（max:20）
     * @param Int class_id 分类ID
     * @param String username 提问人姓名
     * @param Array dp_ids 部门ID(一维数组)
     * @param Int check_status 审核状态（1=未审核；2=审核通过；3=审核未通过）
     * @param Int is_solve 解决状态（1=未解决；2=已解决）
     * @param Int start_time 起始时间
     * @param Int end_time 结束时间
     * @return array 提问列表
                        array(
                            'total' => 3, // 数据总数
                            'wait_total' => 1, // 待审核提问数
                            'pass_total' => 1, // 通过审核提问数
                            'fail_total' => 1, // 未通过审核提问数
                            'list' => array( // 提问列表
                                'question_id' => 1, // 提问ID
                                'question_title' => '第一个提问', // 提问标题
                                'class_id' => 1, // 分类ID
                                'class_name' => '分类', // 分类名称
                                'username' => '张三', // 提问人姓名
                                'dp_name' => '技术部', // 提问人所属部门
                                'check_status' => 1, // 审核状态（1=未审核；2=审核通过；3=审核未通过）
                                'is_solve' => 1, // 解决状态（1=未解决；2=已解决）
                                'answer_pass_total' => 10, // 审核通过数
                                'answer_wait_total' => 10, // 等等审核数
                                'created' => 1491897290000, // 提问时间
                            )
                        )
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'page' => 'integer',
            'limit' => 'integer',
            'question_title' => 'max:20',
            'class_id' => 'integer',
            'username' => 'max:50',
            'dp_ids' => 'array',
            'check_status' => 'integer|in:1,2,3',
            'is_solve' => 'integer|in:1,2',
            'start_time' => 'integer',
            'end_time' => 'integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 分页默认值
        $postData['page'] = isset($postData['page']) ? $postData['page'] : Constant::PAGING_DEFAULT_PAGE;
        $postData['limit'] = isset($postData['limit']) ? $postData['limit'] : Constant::PAGING_DEFAULT_LIMIT;
        list($start, $perpage) = page_limit($postData['page'], $postData['limit']);

        // 条件
        $conds = [];
        if (isset($postData['question_title'])) {
            $conds['question_title like ?'] = '%' . $postData['question_title'] . '%';
        }
        if (isset($postData['class_id'])) {
            $conds['class_id'] = $postData['class_id'];
        }
        if (isset($postData['username'])) {
            $conds['username like ?'] = '%' . $postData['username'] . '%';
        }
        if (isset($postData['dp_ids'])) {
            // 取部门内所有人员，根据人员列表获取提问列表
            $userServ = &User::instance();
            $users = $userServ->listAll([
                'dpIdList' => $postData['dp_ids'],
                'departmentChildrenFlag' => 1,
            ]);
            if (!empty($users)) {
                $uids = array_column($users, 'memUid');
                $conds['uid'] = $uids;
            }
        }
        if (isset($postData['check_status'])) {
            $conds['check_status'] = $postData['check_status'];
        }
        if (isset($postData['is_solve'])) {
            $conds['is_solve'] = $postData['is_solve'];
        }
        if (isset($postData['start_time'])) {
            $conds['created >= ?'] = $postData['start_time'];
        }
        if (isset($postData['end_time'])) {
            $conds['created <= ?'] = $postData['end_time'];
        }

        // 排序
        $order_option = ['created' => 'desc'];

        // 提问列表
        $questionServ = new QuestionService();
        $list = $questionServ->list_by_conds($conds, [$start, $perpage], $order_option);
        if (!empty($list)) {
            // 部门信息
            $uids = array_column($list, 'uid');
            list($dpNames) = AnswerHelper::instance()->getUserInfo($uids);

            // 分类信息
            $classIds = array_column($list, 'class_id');
            $classServ = new ClassService();
            $classList = $classServ->list_by_conds(['class_id' => $classIds]);
            $classList = array_combine_by_key($classList, 'class_id');

            // 组合部门、分类名
            foreach ($list as $k => $v) {
                $list[$k]['dp_name'] = isset($dpNames[$v['uid']]) ? $dpNames[$v['uid']] : '';
                $list[$k]['class_name'] = isset($classList[$v['class_id']]) ? $classList[$v['class_id']]['class_name'] : '';
            }
        }

        // 数据总数量
        $total = $questionServ->count_by_conds($conds);

        // 待审核数量
        $waitConds = $conds;
        $waitConds['check_status'] = Constant::QUESTION_CHECK_STATUS_WAIT;
        $waitTotal = $questionServ->count_by_conds($waitConds);

        // 审核通过数量
        $passConds = $conds;
        $passConds['check_status'] = Constant::QUESTION_CHECK_STATUS_PASS;
        $passTotal = $questionServ->count_by_conds($passConds);

        // 未通过数量
        $failConds = $conds;
        $failConds['check_status'] = Constant::QUESTION_CHECK_STATUS_FAIL;
        $failTotal = $questionServ->count_by_conds($failConds);

        $this->_result = [
            'total' => $total,
            'wait_total' => $waitTotal,
            'pass_total' => $passTotal,
            'fail_total' => $failTotal,
            'list' => $list,
        ];
    }
}
