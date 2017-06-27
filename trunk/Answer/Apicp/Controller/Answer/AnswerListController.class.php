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
use Common\Service\AnswerService;
use Common\Service\ImgService;

class AnswerListController extends \Apicp\Controller\AbstractController
{
    /**
     * AnswerList
     * @author
     * @desc 回答列表
     * @param Int page:1 当前页
     * @param Int limit:20 每页数据总数
     * @param Int question_id:true 提问ID
     * @param Int check_status:true 审核状态（1=未审核；2=审核通过；3=审核未通过）
     * @return array 回答列表
                    array(
                        'total' => 1, // 当前数据总数
                        'wait_total' => 1, // 未审批回答数
                        'pass_total' => 1, // 已通过回答数
                        'fail_total' => 1, // 未通过回答数
                        'list' => array(
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
                            'is_best' => 1, // 是否是最佳答案（1=否；2=是）
                            'like_total' => 10, // 点赞总数
                            'check_status' => 1, // 审核状态（1=未审核；2=审核通过；3=审核未通过）
                            'checker_name' => '李四', // 审核人姓名
                            'checker_dps' => '技术部', // 审核人部门
                            'checker_type' => 1, // 审核人类型（1=用户；2=管理员）
                            'check_time' => 1234567890, // 审核时间
                        )
                    );
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'page' => 'integer',
            'limit' => 'integer',
            'question_id' => 'require|integer',
            'check_status' => 'require|integer|in:1,2,3',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 分页默认值
        $postData['page'] = isset($postData['page']) ? $postData['page'] : Constant::PAGING_DEFAULT_PAGE;
        $postData['limit'] = isset($postData['limit']) ? $postData['limit'] : Constant::PAGING_DEFAULT_LIMIT;
        list($start, $perpage) = page_limit($postData['page'], $postData['limit']);

        // 条件
        $conds = [
            'question_id' => $postData['question_id'],
            'check_status' => $postData['check_status'],
        ];

        // 排序（最佳答案 > 点赞数 > 审批时间）
        $order_option = ['is_best' => 'desc', 'like_total' => 'desc', 'check_time' => 'desc'];

        // 问题列表
        $answerServ = new AnswerService();
        $list = $answerServ->list_by_conds($conds, [$start, $perpage], $order_option);
        if (!empty($list)) {
            // 部门、头像
            $uids = array_column($list, 'uid');
            list($dpNames, $faceList) = AnswerHelper::instance()->getUserInfo($uids);

            // 图片
            $answerIds = array_column($list, 'answer_id');
            $imgServ = new ImgService();
            $imgList = $imgServ->list_by_conds(['answer_id' => $answerIds]);

            // 组合部门、头像、图片
            foreach ($list as $k => $v) {
                $list[$k]['face'] = isset($faceList[$v['uid']]) ? $faceList[$v['uid']] : '';
                $list[$k]['checker_dps'] = isset($dpNames[$v['uid']]) ? $dpNames[$v['uid']] : '';
                foreach ($imgList as $imgkey => $imgInfo) {
                    if ($imgInfo['uid'] == $v['uid']) {
                        $list[$k]['imgs'][] = $imgInfo;
                        unset($imgList[$imgkey]);
                    }
                }
            }
        }

        // 各个状态的回答总数
        $count = [];
        // 待审核回答数
        $conds['check_status'] = Constant::ANSWER_CHECK_STATUS_WAIT;
        $count[Constant::ANSWER_CHECK_STATUS_WAIT] = $answerServ->count_by_conds($conds);
        // 审核通过回答数
        $conds['check_status'] = Constant::ANSWER_CHECK_STATUS_PASS;
        $count[Constant::ANSWER_CHECK_STATUS_PASS] = $answerServ->count_by_conds($conds);
        // 审核未通过回答数
        $conds['check_status'] = Constant::ANSWER_CHECK_STATUS_FAIL;
        $count[Constant::ANSWER_CHECK_STATUS_FAIL] = $answerServ->count_by_conds($conds);

        $this->_result = [
            'total' => $count[$postData['check_status']],
            'wait_total' => $count[Constant::ANSWER_CHECK_STATUS_WAIT],
            'pass_total' => $count[Constant::ANSWER_CHECK_STATUS_PASS],
            'fail_total' => $count[Constant::ANSWER_CHECK_STATUS_FAIL],
            'list' => $list,
        ];
    }
}
