<?php
/**
 * IconOrderController.class.php
 * 【运营管理】管理后台 条幅排序更新接口
 * @author Deepseath
 * @version $Id$
 */
namespace Apicp\Controller\Recommender;

use Apicp\Controller\Recommender\AbstractController;
use Common\Service\CommonRecommenderService;
use Common\Model\CommonRecommenderModel;

/**
 * 【管理后台】条幅排序更新接口
 */
class BannerOrderController extends AbstractController
{

    /**
     * 条幅排序更新接口
     * @desc 【管理后台】用于管理后台对 条幅进行单个移动（向 up/down 移动一位）排序操作
     * @param integer recommenderId:true 进行移动排序操作的 条幅ID
     * @param string upDown:false:up 要进行移动的方向，up=向上；down=向下
     * @return array()
     */
    public function Index()
    {
        // 指定要操作排序的条幅ID
        $recommenderId = I('recommenderId', 0, 'intval');
        // 指定移动方向：up=向上；down=向下
        $upDown = I('upDown', 'up');

        $recommenderService = new CommonRecommenderService();
        $result = $recommenderService->updateOrder($recommenderId, CommonRecommenderModel::TYPE_BANNER, $upDown);
        if ($result === null) {
            return $this->_set_error('_ERR_RECOMMENDER_BANNER_NULL_40099');
        } elseif ($result === false) {
            if ($upDown == 'up') {
                return $this->_set_error('_ERR_RECOMMENDER_BANNER_IS_FIRST_40100');
            } else {
                return $this->_set_error('_ERR_RECOMMENDER_BANNER_IS_END_40101');
            }
        }

        return $this->_result = [];
    }
}
