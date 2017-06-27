<?php
/**
 * CollectionStatusController.class.php
 * 查询收藏状态 RPC 接口
 * @author Xtong
 * @version $Id$
 */
namespace Rpc\Controller\Collection;

use Common\Service\CommonCollectionService;
use Think\Log;

/**
 * 查询收藏状态 RPC 接口
 *
 * @uses 调用方法：\Com\Rpc::phprpc(rpc 接口 url)->invoke(接口方法名, 需要传入的参数数组key-value);
 */
class CollectionStatusController extends AbstractController
{

    /**
     * 是否收藏 未收藏
     */
    const COLLECTION_NO = 0;

    /**
     * 是否收藏 已收藏
     */
    const COLLECTION_YES = 1;

    /**
     * 查询收藏状态
     * @desc 【RPC】查询收藏状态
     * @param mixed app:true 被推荐数据所在应用模块目录标识名
     * @param mixed uid:true 用户uid
     * @param mixed dataId:true 被推荐数据的原始数据 Id
     * @return boolean
     */
    public function Index()
    {
        if (!$this->_checkKeyParams()) {
            return false;
        }

        $collectionService = new CommonCollectionService();

        $collectionDetail = $collectionService->get_by_conds([
            'uid' => $this->_params['uid'],
            'app_dir' => $this->_params['app'],
            'data_id' => $this->_params['dataId']
        ]);

        Log::record('<!-- 开始查询收藏状态数据：');
        Log::record(var_export($this->_params,true));
        Log::record(var_export($collectionDetail,true));
        Log::record('结束查询收藏状态 -->');

        // Rpc 接口返回参数
        $result = array(
            'collection' => empty($collectionDetail) ? self::COLLECTION_NO : self::COLLECTION_YES
        );

        return json_encode($result);
    }
}
