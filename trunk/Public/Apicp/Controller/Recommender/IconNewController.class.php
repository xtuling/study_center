<?php
/**
 * IconNewController.class.php
 * 【运营管理】管理后台新建栏目接口
 * @author Deepseath
 * @version $Id$
 */
namespace Apicp\Controller\Recommender;

use Apicp\Controller\Recommender\AbstractController;
use Common\Service\CommonRecommenderService;
use Common\Model\CommonRecommenderModel;
use Common\Common\Attach;

/**
 * 【管理后台】新建栏目接口
 */
class IconNewController extends AbstractController
{

    /**
     * 新建栏目接口
     * @desc 【管理后台】新建栏目接口
     * @param string attachId:true 栏目图标附件 Id
     * @param string title:true 栏目标题，不能超过 4个 字符
     * @param string description:false 栏目描述，不能超过  120个 字符
     * @param string url:true 栏目相对链接 URL
     * @param string app:true 指定的应用模块
     * @param string dataCategoryId:true 栏目关联的应用分类 ID
     * @param array data:true 栏目关联的应用分类详情，结构如下：<br>
     * [<br>
     *  ['id' => '一级分类ID', 'name' => '分类名称', 'upId' => '上级分类ID', 'url' => '分类 URL'],<br>
     *  ['id' => '二级分类ID', 'name' => '分类名称', 'upId' => '上级分类ID', 'url' => '分类 URL']<br>
     *  ['id' => '三级分类ID', 'name' => '分类名称', 'upId' => '上级分类ID', 'url' => '分类 URL']<br>
     *  ... ... <br>
     * ]
     * @return <pre>array(
     *  'recommendId' => 123 // 新增的栏目 Id
     *  )</pre>
     */
    public function Index()
    {

        // 图片附件 ID
        $attachId = I('attachId', '');
        // 栏目名称
        $title = I('title', '');
        // 栏目描述
        $description = I('description', '');
        // 链接 URL
        $url = I('url', '');
        // 关联的 APP
        $app = I('app', '');
        // 关联的 APP 分类 ID
        $dataCategoryId = I('dataCategoryId', '');
        // 关联版块,数组
        /**
         * [
         * ['id' => '分类ID', 'name' => '分类名称', 'upId' => '上级分类ID', 'url' => '分类 URL'],
         * ['id' => '分类ID', 'name' => '分类名称', 'upId' => '上级分类ID', 'url' => '分类 URL'],
         * ['id' => '分类ID', 'name' => '分类名称', 'upId' => '上级分类ID', 'url' => '分类 URL']
         * ]
         */
        $data = I('data/a', []);

        if (empty($attachId)) {
            return $this->_set_error('_ERR_RECOMMENDER_ICON_ATTACH_ID_EMPTY_40104');
        }
        $attachServ = &Attach::instance();
        $pic = $attachServ->getAttachUrl($attachId);
        if (empty($pic)) {
            return $this->_set_error('_ERR_RECOMMENDER_ICON_ATTACH_URL_ERROR_40105');
        }

        $recommenderService = new CommonRecommenderService();

        if (!$recommenderService->verifyFieldTitle($title)) {
            return $this->_set_error('_ERR_RECOMMENDER_ICON_TITLE_TOOLONG_40106');
        }

        if (!$recommenderService->verifyFieldDescription($description)) {
            return $this->_set_error('_ERR_RECOMMENDER_ICON_DESCRIPTION_TOOLONG_40107');
        }

        if (!$recommenderService->verifyFieldUrl($url)) {
            return $this->_set_error('_ERR_RECOMMENDER_ICON_URL_ERROR_40108');
        }

        if (empty($app) || $dataCategoryId === '' || empty($data)) {
            // 检查提交的相关参数是否存在
            return $this->_set_error('_ERR_RECOMMENDER_ICON_PARAM_40109');
        }

        if (!$recommenderService->is_app($app)) {
            // 检查应用模块标识是否正确
            return $this->_set_error('_ERR_RECOMMENDER_ICON_APP_ERROR_40110');
        }

        if ($recommenderService->countDuplicate(CommonRecommenderModel::TYPE_ICON, $app, null, $dataCategoryId) > 0) {
            // 检查关联的分类是否重复
            return $this->_set_error('_ERR_RECOMMENDER_ICON_DUPLICATE_40111');
        }

        // 新增数据
        $recommenderId = $recommenderService->remmenderUpdate([
            'type' => CommonRecommenderModel::TYPE_ICON,
            'displayorder' => CommonRecommenderModel::VALUE_DISPLAYORDER_MIN,
            'hide' => CommonRecommenderModel::HIDE_NO,
            'system' => CommonRecommenderModel::SYSTEM_NO,
            'title' => $title,
            'attach_id' => $attachId,
            'pic' => $pic,
            'url' => $url,
            'description' => $description,
            'app_dir' => $app,
            'app_identifier' => APP_IDENTIFIER,
            'data_id' => '',
            'data_category_id' => $dataCategoryId,
            'data' => [
                'category' => $data
            ],
            'dateline' => MILLI_TIME,
            'adminer_id' => $this->_login->user['eaId'],
            'adminer' => $this->_login->user['eaRealname']
        ]);

        // 设置其排序号为 ID
        $recommenderService->update($recommenderId, [
            'displayorder' => $recommenderId
        ]);

        return $this->_result = [
            'recommenderId' => $recommenderId
        ];
    }
}
