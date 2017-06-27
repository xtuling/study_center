<?php
/**
 * BannerNewController.class.php
 * 【运营管理】管理后台新增条幅
 * @author Deepseath
 * @version $Id$
 */
namespace Apicp\Controller\Recommender;

use Apicp\Controller\Recommender\AbstractController;
use Common\Service\CommonRecommenderService;
use Common\Model\CommonRecommenderModel;
use Common\Common\Attach;

/**
 * 新增首页条幅
 */
class BannerNewController extends AbstractController
{

    /**
     * 新增首页条幅数据
     * @desc 【管理后台】新增首页条幅数据
     * @param string attachId:true 条幅图片附件 Id
     * @param string title:true 条幅标题，不能超过 4个 字符
     * @param string url:true 条幅手机端访问链接 URL
     * @param string app:true 指定的应用模块
     * @param string dataCategoryId:true 条幅关联的应用分类 ID
     * @param string dataId:true 条幅关联的文章 ID
     * @param array data:true 条幅关联的文章和分类信息：<pre>
     * [<br>
     *  ['category' => [<br>
     *      ['id' => '分类ID', 'name' => '分类名称', 'upId' => '上级分类ID', 'url' => '分类 URL'],<br>
     *      ['id' => '分类ID', 'name' => '分类名称', 'upId' => '上级分类ID', 'url' => '分类 URL'],<br>
     *      ['id' => '分类ID', 'name' => '分类名称', 'upId' => '上级分类ID', 'url' => '分类 URL']<br>
     *     ]<br>
     *  ]<br>
     * ]
     * </pre>
     * @return array('recommendId' => '新增的条幅 Id')
     */
    public function Index()
    {

        // 图片附件 ID
        $attachId = I('attachId', '');
        // 条幅名称
        $title = I('title', '');
        // 链接 URL
        $url = I('url', '');
        // 关联的 APP
        $app = I('app', '');
        // 关联的 APP 分类 ID
        $dataCategoryId = I('dataCategoryId', '');
        // 关联的文章 ID
        $dataId = I('dataId', '');
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
            return $this->_set_error('_ERR_RECOMMENDER_ATTACHID_EMPTY_40093');
        }

        $attachServ = &Attach::instance();
        $pic = $attachServ->getAttachUrl($attachId);
        if (empty($pic)) {
            return $this->_set_error('_ERR_RECOMMENDER_ATTACH_URL_ERROR_40094');
        }

        $recommenderService = new CommonRecommenderService();

        if (!$recommenderService->verifyFieldTitle($title, 64)) {
            return $this->_set_error('_ERR_RECOMMENDER_TITLE_TOOLONG_40095');
        }

        if (!$recommenderService->verifyFieldUrl($url)) {
            return $this->_set_error('_ERR_RECOMMENDER_URL_ERROR_40096');
        }

        if (!$recommenderService->is_app($app)) {
            // 检查应用模块标识是否正确
            return $this->_set_error('_ERR_RECOMMENDER_APP_ERROR_40097');
        }

        if ($recommenderService->countDuplicate(CommonRecommenderModel::TYPE_BANNER, $app, $dataId, $dataCategoryId) > 0) {
            // 检查关联的分类是否重复
            return $this->_set_error('_ERR_RECOMMENDER_DATA_DUPLICATE_40098');
        }

        $recommenderId = $recommenderService->remmenderUpdate([
            'type' => CommonRecommenderModel::TYPE_BANNER,
            'displayorder' => CommonRecommenderModel::VALUE_DISPLAYORDER_MIN,
            'hide' => CommonRecommenderModel::HIDE_NO,
            'system' => CommonRecommenderModel::SYSTEM_NO,
            'title' => $title,
            'attach_id' => $attachId,
            'pic' => $pic,
            'url' => $url,
            'description' => '',
            'app_dir' => $app,
            'app_identifier' => APP_IDENTIFIER,
            'data_id' => $dataId,
            'data_category_id' => $dataCategoryId,
            'data' => $data,
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
