<?php
/**
 * CollectionUpdateController.class.php
 * 更新收藏状态 RPC 接口
 * @author Xtong
 * @version $Id$
 */
namespace Rpc\Controller\Collection;

use Common\Service\CommonCollectionService;
use Common\Model\CommonCollectionModel;
use Think\Log;

/**
 * 更新收藏状态 RPC 接口
 * @uses 调用方法：\Com\Rpc::phprpc(rpc 接口 url)->invoke(接口方法名, 需要传入的参数数组key-value);
 */
class CollectionUpdateController extends AbstractController
{

    /**
     * 更新收藏状态
     *
     * 在应用中删除记录后，需要调用此接口同步收藏的删除状态标记
     *
     * @desc 【RPC】更新收藏状态
     * @param string app:true 被推荐数据所在应用模块目录标识名
     * @param string uid:true 用户uid，可以为空，但必须提供该参数
     * @param string|Array dataId:true 被推荐数据的原始数据 Id（可以为数组）。<strong style="color: red">注意：请确保被推荐数据 app、dataId 联合值为唯一</strong>
     * @return boolean
     */
    public function Index()
    {
        if (!$this->_checkKeyParams()) {
            return false;
        }

        if (empty($this->_params['app']) && empty($this->_params['dataId'])) {
            Log::record('收藏的数据标记为空');
            return false;
        }

        $collectionService = new CommonCollectionService();

        $conds = [
            'app_dir' => $this->_params['app'],
            'data_id' => explode(',',$this->_params['dataId']),
        ];

        $collectionService->update_by_conds($conds,['c_deleted'=>CommonCollectionModel::DELETE_YES]);

        Log::record('<!-- 更新同步收藏状态数据：');
        Log::record(var_export($conds,true));
        Log::record('结束同步收藏状态数据 -->');

        return true;
    }
}
