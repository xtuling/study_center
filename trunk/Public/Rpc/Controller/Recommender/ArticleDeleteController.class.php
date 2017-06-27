<?php
/**
 * ArticleDeleteController.class.php
 * 删除文章推荐 RPC 接口
 * @author Deepseath
 * @version $Id$
 */
namespace Rpc\Controller\Recommender;

use Common\Service\CommonRecommenderService;
use Think\Log;
use Common\Model\CommonRecommenderModel;

/**
 * 删除文章推荐 RPC 接口
 *
 * @uses 调用方法：\Com\Rpc::phprpc(rpc 接口 url)->invoke(接口方法名, 需要传入的参数数组key-value);
 */
class ArticleDeleteController extends AbstractController
{

    /**
     * 删除指定文章推荐信息
     * @desc 【RPC】删除指定文章推荐信息接口
     * @param mixed app:true 被推荐数据所在应用模块目录标识名
     * @param mixed dataCategoryId:true 被推荐数据所属的分类Id，可以为空，但必须提供该参数
     * @param mixed dataId:true 被推荐数据的原始数据 Id
     * @return boolean
     */
    public function Index()
    {
        if (!$this->_checkKeyParams()) {
            return false;
        }

        $recommenderService = new CommonRecommenderService();

        $recommenderService->delete_by_conds([
            'type' => CommonRecommenderModel::TYPE_ARTICLE,
            'app_dir' => $this->_params['app'],
            'data_category_id' => $this->_params['dataCategoryId'],
            'data_id' => $this->_params['dataId']
        ]);

        Log::record('<!-- 删除推荐数据：');
        Log::record($this->_params);
        Log::record('结束删除推荐数据 -->');

        return true;
    }
}
