<?php
/**
 * BannerDeleteController.class.php
 * 【运营管理】管理后台条幅删除接口
 * @author Deepseath
 * @version $Id$
 */
namespace Apicp\Controller\Recommender;

use Apicp\Controller\Recommender\AbstractController;
use Common\Service\CommonRecommenderService;
use Common\Model\CommonRecommenderModel;

/**
 * 【管理后台】条幅 删除接口
 */
class BannerDeleteController extends AbstractController
{

    /**
     * 条幅删除接口
     * @desc 【管理后台】条幅删除接口
     * @param mixed recommenderId:true 待删除的条幅ID，<i>允许数值、以“,”分隔的字符串以及数组格式</i>
     * @return array()
     */
    public function Index()
    {
        $recommenderId = I('recommenderId');

        if (empty($recommenderId)) {
            return $this->_set_error('_ERR_RECOMMENDER_ID_EMPTY_40091');
        }

        if (is_scalar($recommenderId)) {
            // 如果给定的是字符串格式，则使用半角逗号分隔
            $recommenderId = explode(',', $recommenderId);
        }
        if (!is_array($recommenderId)) {
            return $this->_set_error('_ERR_RECOMMENDER_ID_ERROR_40092');
        }
        // 将请求的 ID 整理类型
        $recommenderId = array_map('intval', $recommenderId);
        // 将重复的 ID 移除
        $recommenderId = array_unique($recommenderId);

        $recommenderService = new CommonRecommenderService();
        $conds = [
            'recommender_id' => $recommenderId,
            'type' => CommonRecommenderModel::TYPE_BANNER,
            'system' => CommonRecommenderModel::SYSTEM_NO
        ];
        $recommenderService->delete_by_conds($conds);

        return $this->_result = $recommenderId;
    }
}
