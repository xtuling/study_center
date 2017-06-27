<?php
/**
 * IconUpdateController.class.php
 * 【运营管理】管理后台栏目更新接口
 * @author Deepseath
 * @version $Id$
 */
namespace Apicp\Controller\Recommender;

use Common\Service\CommonRecommenderService;
use Common\Common\Attach;
use Common\Model\CommonRecommenderModel;

/**
 * 【管理后台】栏目更新接口
 */
class IconUpdateController extends AbstractController
{

    /**
     * 栏目更新接口
     * @desc 【管理后台】栏目更新接口
     *
     * @param integer recommenderId:true 待更新的栏目 Icon ID
     * @param string attachId:false 栏目图标附件 Id，不提供则不更新
     * @param string title:false 栏目标题，不能超过 4个 字符，不提供则不更新
     * @param string description:false 栏目描述，不能超过 120个 字符，不提供则不更新
     * @param string url:false 栏目相对链接 URL，不提供则不更新
     * @param string app:false 指定的应用模块，不提供则不更新
     * @param string dataCategoryId:false 栏目关联的应用分类 ID，不提供则不更新
     * @param array data:false 栏目关联的应用分类详情，不提供则不更新，否则，结构如下：<br>
     *            [<br>
     *              ['id' => '一级分类ID', 'name' => '分类名称', 'upId' => '上级分类ID', 'url' => '分类 URL'],<br>
     *              ['id' => '二级分类ID', 'name' => '分类名称', 'upId' => '上级分类ID', 'url' => '分类 URL'],<br>
     *              ['id' => '三级分类ID', 'name' => '分类名称', 'upId' => '上级分类ID', 'url' => '分类 URL']<br>
     *              ... ... <br>
     *            ]<br>
     * <strong style="color:red">需要注意的是：请自行确保 url、app、dataCategoryId、data 几个数据的一致性</strong>
     *
     * @return <pre>
     * array(
     *  'recommendId' => 123 // 已更新的数据
     * )
     * </pre>
     */
    public function Index()
    {
        // 当前编辑的数据 Id
        $recommenderId = I('recommenderId', 0, 'intval');
        // 图片附件 ID
        $attachId = I('attachId', null);
        // 栏目名称
        $title = I('title', null);
        // 栏目描述
        $description = I('description', null);
        // 链接 URL
        $url = I('url', null);
        // 关联的 APP
        $app = I('app', null);
        // 关联的 APP 分类 ID
        $dataCategoryId = I('dataCategoryId', null);
        // 关联版块,数组
        /**
         * [
         * ['id' => '分类ID', 'name' => '分类名称', 'upId' => '上级分类ID', 'url' => '分类 URL'],
         * ['id' => '分类ID', 'name' => '分类名称', 'upId' => '上级分类ID', 'url' => '分类 URL'],
         * ['id' => '分类ID', 'name' => '分类名称', 'upId' => '上级分类ID', 'url' => '分类 URL']
         * ]
         */
        $data = I('data/a', null);

        // 待更新的数据
        $updateData = [];

        if ($attachId !== null) {
            if (empty($attachId)) {
                return $this->_set_error('_ERR_RECOMMENDER_ICON_ATTACH_ID_EMPTY_40115');
            }
            $updateData['attach_id'] = $attachId;

            $attachServ = &Attach::instance();
            $updateData['pic'] = $attachServ->getAttachUrl($attachId);
            if (empty($updateData['pic'])) {
                return $this->_set_error('_ERR_RECOMMENDER_ICON_ATTACH_URL_EMPTY_40116');
            }
        }

        $recommenderService = new CommonRecommenderService();

        if ($title != null) {
            if (!$recommenderService->verifyFieldTitle($title)) {
                return $this->_set_error('_ERR_RECOMMENDER_ICON_TITLE_TOOLONG_40117');
            }
            $updateData['title'] = $title;
        }

        if ($description !== null) {
            if (!$recommenderService->verifyFieldDescription($description)) {
                return $this->_set_error('_ERR_RECOMMENDER_ICON_DESCRIPT_TOOLONG_40118');
            }
            $updateData['description'] = $description;
        }

        if ($url !== null) {
            if (!$recommenderService->verifyFieldUrl($url)) {
                return $this->_set_error('_ERR_RECOMMENDER_ICON_URL_ERROR_40119');
            }
            $updateData['url'] = $url;
        }

        if ($app !== null) {
            if (!$recommenderService->is_app($app)) {
                // 检查应用模块标识是否正确
                return $this->_set_error('_ERR_RECOMMENDER_ICON_APP_ERROR_40120');
            }
            $updateData['app_dir'] = $app;
        }

        if ($dataCategoryId !== null) {
            if (!$app) {
                return $this->_set_error('_ERR_RECOMMENDER_ICON_APP_NULL_40121');
            }
            if ($recommenderService->countDuplicate(CommonRecommenderModel::TYPE_ICON, $app, null, $dataCategoryId, $recommenderId) > 0) {
                // 检查关联的分类是否重复
                return $this->_set_error('_ERR_RECOMMENDER_ICON_APP_DUPLICATE_40123');
            }
            $updateData['data_category_id'] = $dataCategoryId;
            if ($data !== null) {
                $updateData['data'] = [
                    'category' => $data
                ];
            }
        }

        if (empty($updateData)) {
            $this->_result = [];
            return true;
        }

        $recommenderId = $recommenderService->remmenderUpdate($updateData, $recommenderId);
        return $this->_result = $updateData;
    }
}
