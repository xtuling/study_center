<?php
/**
 * DemoController.class.php
 * 演示测试接口
 * @author Deepseath
 * @version $Id$
 */
namespace Apicp\Controller\Recommender;

class DemoController extends AbstractController
{
    /**
     * 栏目接口模拟演示
     * @desc 栏目接口模拟演示
     * @return array(
     *  array(
     *      'id' => '分类 ID',
     *      'name' => '分类名称',
     *      'url' => '分类链接，如果为空，则表明该链接不可直接访问',
     *      'upId' => '上级分类 ID，为 0 则表示顶级'
     *  ),
     *  array()
     * )
     */
    public function iconApi()
    {

        return $this->_result = [
            [
                'id' => 1,
                'name' => '一级分类AA',
                'url' => 'http://a',
                'upId' => 0
            ],
            [
                'id' => 2,
                'name' => '一级分类BB',
                'url' => 'http://b',
                'upId' => 0
            ],
            [
                'id' => 3,
                'name' => '一级分类 CC',
                'url' => 'http://c',
                'upId' => 0,
            ],
            [
                'id' => 4,
                'name' => '二级分类AA',
                'url' => 'http://AA_AA',
                'upId' => 1,
            ],
            [
                'id' => 5,
                'name' => '二级分类 BB',
                'url' => 'http://AA_BB',
                'upId' => 1,
            ],
            [
                'id' => 6,
                'name' => '三级分类 AA',
                'url' => 'http://CCCA',
                'upId' => 5
            ],
            [
                'id' => 7,
                'name' => '四级分类EEE',
                'url' => 'http://aaa',
                'upId' => 6
            ]
        ];
    }

    /**
     * Banner 分类选择接口
     * @desc 用于首页 Banner 展示的接口模拟
     * @return array(
     *  array(
     *      'id' => '分类 ID',
     *      'name' => '分类名称',
     *      'upId' => '上级分类 ID，为 0 则表示顶级'
     *  ),
     *  array()
     * )
     */
    public function bannerApiCategoryUrl()
    {
        return $this->_result = [
            [
                'id' => 1,
                'name' => '一级分类.AA',
                'upId' => 0
            ],
            [
                'id' => 2,
                'name' => '一级分类.BB',
                'upId' => 0
            ],
            [
                'id' => 3,
                'name' => '一级分类. CC',
                'upId' => 0,
            ],
            [
                'id' => 4,
                'name' => '二级分类.AA',
                'upId' => 1,
            ],
            [
                'id' => 5,
                'name' => '二级分类 .BB',
                'upId' => 1,
            ],
            [
                'id' => 6,
                'name' => '三级分类 .AA',
                'upId' => 5
            ],
            [
                'id' => 7,
                'name' => '四级分类.DD',
                'upId' => 6
            ]
        ];
    }

    /**
     * Banner 文章列表接口模拟
     * @desc 用于首页 Banner 文章列表的接口模拟
     * @param integer categoryId:false 要列表的分类 Id，为空则请求全部
     * @param integer limit:false:15 每页显示的数据条数
     * @param integer page:false:1 当前请求的页码
     * @return array(
     *  array(
     *      'id' => '文章 ID',
     *      'subject' => '文章标题',
     *      'time' => '发表时间',
     *      'categoryName' => '所属分类名称',
     *      'categoryId' => '所属分类 ID',
     *      'author' => '作者名称',
     *  ),
     *  array()
     * )
     */
    public function bannerApiArticleListUrl()
    {
        // 当前请求的分类 ID，为空则请求全部
        $categoryId = I('categoryId', 0, 'intval');
        // 每页显示的条数
        $limit = I('limit', 15, 'intval');
        // 当前请求的页码
        $page = I('page', 1, 'intval');

        if ($limit < 0) {
            $limit = 15;
        }
        if ($page < 1) {
            $page = 1;
        }
        $total = 1001;

        $list = [];
        $_start = ($page - 1) * $limit;
        for ($i = 0; $i < $limit; $i++) {
            $randId = ($i + 1) + $_start;
            $list[] = [
                'id' => $randId,
                'subject' => '文章标题 ' . sprintf("%04s", $randId),
                'time' => time() - mt_rand(43200, 86400 * 7),
                'categoryName' => '分类名称' . mt_rand(1, 10),
                'categoryId' => !$categoryId ? mt_rand(1, 5) : $categoryId,
                'author' => '作者名称'
            ];
        }

        return $this->_result = [
            'limit' => $limit,
            'page' => $page,
            'total' => $total,
            'pages' => ceil($total/$limit),
            'categoryId' => $categoryId,
            'list' => $list
        ];
    }

    /**
     * Banner 搜索文章接口模拟
     * @desc 用于 Banner 选择器的文章搜索接口模拟
     * @param string kw:false 待搜索的关键词，为空则返回全部
     * @param integer categoryId:false:0 分类筛选
     * @param integer limit:false:15 每页显示的数据条数
     * @param integer page:false:1 当前请求的页码
     * @return array(
     *  array(
     *      'id' => '文章 ID',
     *      'subject' => '文章标题',
     *      'time' => '发表时间',
     *      'categoryName' => '所属分类名称',
     *      'categoryId' => '所属分类 ID',
     *      'author' => '作者名称',
     *  ),
     *  array()
     * )
     */
    public function bannerApiSearchUrl()
    {
        // 待搜索的关键词，为空返回全部
        $kw = I('kw', '');
        // 当前请求的分类 ID，为空则请求全部
        $categoryId = I('categoryId', 0, 'intval');
        // 每页显示的条数
        $limit = I('limit', 15, 'intval');
        // 当前请求的页码
        $page = I('page', 1, 'intval');

        if ($limit < 0) {
            $limit = 15;
        }
        if ($page < 1) {
            $page = 1;
        }
        $total = 1001;

        $list = [];
        $_start = ($page - 1) * $limit;
        for ($i = 0; $i < $limit; $i++) {
            $randId = ($i + 1) + $_start;
            $list[] = [
                'id' => $randId,
                'subject' => '文章标题 ' . sprintf("%04s", $randId),
                'time' => time() - mt_rand(43200, 86400 * 7),
                'categoryName' => '分类名称' . mt_rand(1, 10),
                'categoryId' => !$categoryId ? mt_rand(1, 5) : $categoryId,
                'author' => '作者名称'
            ];
        }

        return $this->_result = [
            'limit' => $limit,
            'page' => $page,
            'total' => $total,
            'pages' => ceil($total/$limit),
            'categoryId' => $categoryId,
            'kw' => $kw,
            'list' => $list
        ];
    }

    /**
     * 测试 RPC 连接可用
     * @desc 不需要请求参数
     */
    public function testRpc()
    {

        $param_arr = ['page'=>999, 'limit' => 10, 'as' => 9999];
        $this->_result = \Com\Rpc::phprpc('http://studycenter.dev/local/Public/Rpc/Recommender/ArticleNew')->invoke('Index', $param_arr);
    }
}
