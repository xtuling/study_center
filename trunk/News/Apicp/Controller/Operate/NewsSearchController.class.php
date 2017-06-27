<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Apicp\Controller\Operate;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\ArticleService;
use Common\Service\ClassService;

class NewsSearchController extends \Apicp\Controller\AbstractController
{
    /**
     * Banner 搜索新闻接口
     * @desc 用于 Banner 选择器的新闻搜索接口
     * @param string kw:false 待搜索的关键词，为空则返回全部
     * @param integer categoryId:false 分类筛选
     * @param integer limit:false:15 每页显示的数据条数
     * @param integer page:false:1 当前请求的页码
     * @return array(
                array(
                    'limit' => 20, // 每页显示的数据条数
                    'page' => 1, // 当前请求的页码
                    'total' => 20, // 数据总数
                    'pages' => 1, // 页码总数
                    'categoryId' => 1, // 当前请求的分类 ID
                    'list' => array(
                        'id' => '新闻 ID', // 新闻ID
                        'subject' => '新闻标题', // 新闻标题
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
            'kw' => 'max:120',
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
        $conds = ['news_status' => Constant::NEWS_STATUS_SEND];
        if (isset($postData['categoryId'])) {
            $conds['class_id'] = $postData['categoryId'];
        }
        if (isset($postData['kw'])) {
            $conds['title like ?'] = '%' . $postData['kw'] . '%';
        }

        // 分页
        list($start, $perpage) = page_limit($postData['page'], $postData['limit']);

        // 排序
        $order_option = ['top_time' => 'desc', 'send_time' => 'desc'];

        // 列表
        $articleServ = new ArticleService();
        $list = $articleServ->list_by_conds($conds, [$start, $perpage], $order_option);
        $resList = [];
        if (!empty($list)) {
            // 取分类
            $classServ = new ClassService();
            $classList = $classServ->list_all();
            if (!empty($classList)) {
                $classList = array_combine_by_key($classList, 'class_id');
            }

            // 格式化数据
            foreach ($list as $v) {
                $resList[] = [
                    'id' => $v['article_id'],
                    'subject' => $v['title'],
                    'time' => $v['send_time'],
                    'categoryName' => $v['class_name'],
                    'categoryId' => $v['class_id'],
                    'author' => $v['author'],
                    'attachId' => $v['cover_id'],
                    'url' => 'News/Frontend/Index/Detail/Index?article_id=' . $v['article_id'],
                    'category' => isset($classList) ? $this->_getCategory($v['class_id'], $classList) : [],
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

    /**
     * @desc 根据分类ID取出当前分类以及父级分类信息
     * @author tangxingguo
     * @param int $classId 分类ID
     * @param array $classList 分类列表
     * @return array
     */
    private function _getCategory($classId, $classList)
    {
        $category = [];
        while (isset($classList[$classId]) && $classId != 0) {
            $category[] = [
                'id' => $classId,
                'name' => $classList[$classId]['class_name'],
                'upId' => $classList[$classId]['parent_id'],
                'url' => $classList[$classId]['parent_id'] == 0 ? frontUrl('/app/page/news/list/list', ['class_id' => $classId]) : '',
            ];
            $classId = $classList[$classId]['parent_id'];
        }
        return $category;
    }
}
