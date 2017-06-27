<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/11
 * Time: 15:07
 */
namespace Apicp\Controller\News;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\ArticleService;
use Common\Service\ClassService;

class ListController extends \Apicp\Controller\AbstractController
{
    /**

     * List

     * @desc 新闻列表

     * @param Int page:1 当前页(默认第一页)

     * @param Int limit:20 当前页条数

     * @param string start_time 更新开始时间(毫秒级时间戳)

     * @param string end_time 更新结束时间(毫秒级时间戳)

     * @param string title 标题关键词

     * @param Int class_id 分类ID

     * @param Int news_status 发布状态（1=草稿;2=已发布;3=预发布）

     * @return array

     *               array(

     *                  'total' => 100, // 总条数

     *                  'page' => 1, // 当前页

     *                  'limit' => 20, // 当前页条数

     *                  'list' => array( // 列表数据

     *                      'article_id' => 1, // 新闻ID

     *                      'title' => '电商冲击,实体店靠什么赢', // 标题

     *                      'class_name' => '导购FM', // 栏目

     *                      'is_secret' => 1, // 是否保密（1=不保密，2=保密）

     *                      'read_total' => 1, // 已阅读人数

     *                      'allow_read_total' => 30, // 可阅读人数

     *                      'news_status' => 1, // 发布状态(新闻状态（1=草稿，2=已发布，3=预发布）)

     *                      'like_total' => 12, // 点赞人数

     *                      'comment_total' => 34, // 评论人数

     *                      'send_time' => '1491897290000', // 最后更新时间(毫秒级时间戳)

     *                      'top_time' => '1491897290000', // 置顶时间(毫秒级时间戳，0为未置顶)

     *                  ),

     *               )

     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'page' => 'integer',
            'limit' => 'integer',
            'start_time' => 'integer',
            'end_time' => 'integer',
            'title' => 'max:64',
            'class_id' => 'integer',
            'news_status' => 'integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 默认值
        $postData['page'] = isset($postData['page']) ? $postData['page'] : Constant::PAGING_DEFAULT_PAGE;
        $postData['limit'] = isset($postData['limit']) ? $postData['limit'] : Constant::PAGING_DEFAULT_LIMIT;

        // 组合条件
        $conds = [];
        if (isset($postData['start_time'])) {
            $conds['send_time > ?'] = $postData['start_time'];
        }
        if (isset($postData['end_time'])) {
            $conds['send_time < ?'] = $postData['end_time'];
        }
        if (isset($postData['title'])) {
            $conds['title like ?'] = '%' . $postData['title'] . '%';
        }
        if (isset($postData['class_id'])) {
            $classServ = new ClassService();
            $childList = $classServ->list_by_conds(['parent_id' => $postData['class_id']]);
            if ($childList) {
                $class_ids = array_column($childList, 'class_id');
                $conds['class_id in (?)'] = $class_ids;
            } else {
                $conds['class_id = ?'] = $postData['class_id'];
            }
        }
        if (isset($postData['news_status'])) {
            $conds['news_status = ?'] = $postData['news_status'];
        }

        // 分页
        list($start, $perpage) = page_limit($postData['page'], $postData['limit']);

        // 排序
        $order_option = ['top_time' => 'desc', 'send_time' => 'desc'];

        // 列表
        $articleServ = new ArticleService();
        $list = $articleServ->list_by_conds($conds, [$start, $perpage], $order_option);
        if ($list) {
            // 可阅读人数
            foreach ($list as $k => $v) {
                $list[$k]['allow_read_total'] = $v['read_total'] + $v['unread_total'];
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
