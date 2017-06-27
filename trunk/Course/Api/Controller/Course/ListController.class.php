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
use Common\Service\ClassService;
use Common\Service\RightService;
use Common\Service\ArticleService;
use Common\Service\StudyService;

class ListController extends \Api\Controller\AbstractController
{
    /**
     * List
     * @author liyifei
     * @desc 课程列表接口
     * @param Int page:false:1 页码
     * @param Int limit:false:20 每页记录数
     * @param Int class_id:false:0 分类ID（最新,默认为0）
     * @param String keyword 搜索关键字
     * @return array|bool 新闻列表
                   array(
                        'page' => 1, // 页码
                        'limit' => 20, // 每页记录数
                        'total' => 200, // 记录总数
                        'class_id' => 21, // 分类ID（最新,默认为0）
                        'list' => array(
                            array(
                                'article_id' => 12, // 课程ID
                                'article_title' => '冰糖雪梨', // 课程标题
                                'article_type' => 1, // 课程类型（1=单课程；2=系列课程）
                                'source_type' => 1, // 素材类型（1=图文素材；2=音图素材；3=视频素材；4=文件素材；5=外部素材）
                                'cover_id' => 'b3ddbc502e307665f346cbd6e52cc10d', // 封面ID
                                'cover_url' => 'http://qy.vchangyi.org', // 封面图片URL
                                'update_time' => 1491880545000, // 最后更新时间（毫秒级时间戳）
                                'study_total' => 10, // 已学习人数
                                'my_is_study' => 1, // 我是否已学（1=未学；2=已学）
                            ),
                        ),
                   )
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'page' => 'integer',
            'limit' => 'integer',
            'class_id' => 'integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 登录人员
        $user = $this->_login->user;

        // 获取人员可学习的课程ID列表
        $rightServ = new RightService();
        $article_ids = $rightServ->listByRight($user, 'article_id');

        // 处理课程数据(搜索、分页、排序)
        if (!empty($article_ids)) {
            // 默认值
            $postData['keyword'] = I('post.keyword', '', 'trim');
            $postData['class_id'] = isset($postData['class_id']) ? $postData['class_id'] : 0;
            $postData['page'] = isset($postData['page']) ? $postData['page'] : Constant::PAGING_DEFAULT_PAGE;
            $postData['limit'] = isset($postData['limit']) ? $postData['limit'] : Constant::PAGING_DEFAULT_LIMIT;
            list($start, $perpage) = page_limit($postData['page'], $postData['limit']);

            // 组合搜索条件
            $conds = [
                'article_id' => array_column($article_ids, 'article_id'),
                'article_status' => Constant::ARTICLE_STATUS_SEND,
            ];
            if (strlen($postData['keyword']) > 0) {
                $conds['article_title like ?'] = '%' . $postData['keyword'] . '%';
            }
            $classServ = new ClassService();
            if ($postData['class_id'] != 0) {
                // 无限级取出当前分类及所有已启用的子分类ID
                $classIds = $classServ->getChildClassIds($postData['class_id']);
                $conds['class_id'] = $classIds;
            } else {
                // 最新课程列表，排除被禁用的分类
                $isOpenClassIds = $classServ->getOpenClassIds();
                $conds['class_id'] = $isOpenClassIds;
            }

            // 排序条件
            $order_option = ['update_time' => 'desc'];

            // 课程列表
            $articleServ = new ArticleService();
            $list = $articleServ->list_by_conds($conds, [$start, $perpage], $order_option);
            if (!empty($list)) {
                $art_ids = array_column($list, 'article_id');

                // 我已学习的课程列表
                $studyServ = new StudyService();
                $study_list = $studyServ->list_by_conds([
                    'uid' => $user['memUid'],
                    'article_id' => $art_ids,
                ]);
                $study_art_ids = [];
                if (!empty($study_list)) {
                    $study_art_ids = array_column($study_list, 'article_id');
                }

                // 返回列表追加已学未学标识
                foreach ($list as $k => $v) {
                    $list[$k]['my_is_study'] = Constant::ARTICLE_IS_STUDY_FALSE;
                    if (in_array($v['article_id'], $study_art_ids)) {
                        $list[$k]['my_is_study'] = Constant::ARTICLE_IS_STUDY_TRUE;
                    }
                }
            }

            // 课程总数
            $total = $articleServ->count_by_conds($conds);
        }

        $this->_result = [
            'page' => $postData['page'],
            'limit' => $postData['limit'],
            'total' => isset($total) ? intval($total) : 0,
            'class_id' => $postData['class_id'],
            'list' => isset($list) ? $list : [],
        ];
    }
}
