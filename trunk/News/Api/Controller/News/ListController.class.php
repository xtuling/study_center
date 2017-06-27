<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 10:40
 */
namespace Api\Controller\News;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\ArticleService;
use Common\Service\ClassService;
use Common\Service\ReadService;
use Common\Service\RightService;

class ListController extends \Api\Controller\AbstractController
{
   /**
    * List
    * @author tangxingguo
    * @desc 新闻列表
    * @param int page:false:1 页码
    * @param int limit:false:20 每页记录数
    * @param int class_id 新闻分类ID
    * @return array 新闻列表
    *              array(
                       'page' => 1, // 页码
                       'limit' => 20, // 每页记录数
                       'total' => 200, // 记录总数
                       'class_id' => 21, // 新闻分类ID
                       'list' => array(
                           array(
                               'article_id' => 12, // 新闻ID
                               'title' => '冰糖雪梨', // 新闻标题
                               'cover_id' => 'b3ddbc502e307665f346cbd6e52cc10d', // 封面ID
                               'cover_url' => 'http://qy.vchangyi.org', // 封面图片URL
                               'top_time' => 0, // 置顶时间（0为未置顶，毫秒级时间戳）
                               'send_time' => 1491880545000, // 发送、编辑时间（毫秒级时间戳）
                               'is_read' => 1, // 是否已读（1=否，2=是）
                           ),
                       ),
                   );
    */
    public function Index_post()
    {
        $user = $this->_login->user;

        // 验证规则
        $rules = [
            'class_id' => 'integer',
            'limit' => 'integer',
            'page' => 'integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 默认值
        $postData['page'] = isset($postData['page']) ? $postData['page'] : Constant::PAGING_DEFAULT_PAGE;
        $postData['limit'] = isset($postData['limit']) ? $postData['limit'] : Constant::PAGING_DEFAULT_LIMIT;

        // 全公司对应的新闻ID
        $rightConds = ['obj_type' => Constant::RIGHT_TYPE_ALL];
        $rightServ = new RightService();
        $rightInfo = $rightServ->list_by_conds($rightConds);
        $articleIds = array_column($rightInfo, 'article_id');

        // 人员、部门、标签、职位、角色对应的新闻ID
        $rightData = $rightServ->getUserRight($user);
        $right = $rightData[Constant::RIGHT_TYPE_USER];
        if (isset($rightData[Constant::RIGHT_TYPE_DEPARTMENT])) {
            $right = array_merge($right, $rightData[Constant::RIGHT_TYPE_DEPARTMENT]);
        }
        if (isset($rightData[Constant::RIGHT_TYPE_TAG])) {
            $right = array_merge($right, $rightData[Constant::RIGHT_TYPE_TAG]);
        }
        if (isset($rightData[Constant::RIGHT_TYPE_JOB])) {
            $right = array_merge($right, $rightData[Constant::RIGHT_TYPE_JOB]);
        }
        if (isset($rightData[Constant::RIGHT_TYPE_ROLE])) {
            $right = array_merge($right, $rightData[Constant::RIGHT_TYPE_ROLE]);
        }
        $rightConds = ['obj_id in (?)' => array_values($right)];
        $rightInfo = $rightServ->list_by_conds($rightConds);
        // 合并、去重新闻ID
        $articleIds = array_merge($articleIds, array_column($rightInfo, 'article_id'));
        $articleIds = array_values(array_unique($articleIds));

        // 条件
        $conds = ['news_status' => Constant::NEWS_STATUS_SEND];
        if ($articleIds) {
            $conds['article_id in (?)'] = $articleIds;
        }
        $classServ = new ClassService();
        if (isset($postData['class_id']) && $postData['class_id'] > 0) {
            $childList = $classServ->list_by_conds(['parent_id' => $postData['class_id'], 'is_open' => Constant::CLASS_IS_OPEN_TRUE]);
            if ($childList) {
                // 分类内有子分类
                $class_ids = array_column($childList, 'class_id');
                $conds['class_id in (?)'] = $class_ids;
            } else {
                // 分类是否启用
                $classCount = $classServ->count_by_conds(['class_id' => $postData['class_id'], 'is_open' => Constant::CLASS_IS_OPEN_TRUE]);
                $conds['class_id = ?'] = ($classCount > 0) ? $postData['class_id'] : 0;
            }
        } else {
            $childList = $classServ->list_by_conds(['is_open' => Constant::CLASS_IS_OPEN_TRUE]);
            if ($childList) {
                $class_ids = array_column($childList, 'class_id');
                $conds['class_id in (?)'] = $class_ids;
            }
        }

        // 分页
        list($start, $perpage) = page_limit($postData['page'], $postData['limit']);

        // 排序
        $order_option = ['top_time' => 'desc', 'send_time' => 'desc'];

        // 总数量
        $articleServ = new ArticleService();
        $total = $articleServ->count_by_conds($conds);

        // 列表
        $list = [];
        $readServ = new ReadService();
        $newsList = $articleServ->list_by_conds($conds, [$start, $perpage], $order_option);
        if ($newsList) {
            // 取是否已读
            $article_ids = array_column($newsList, 'article_id');
            $readList = $readServ->list_by_conds(['article_id in (?)' => $article_ids, 'uid' => $user['memUid']]);
            if ($readList) {
                $readList = array_combine_by_key($readList, 'article_id');
            }
            foreach ($newsList as $k => $v) {
                $newsList[$k]['is_read'] = isset($readList[$v['article_id']]) ? Constant::READ_STATUS_IS_YES : Constant::READ_STATUS_IS_NO;
            }
            $list = $newsList;
        }

        $result = [
            'total' => intval($total),
            'list' => $list,
        ];
        $this->_result = array_merge($postData, $result);
    }
}
