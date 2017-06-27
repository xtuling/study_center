<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/27
 * Time: 11:41
 */
namespace Apicp\Controller\Course;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\ClassService;
use Common\Service\ArticleService;

class ListController extends \Apicp\Controller\AbstractController
{
    /**
     * List
     * @author liyifei
     * @desc 课程列表
     * @param Int page:1 当前页
     * @param Int limit:20 每页数据总数
     * @param Int article_status 课程状态（1=草稿；2=已发布）
     * @param String article_title 标题
     * @param String ea_name 创建人姓名关键字
     * @param Int class_id 课程分类ID
     * @param Int cm_id 能力模型ID
     * @param Int is_exam 是否闯关
     * @param String start_time 更新开始时间（毫秒级时间戳）
     * @param String end_time 更新结束时间（毫秒级时间戳）
     * @return array
                    array(
                        'page' => 1, // 当前页
                        'limit' => 20, // 当前页条数
                        'total' => 100, // 总条数
                        'list' => array( // 列表数据
                            'article_id' => 1, // 课程ID
                            'article_title' => '电商冲击,实体店靠什么赢', // 标题
                            'class_name' => '导购FM', // 分类
                            'is_exam' => 1, // 是否闯关（1=不闯关；2=闯关）
                            'study_total' => 1, // 已学人数
                            'allow_study_total' => 30, // 需学习总人数
                            'article_status' => 1, // 课程状态（1=草稿；2=已发布）
                            'like_total' => 12, // 点赞人数
                            'comment_total' => 34, // 评论人数
                            'exam_total' => 34, // 测评题数
                            'update_time' => '1491897290000', // 最后更新时间（毫秒级时间戳）
                        ),
                    ),
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'page' => 'integer',
            'limit' => 'integer',
            'article_status' => 'integer|in:1,2',
            'article_title' => 'max:64',
            'ea_name' => 'max:50',
            'class_id' => 'integer',
            'cm_id' => 'integer',
            'is_exam' => 'integer|in:1,2',
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

        // 组合搜索条件
        $conds = [];
        if (isset($postData['article_status'])) {
            $conds['article_status'] = $postData['article_status'];
        }
        if (isset($postData['article_title'])) {
            $conds['article_title like ?'] = '%' . $postData['article_title'] . '%';
        }
        if (isset($postData['ea_name'])) {
            $conds['ea_name like ?'] = '%' . $postData['ea_name'] . '%';
        }
        $classServ = new ClassService();
        if (isset($postData['class_id'])) {
            // 无限级取出当前分类及所有已启用的子分类ID
            $classIds = $classServ->getChildClassIds($postData['class_id']);
            $conds['class_id'] = $classIds;
        } else {
            // 所有分类的课程列表，排除被禁用的分类
            $isOpenClassIds = $classServ->getOpenClassIds();
            $conds['class_id'] = $isOpenClassIds;
        }
        if (isset($postData['cm_id'])) {
            $conds['cm_id'] = $postData['cm_id'];
        }
        if (isset($postData['is_exam'])) {
            $conds['is_exam'] = $postData['is_exam'];
        }
        if (isset($postData['start_time'])) {
            $conds['update_time >= ?'] = $postData['start_time'];
        }
        if (isset($postData['end_time'])) {
            $conds['update_time <= ?'] = $postData['end_time'];
        }

        // 排序
        $order_option = ['update_time' => 'desc'];

        // 课程列表
        $articleServ = new ArticleService();
        $list = $articleServ->list_by_conds($conds, [$start, $perpage], $order_option);

        // 分类名称
        if (!empty($list)) {
            $class_ids = array_column($list, 'class_id');
            $classList = $classServ->list_by_conds(['class_id' => $class_ids]);
            $classNames = [];
            if (!empty($classList)) {
                $classNames = array_combine_by_key($classList, 'class_id');
            }

            $url = convertUrl(QY_DOMAIN . '/Contact/Rpc/Competence/Detail');

            foreach ($list as $k => $v) {
                $class_id = $v['class_id'];
                $list[$k]['class_name'] = isset($classNames[$class_id]['class_name']) ? $classNames[$class_id]['class_name'] : '';
                $list[$k]['allow_study_total'] = $v['study_total'] + $v['unstudy_total'];
                $list[$k]['exam_total'] = !empty($v['et_ids']) ? count(unserialize($v['et_ids'])) : 0;

                // RPC取能力模型信息
                $data = [
                    'cm_id' => $v['cm_id'],
                ];
                $detail = \Com\Rpc::phprpc($url)->invoke('index', $data);
                if (!empty($detail) && is_array($detail)) {
                    $list[$k]['cm_name'] = $detail['cm_name'];
                } else {
                    $list[$k]['cm_name'] = '';
                }
            }
        }

        // 数据总数
        $total = $articleServ->count_by_conds($conds);

        $this->_result = [
            'page' => $postData['page'],
            'limit' => $postData['limit'],
            'total' => intval($total),
            'list' => $list,
        ];
    }
}
