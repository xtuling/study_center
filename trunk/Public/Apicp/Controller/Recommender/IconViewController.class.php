<?php
/**
 * IconViewController.class.php
 * 【运营管理】管理后台栏目信息查看接口
 * @author Deepseath
 * @version $Id$
 */
namespace Apicp\Controller\Recommender;

use Common\Service\CommonRecommenderService;

/**
 * 【管理后台】栏目查看接口
 */
class IconViewController extends AbstractController
{

    /**
     * 栏目查看接口
     * @desc 【管理后台】栏目查看接口
     * @param integer recommenderId:true 待读取的栏目 ID
     * @return <pre>
     * array(
     *    'id' => 1,// 栏目ID
     *    'hide' => 1,// 是否隐藏，1=显示；2=隐藏
     *    'system' => 1,// 是否系统内置，1=是；2=否
     *    'title' => '栏目名称',// 栏目名称
     *    'attachId' => '1234567890123456789012',// 栏目 icon 附件ID
     *    'pic' => 'http://',// 栏目 icon 图片地址
     *    'url' => 'http://',// 栏目手机端链接 URL
     *    'description' => '栏目描述',// 栏目描述
     *    'dataCategoryId' => 9,// 栏目关联的应用分类 ID
     *    'dateline' => 123456789012,// 栏目关联设置时间
     *    'adminer' => 'admin',// 操作者名称（管理员名）
     *    'appName' => '新闻通知', // 应用名称
     *    'data' => array(
     *        'category' => array(
     *          array(
     *            "id" => "1",// 关联的应用一级分类 ID
     *            "name" => "一级分类",// 关联的应用分类名称
     *            "upId" => "0",// 关联的应用分类的上级分类 ID
     *            "url" => "http://"// 关联的应用手机端访问 URL
     *          ),
     *          array()
     *        ),
     *    )
     *)
     * </pre>
     * @return boolean
     */
    public function Index()
    {
        // 要查看的栏目 ID
        $recommenderId = I('recommenderId', 0, 'intval');

        if ($recommenderId <= 0) {
            return $this->_set_error('_ERR_RECOMMENDER_ICON_ID_EMPTY_40122');
        }

        $recommenderService = new CommonRecommenderService();
        $data = $recommenderService->get($recommenderId);
        $data = $recommenderService->iconDataFormat($data);

        return $this->_result = $data;
    }
}
