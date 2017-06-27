<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/13
 * Time: 11:48
 */
namespace Apicp\Controller\Course;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Common\User;
use Common\Common\ArticleHelper;
use Common\Service\ArticleService;
use Common\Service\StudyService;

class StudyListController extends \Apicp\Controller\AbstractController
{
    /**
     * StudyList
     * @author liyifei
     * @desc 已学/未学人员列表
     * @param Int article_id:true 课程ID
     * @param Int study_type:true 学习类型（1=未学，2=已学）
     * @param Int page:1 当前页
     * @param Int limit:20 每页数据总数
     * @return array 学习人员列表
                   array(
                        'study_type' => 1, // 学习类型（1=未学，2=已学）
                        'article_id' => 123, // 素材ID
                        'article_title' => '哈哈哈哈', // 新闻标题
                        'update_time' => 1491897290000, // 更新时间
                        'is_exam' => 1, // 是否开启测评（1=未开启；2=已开启）
                        'study_total' => 10, // 已学总数（学习类型为已学时,该参数可作为total使用）
                        'unstudy_total' => 9, // 未学总数（学习类型为未学时,该参数可作为total使用）
                        'total' => 30, // 数据总数
                        'page' => 1, // 页码
                        'limit' => 20, // 每页数据条数
                        'list' => array( // 列表数据
                            array(
                                'uid' => 'B4B3B9D17F00000173E870DA9A855AE7', // 人员UID
                                'username' => '张三', // 姓名
                                'dp_name' => array('技术部','后端'), // 所属部门
                                'job' => 'PHP', // 职位
                                'mobile' => '15821392414', // 手机号码
                                'is_pass' => '', // 测评是否通过（空=未测评；1=未通过；2=已通过）
                                'created' => 1491897290000, // 学习时间
                            ),
                        )
                   )
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'page' => 'integer',
            'limit' => 'integer',
            'article_id' => 'require|integer',
            'study_type' => 'require|integer|between:1,2',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 课程详情
        $articleServ = new ArticleService();
        $article = $articleServ->get($postData['article_id']);
        if (empty($article)) {
            E('_ERR_ARTICLE_DATA_NOT_FOUND');
        }

        // 课程可学、已学、未学人员UID
        $articleHelper = &ArticleHelper::instance();
        list($uids_all, $uids_study, $uids_unstudy) = $articleHelper->getStudyData($article['article_id']);

        // 已学总数
        $study_total = count($uids_study);

        // 未学总数
        $unstudy_total = count($uids_unstudy);

        // 已学人员列表
        if ($postData['study_type'] == Constant::ARTICLE_IS_STUDY_TRUE) {
            // 分页默认值
            $postData['page'] = isset($postData['page']) ? $postData['page'] : Constant::PAGING_DEFAULT_PAGE;
            $postData['limit'] = isset($postData['limit']) ? $postData['limit'] : Constant::PAGING_DEFAULT_LIMIT;
            list($start, $perpage) = page_limit($postData['page'], $postData['limit']);

            // 条件
            $conds = [
                'article_id' => $postData['article_id'],
            ];

            // 排序
            $order_option = ['created' => 'desc'];

            $studyServ = new StudyService();
            $list = $studyServ->list_by_conds($conds, [$start, $perpage], $order_option);
            $data = $this->_fixStudyList($list);

        // 未学人员列表（'memUids'为空时UC默认捞全公司的数据）
        } elseif (!empty($uids_unstudy)) {
            // 用UC方法进行分页
            $userServ = &User::instance();
            $users = $userServ->listByConds(['memUids' => $uids_unstudy], $postData['page'], $postData['limit']);
            $data = $this->_fixUnstudyList($users);
        }

        $this->_result = [
            'study_type' => $postData['study_type'],
            'article_id' => $postData['article_id'],
            'article_title' => $article['article_title'],
            'update_time' => $article['update_time'],
            'study_total' => $study_total,
            'unstudy_total' => $unstudy_total,
            'total' => $postData['study_type'] == Constant::ARTICLE_IS_STUDY_TRUE ? $study_total : $unstudy_total,
            'page' => $postData['page'],
            'limit' => $postData['limit'],
            'list' => $data,
        ];
    }

    /**
     * 补全已学人员信息
     * @author liyifei
     * @param array $list 已学人员列表(数据库读取的列表)
     * @return array
     */
    private function _fixStudyList($list)
    {
        if (empty($list) || !is_array($list)) {
            return [];
        }
        $uids = array_column($list, 'uid');

        $data = [];
        $userServ = &User::instance();
        $users = $userServ->listByUid($uids);

        foreach ($list as $v) {
            $uid = $v['uid'];
            if (!isset($users[$uid])) {
                continue;
            }

            $data[] = [
                'uid' => $uid,
                'username' => $users[$uid]['memUsername'],
                'dp_name' => array_column($users[$uid]['dpName'], 'dpName'),
                'job' => $users[$uid]['memJob'],
                'mobile' => $users[$uid]['memMobile'],
                // TODO liyifei 2017-05-03 16:59:14 测评应用完成后,补全用户测评是否通过
                'is_pass' => '',
                'created' => isset($v['created']) ? $v['created'] : '',
            ];
        }

        return $data;
    }

    /**
     * 补全已学人员信息
     * @author liyifei
     * @param array $users 未学人员列表(UC返回的数据列表)
     * @return array
     */
    private function _fixUnstudyList($users)
    {
        if (!isset($users['list']) || !is_array($users['list']) || empty($users['list'])) {
            return [];
        }

        $data = [];
        foreach ($users['list'] as $user) {
            $data[] = [
                'uid' => $user['memUid'],
                'username' => $user['memUsername'],
                'dp_name' => array_column($user['dpName'], 'dpName'),
                'job' => $user['memJob'],
                'mobile' => $user['memMobile'],
                // TODO liyifei 2017-05-03 16:59:14 测评应用完成后,补全用户测评是否通过
                'is_pass' => '',
                'created' => '',
            ];
        }

        return $data;
    }
}
