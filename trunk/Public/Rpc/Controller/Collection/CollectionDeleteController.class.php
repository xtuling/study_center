<?php
/**
 * CollectionDeleteController.class.php
 * 取消收藏 RPC 接口
 * @author Xtong
 * @version $Id$
 */
namespace Rpc\Controller\Collection;

use Common\Service\CommonCollectionService;
use Think\Log;

/**
 * 取消收藏 RPC 接口
 *
 * @uses 调用方法：\Com\Rpc::phprpc(rpc 接口 url)->invoke(接口方法名, 需要传入的参数数组key-value);
 */
class CollectionDeleteController extends AbstractController
{

    /**
     * 取消收藏
     * @desc 【RPC】取消收藏
     * @param mixed app:true 被推荐数据所在应用模块目录标识名
     * @param mixed uid:true 用户uid，可以为空，但必须传
     * @param mixed dataId:true 被推荐数据的原始数据 Id
     * @return boolean
     */
    public function Index()
    {
        if (!$this->_checkKeyParams()) {
            return false;
        }

        $collectionService = new CommonCollectionService();

        $collectionService->delete_by_conds([
            'uid' => $this->_params['uid'],
            'app_dir' => $this->_params['app'],
            'data_id' => $this->_params['dataId']
        ]);

        Log::record('<!-- 开始取消收藏数据：');
        Log::record($this->_params);
        Log::record('结束取消收藏 -->');

        return true;
    }
}
