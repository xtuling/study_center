<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/5/5
 * Time: 10:23
 */

namespace Api\Controller\Course;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\ArticleService;
use Common\Service\StudyService;
use Common\Service\UserService;

class MyStudyController extends \Api\Controller\AbstractController
{
    /**
     * MyStudy
     * @author tangxingguo
     * @desc 我的学习数据接口
     * @param int    page:false:1 页码
     * @param int    limit:false:20 每页记录数
     * @return array 我的学习数据
                array(
                    'study_total' => 10, // 累计课程数
                    'time_total' => 100, // 累计学习时长
                    'ranking' => 11, // 排名
                    'page' => 1, // 当前页
                    'limit' => 20, // 当前页条数
                    'study_list' => array( // 已学课程
                        'article_id' => 12, // 课程ID
                        'article_title' => '基础护理', // 课程名称
                        'cover_id' => 'A82DA38B7F0000013E9C84963F706B3A', // 封面图片ID
                        'cover_url' => 'http://t-rep.vchangyi.com/image/20170426/f455f3ee-90ea-4059-b69e-3a7e9da584e4.gif?atId=A82DA38B7F0000013E9C84963F706B3A', // 封面图片URL
                        'update_time' => 1493709035000, // 课程编辑时间
                        'study_total' => 123, // 已学习人数
                        'study_time' => 1493709035000, // 课程学习时间
                        'source_type' => 1, // 素材类型（1=图文素材；2=音图素材；3=视频素材；4=文件素材；5=外部素材）
                    ),
                );
     */

    public function Index_post()
    {
        $uid = $this->_login->user['memUid'];
        // 验证规则
        $rules = [
            'limit' => 'integer',
            'page' => 'integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 默认值
        $postData['page'] = isset($postData['page']) ? $postData['page'] : Constant::PAGING_DEFAULT_PAGE;
        $postData['limit'] = isset($postData['limit']) ? $postData['limit'] : Constant::PAGING_DEFAULT_LIMIT;

        $userServ = new UserService();
        $studyServ = new StudyService();
        $articleServ = new ArticleService();
        $studyInfo = $userServ->get_by_conds(['uid' => $uid]);
        if ($studyInfo) {
            // 累计学习时长
            $time_total = $studyInfo['time_total'];
            // 排名
            $count = $userServ->count_by_conds(['time_total > ?' => $studyInfo['time_total']]);
            $ranking = $count + 1;
        }

        // 已学课程列表
        $conds = ['uid' => $uid];
        list($start, $perpage) = page_limit($postData['page'], $postData['limit']);
        $order_option = ['created' => 'desc'];
        $studyList = $studyServ->list_by_conds($conds, [$start, $perpage], $order_option);
        $list = [];
        if ($studyList) {
            // 取课程ID
            $articleIds = array_column($studyList, 'article_id');

            // 合并数据 + 排序
            $studyList = array_combine_by_key($studyList, 'article_id');
            $articleList = $articleServ->list_by_conds(['article_id in (?)' => $articleIds]);
            if (!empty($articleList)) {
                $articleList = array_combine_by_key($articleList, 'article_id');
                foreach ($articleIds as $v) {
                    if (isset($articleList[$v])) {
                        $articleList[$v]['study_time'] = $studyList[$v]['created'];
                        $articleList[$v]['created'] = $studyList[$v]['created'];
                        $articleList[$v]['update_time'] = $studyList[$v]['created'];
                        $list[] = $articleList[$v];
                    }
                }
            }
        }

        $study_total = $studyServ->count_by_conds($conds);

        $this->_result = [
            'study_total' => empty($study_total) ? 0 : $study_total,
            'time_total' => isset($time_total) ? $time_total : 0,
            'ranking' => isset($ranking) ? $ranking : 0,
            'page' => $postData['page'],
            'limit' => $postData['limit'],
            'study_list' => $list,
        ];
    }
}
