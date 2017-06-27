<?php
/**
 * CollectionNewController.class.php
 * 新增收藏 RPC 接口
 * @author Xtong
 * @version $Id$
 */
namespace Rpc\Controller\Collection;

use Common\Service\CommonCollectionService;
use Common\Model\CommonCollectionModel;
use Think\Log;

/**
 * 新增收藏 RPC 接口
 * @uses 调用方法：\Com\Rpc::phprpc(rpc 接口 url)->invoke(接口方法名, 需要传入的参数数组key-value);
 */
class CollectionNewController extends AbstractController
{

    /**
     * 新增收藏信息
     * @desc 【RPC】新增收藏接口
     * @param string app:true 被收藏数据所在应用模块目录标识名
     * @param string uid:true 用户uid
     * @param string dataId:true 被收藏数据的原始数据 Id。<strong style="color: red">注意：请确保被推荐数据 app、uid、dataId 联合值为唯一</strong>
     * @param string title:true: 收藏内容标题（同事圈内容，资料库文件（夹）名等）
     * @param string cover_type:true: 封面类型（0：无封面，1：图片，2：音频，3：视频）
     * @param string cover_id:false: 封面附件ID
     * @param string cover_url:false: 封面附件URL
     * @param string url:true: 详情跳转URL
     * @param string file_type:false: 文件类型（JPG，TXT，PDF等
     * @param string file_size:false: 文件大小（单位：字节）
     * @param string is_dir:false: 是否为文件夹（0：否，1：是）
     * @param string circle_uid:false: 发布同事圈用户UID
     * @param string circle_face:false: 发布同事圈的用户头像
     * @param string circle_name:false: 发布同事圈的用户姓名
     * @param string circle_img:false: 同事圈的图片数组
     * @return boolean
     */
    public function Index()
    {
        if (!$this->_checkKeyParams()) {
            return false;
        }

        $keys = [
            'cover_id',
            'cover_url',
            'file_type',
            'file_size',
            'is_dir',
            'circle_uid',
            'circle_face',
            'circle_name',
            'circle_img'
        ];
        foreach ($keys as $_key) {
            if (!isset($this->_params[$_key])) {
                $this->_params[$_key] = '';
            }
        }

        if (empty($this->_params['app']) && empty($this->_params['dataId']) && empty($this->_params['uid'])) {
            Log::record('收藏标记为空');
            return $this->_set_error('_ERR_COLLECTION_ARTICLE_NULL_90001');
        }

        // 组织序列化数据
        $extData = [
            'file_type' => $this->_params['file_type'],
            'file_size' => $this->_params['file_size'],
            'is_dir' => $this->_params['is_dir'],
            'circle_uid' => $this->_params['circle_uid'],
            'circle_face' => $this->_params['circle_face'],
            'circle_name' => $this->_params['circle_name'],
            'circle_img' => $this->_params['circle_img']
        ];

        $extSerialize = serialize($extData);

        $collectionService = new CommonCollectionService();
        // 判断收藏的数据是否已存在
        $exists = $collectionService->getDuplicate(
            $this->_params['app'],
            $this->_params['dataId'],
            $this->_params['uid']
        );

        if (!empty($exists)) {
            Log::record('数据已被此用户收藏过，忽略再次收藏：app=' . $this->_params['app'] . '; dataId=' . $this->_params['dataId']
                . '; uid=' . $this->_params['uid']);
            $this->_set_error('_ERR_COLLECTION_DUPLICATE');

            return false;
        }

        $data = [
            'title' => $this->_params['title'],
            'cover_id' => $this->_params['cover_id'],
            'cover_url' => $this->_params['cover_url'],
            'cover_type' => $this->_params['cover_type'],
            'url' => $this->_params['url'],
            'app_dir' => $this->_params['app'],
            'app_identifier' => APP_IDENTIFIER,
            'data_id' => $this->_params['dataId'],
            'data' => $extSerialize, // 序列化同事圈和资料库的额外内容
            'uid' => $this->_params['uid'],
            'c_time' => MILLI_TIME,
            'c_deleted' => CommonCollectionModel::DELETE_NO
        ];

        $data['collection_id'] = $collectionService->collectionAdd($data);

        Log::record('<!-- 新增收藏数据：');
        Log::record(var_export($data,true));
        Log::record('结束新增收藏数据 -->');

        return true;
    }
}
