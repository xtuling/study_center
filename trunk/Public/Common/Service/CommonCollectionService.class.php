<?php
/**
 * CommonCollectionService.class.php
 * 收藏系统 Service
 * @author Xtong
 * @version $Id$
 */
namespace Common\Service;

use Common\Model\CommonCollectionModel;
use Think\Log;

class CommonCollectionService extends AbstractService
{

    /**
     * 有效的封面类型列表
     */
    public $coverType = [];

    /**
     * 应用配置
     */
    protected $_settingAppConfig = null;

    /**
     * 收藏系统 Service 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->_d = new CommonCollectionModel();
        $this->coverType = [
            CommonCollectionModel::COVER_NULL,
            CommonCollectionModel::COVER_PIC,
            CommonCollectionModel::COVER_AUDIO,
            CommonCollectionModel::COVER_VIDEO
        ];
    }

    /**
     * 验证封面类型是否合法
     * @param number $type
     * @return boolean
     */
    public function is_type($type)
    {
        if (!is_scalar($type) || !in_array($type, $this->coverType)) {
            return false;
        }

        return true;
    }

    /**
     * 获取指定收藏数据关联的所有数据，一般用于查找重复的关联数据
     * @param string $appDir 应用目录标识名
     * @param string $dataId 数据关联的所在应用数据 ID
     * @param string $uid 收藏者的 UID
     * @return number
     */
    public function getDuplicate($appDir, $dataId, $uid)
    {
        return $this->_d->getByAppdirDataidUid($appDir, $dataId, $uid);
    }

    /**
     * 添加 一条收藏数据
     * @param array $data 收藏数据
     * @return \Think\mixed|string
     */
    public function collectionAdd($data)
    {
        // 判断封面类型合法性
        if (!$this->is_type($data['cover_type'])) {

            Log::record('<!-- 收藏封面类型不合法 -->');

            return false;
        }

        $fields = [
            'title',
            'cover_id',
            'cover_url',
            'cover_type',
            'url',
            'app_dir',
            'app_identifier',
            'data_id',
            'data',
            'uid',
            'c_time',
            'c_deleted'
        ];

        $values = [];
        foreach ($fields as $_field) {
            if (isset($data[$_field])) {
                $values[$_field] = $data[$_field];
            }
        }

        return $this->_d->insert($values);

    }

    public function formate_list($list)
    {

        // 初始化列表
        $new_list = [];

        foreach ($list as $k => $v) {
            // 初始化序列化
            $data = [];

            $new_list[$k]['collection_id'] = (int)$v['collection_id'];
            $new_list[$k]['app'] = $v['app_dir'];
            $new_list[$k]['dataId'] = (int)$v['data_id'];
            $new_list[$k]['title'] = $v['title'];
            $new_list[$k]['cover_type'] = (int)$v['cover_type'];
            $new_list[$k]['cover_id'] = $v['cover_id'];
            $new_list[$k]['cover_url'] = $v['cover_url'];
            $new_list[$k]['url'] = 'http://' . $_SERVER['HTTP_HOST'] . D_S . QY_DOMAIN . D_S . $v['url'];
            $new_list[$k]['c_time'] = $v['c_time'];
            $new_list[$k]['c_deleted'] = (int)$v['c_deleted'];

            if (!empty($v['data'])) {
                $data = unserialize($v['data']);
            }

            // 资料库
            $new_list[$k]['file_type'] = $data['file_type'];
            $new_list[$k]['file_size'] = $data['file_size'];
            $new_list[$k]['is_dir'] = (int)$data['is_dir'];

            // 同事圈
            $new_list[$k]['circle_uid'] = $data['circle_uid'];
            $new_list[$k]['circle_face'] = $data['circle_face'];
            $new_list[$k]['circle_name'] = $data['circle_name'];
            $new_list[$k]['circle_img'] = $data['circle_img'];
        }

        return $new_list;
    }

}
