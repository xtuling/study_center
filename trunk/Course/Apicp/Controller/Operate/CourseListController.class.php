<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/11
 * Time: 16:31
 */
namespace Apicp\Controller\Operate;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\ArticleService;
use Common\Service\ClassService;

class CourseListController extends \Apicp\Controller\AbstractController
{
    /**
     * Banner 课程列表接口
     * @desc 用于首页 Banner 课程列表的接口
     * @param integer categoryId:false 要列表的分类 Id，为空则请求全部
     * @param integer limit:false:20 每页显示的数据条数
     * @param integer page:false:1 当前请求的页码
     * @return array(
    array(
    'limit' => 20, // 每页显示的数据条数
    'page' => 1, // 当前请求的页码
    'total' => 20, // 数据总数
    'pages' => 1, // 页码总数
    'categoryId' => 1, // 当前请求的分类 ID
    'list' => array(
    'id' => '课程 ID', // 课程ID
    'subject' => '课程标题', // 课程标题
    'time' => '发表时间', // 发表时间
    'categoryName' => '所属分类名称', // 分类名称
    'categoryId' => '所属分类 ID', // 分类ID
    'author' => '作者名称', // 作者名称
    )
    );
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'categoryId' => 'integer',
            'limit' => 'integer',
            'page' => 'integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 默认值
        $postData['page'] = isset($postData['page']) ? $postData['page'] : Constant::PAGING_DEFAULT_PAGE;
        $postData['limit'] = isset($postData['limit']) ? $postData['limit'] : Constant::PAGING_DEFAULT_LIMIT;

        // 组合条件
        $conds = [];
        if (isset($postData['categoryId'])) {
            $conds['class_id'] = $postData['categoryId'];
        }

        // 分页
        list($start, $perpage) = page_limit($postData['page'], $postData['limit']);

        // 排序
        $order_option = ['update_time' => 'desc'];

        // 列表
        $articleServ = new ArticleService();
        $list = $articleServ->list_by_conds($conds, [$start, $perpage], $order_option);
        $resList = [];
        if (!empty($list)) {
            // 取所有分类，组合分类名称字段
            $classServ = new ClassService();
            $classList = $classServ->list_all();
            if (!empty($classList)) {
                $classList = array_combine_by_key($classList, 'class_id');
            }
            foreach ($list as $v) {
                $resList[] = [
                    'id' => $v['article_id'],
                    'subject' => $v['article_title'],
                    'time' => $v['update_time'],
                    'categoryName' => isset($classList[$v['class_id']]) ? $classList[$v['class_id']]['class_name'] : '',
                    'categoryId' => $v['class_id'],
                    'author' => $v['ea_name'],
                ];
            }
        }

        // 数据总数
        $total = $articleServ->count_by_conds($conds);

        $this->_result = [
            'limit' => $postData['limit'],
            'page' => $postData['page'],
            'total' => intval($total),
            'pages' => ceil($total/$postData['limit']),
            'categoryId' => isset($postData['categoryId']) ? $postData['categoryId'] : 0,
            'list' => $resList,
        ];
    }
}
