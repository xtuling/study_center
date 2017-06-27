<?php
/**
 * BannerListController.class.php
 * 【运营管理】管理后台条幅列表接口
 * @author Deepseath
 * @version $Id$
 */
namespace Apicp\Controller\Recommender;

use Common\Service\CommonRecommenderService;
use Common\Model\CommonRecommenderModel;

/**
 * 【管理后台】 条幅列表接口
 */
class BannerListController extends AbstractController
{

    /**
     * 条幅列表接口
     * @desc 【管理后台】 条幅列表接口
     * @param integer page:false:1 请求的页码
     * @param integer limit:false:15 每页显示的数据条数
     * @return <pre>
     * array(
     *  'total' => 4,// 数据总数
     *  'pages' => 1,// 分页总数
     *  'page' => 1,// 当前页码
     *  'limit' => 15,// 每页显示数据条数
     *  'list' => array(
     *      array(
     *          'id' => 1,//  条幅 ID
     *          'hide' => 1,// 是否隐藏，1=显示；2=隐藏
     *          'system' => 1,// 是否系统内置，1=是；2=否
     *          'title' => '条幅 名称',//  条幅 名称
     *          'attachId' => '1234567890123456789012',//  条幅 附件ID
     *          'pic' => 'http://',//  条幅 图片地址
     *          'url' => 'http://',// 手机端链接 URL
     *          'dataCategoryId' => 9,//  条幅 关联的应用分类 ID
     *          'appName' => '',// 所属的应用名称
     *          'dataId' => 10,// 条幅关联的文章 ID
     *          'dateline' => 123456789012,//  条幅 关联设置时间
     *          'adminer' => 'admin',// 操作者名称（管理员名）
     *          'data' => array(
     *              'category' => array(
     *                  array(
     *                      "id" => "1",// 关联的应用一级分类 ID
     *                      "name" => "一级分类",// 关联的应用分类名称
     *                      "upId" => "0",// 关联的应用分类的上级分类 ID
     *                      "url" => "http://"// 关联的应用手机端访问 URL
     *                  )
     *              ),
     *              'article' => array(
     *              )
     *          )
     *      ),
     *      array()
     *  )
     * )
     * </pre>
     */
    public function Index()
    {
        // 当前请求的页码
        $page = I('page', 1, 'intval');
        // 当前请求显示的数据条数
        $limit = I('limit', 15, 'intval');
        // 检查页码和每页显示数
        if ($page < 1) {
            $page = 1;
        }
        if ($limit < 1) {
            $limit = 1;
        }

        $recommenderService = new CommonRecommenderService();

        // 获取 条幅 数据
        $conds = [
            'type' => CommonRecommenderModel::TYPE_BANNER
        ];

        // 计算总数
        $total = $recommenderService->count_by_conds($conds, 'recommender_id');
        // 计算总页码和当前请求的首行
        $pages = ceil($total / $limit);
        $start = ($page - 1) * $limit;
        // 初始化待返回的数据列表数据
        $list = [];

        if ($total > 0) {
            // 存在数据时进行列表查询
            $datas = $recommenderService->list_by_conds($conds, [
                $start,
                $limit
            ], [
                'displayorder' => 'DESC'
            ]);
            foreach ((array) $datas as $data) {
                $list[] = $recommenderService->BannerDataFormat($data);
            }
            unset($datas, $data);
        }

        return $this->_result = [
            'total' => $total,
            'pages' => $pages,
            'page' => $page,
            'limit' => $limit,
            'list' => $list
        ];
    }


}
