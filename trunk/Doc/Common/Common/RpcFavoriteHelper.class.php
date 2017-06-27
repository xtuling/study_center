<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/6/7
 * Time: 18:56
 */

namespace Common\Common;

class RpcFavoriteHelper
{
    /**
     * 是否收藏：未收藏（所有常量值，必须和RPC规定一致）
     */
    const COLLECTION_NO = 0;

    /**
     * 是否收藏：已收藏
     */
    const COLLECTION_YES = 1;

    /*
     * 封面类型：无
     */
    const COVER_TYPE_NONE = 0;

    /*
     * 封面类型：图片
     */
    const COVER_TYPE_IMAGE = 1;

    /*
     * 封面类型：音频
     */
    const COVER_TYPE_RADIO = 2;

    /*
     * 封面类型：视频
     */
    const COVER_TYPE_VIDEO = 3;

    /*
     * 是否为文件夹：否
     */
    const IS_DIR_FALSE = 0;

    /*
     * 是否为文件夹：1
     */
    const IS_DIR_TRUE = 1;

    /*
     * RPC查询收藏状态的url
     */
    const COLLECTION_STATUS = QY_DOMAIN . '/Public/Rpc/Collection/CollectionStatus';

    /*
     * 删除应用数据，同步标记收藏状态的url
     */
    const COLLECTION_UPDATE = QY_DOMAIN . '/Public/Rpc/Collection/CollectionUpdate';

    /*
     * RPC新增收藏的url
     */
    const COLLECTION_NEW = QY_DOMAIN . '/Public/Rpc/Collection/CollectionNew';

    /*
     * RPC取消收藏的url
     */
    const COLLECTION_DELETE = QY_DOMAIN . '/Public/Rpc/Collection/CollectionDelete';

    /**
     * 实例化
     * @author zhonglei
     * @return RpcFavoriteHelper
     */
    public static function &instance()
    {
        static $instance;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * 查询收藏状态
     * @author liyifei
     * @param array $data 被收藏数据
     *      + string app 被收藏数据所在应用模块目录标识名
     *      + string uid 当前用户的uid
     *      + int dataId 被收藏数据的原始数据 Id
     * @return array
     *          array(
     *              'collection' => 1, // 收藏状态（0：未收藏，1：已收藏）
     *          )
     */
    public function getStatus($data)
    {
        if (empty($data) || !is_array($data)) {
            return [];
        }

        // 必传参数
        $data['app'] = APP_DIR;
        if (!isset($data['uid'], $data['dataId'])) {
            return [];
        }

        $url = oaUrl(self::COLLECTION_STATUS);
        $resJson = \Com\Rpc::phprpc($url)->invoke('index', $data);
        if (!$resJson) {
            return [];
        }

        return json_decode($resJson, true);
    }

    /**
     * 新增收藏
     * @author liyifei
     * @param array $data 被收藏数据信息
     *      + string app 被收藏数据所在应用模块目录标识名
     *      + string uid 当前用户的uid
     *      + int dataId 被收藏数据的原始数据 Id
     *      + string title 收藏标题（同事圈内容，资料库文件（夹）名等）
     *      + int cover_type 封面类型（0：无封面，1：图片，2：音频，3：视频）
     *      + string cover_id 封面附件ID
     *      + string cover_url 封面附件URL
     *      + int url 详情跳转地址（请传php相对地址，方便后续维护[/Course/Frontend/Index/Detail?article_id=1&data_id=2]）
     *      + string file_type 文件类型（JPG，TXT，PDF等）（资料库必传）
     *      + int file_size 文件大小（单位：字节）（资料库必传）
     *      + int is_dir 是否为文件夹（0：否，1：是）（资料库必传）
     * @return bool
     */
    public function addFavorite($data)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        // 必传参数
        $data['app'] = APP_DIR;
        if (!isset($data['uid'], $data['dataId'], $data['title'], $data['url'])) {
            return false;
        }

        // 资料库必传参数
        if (APP_DIR == 'Doc') {
            if (!isset($data['file_type'], $data['file_size'], $data['is_dir'])) {
                return false;
            }
        }

        $url = oaUrl(self::COLLECTION_NEW);

        return \Com\Rpc::phprpc($url)->invoke('index', $data);
    }

    /**
     * 取消收藏
     * @author liyifei
     * @param array $data 收藏信息
     *      + string app 被收藏数据所在应用模块目录标识名
     *      + string uid 当前用户的uid
     *      + int dataId 被收藏数据的原始数据 Id
     * @return bool
     */
    public function cancelFavorite($data)
    {
        // 必传参数
        $data['app'] = APP_DIR;
        if (!isset($data['uid'], $data['dataId'])) {
            return false;
        }

        $url = oaUrl(self::COLLECTION_DELETE);

        return \Com\Rpc::phprpc($url)->invoke('index', $data);
    }

    /**
     * 删除应用数据时，RPC同步收藏状态
     * @author liyifei
     * @param array $dataIds 被收藏数据的原始数据 Id。如果是多个使用逗号分割（如：'1,25,65'）
     * @return bool
     */
    public function updateStatus($dataIds)
    {
        if (empty($dataIds) || !is_array($dataIds)) {
            return false;
        }

        $url = oaUrl(self::COLLECTION_UPDATE);

        $param = [
            'app' => APP_DIR,
            'dataId' => implode(',', $dataIds),
        ];

        return \Com\Rpc::phprpc($url)->invoke('index', $param);
    }
}
