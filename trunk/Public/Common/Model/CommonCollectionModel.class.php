<?php
/**
 * CommonCollectionModel.class.php
 * 收藏系统 Model
 * @author Xtong
 * @version $Id$
 */
namespace Common\Model;

class CommonCollectionModel extends AbstractModel
{

    /**
     * 封面类型：无封面
     */
    const COVER_NULL = 0;

    /**
     * 封面类型：图片
     */
    const COVER_PIC = 1;

    /**
     * 封面类型：音频
     */
    const COVER_AUDIO = 2;

    /**
     * 封面类型：视频
     */
    const COVER_VIDEO = 3;


    /**
     * 单次获取数据允许的最大条数
     */
    const LIMIT_MAX = 500;

    /**
     * 单次获取数据允许的最小条数
     */
    const LIMIT_MIN = 1;


    /**
     * 是否被删除：未删除
     *
     * @var integer
     */
    const DELETE_NO = 0;

    /**
     * 是否被删除：已删除
     *
     * @var integer
     */
    const DELETE_YES = 1;


    /**
     * 推荐系统 Model 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取 指定应用、数据Id、用户Uid 相关的收藏数据
     *
     * @param string $appDir
     * @param string $dataId
     * @param string $uid
     * @return array
     */
    public function getByAppdirDataidUid($appDir = null, $dataId = null, $uid = null)
    {
        return $this->get_by_conds($this->__whereDuplicate($appDir, $dataId, $uid));
    }

    /**
     * 构造查询 指定应用、数据 Id、用户Uid 相关的收藏数据
     * @param string $appDir
     * @param string $dataId
     * @param string $uid
     */
    private function __whereDuplicate($appDir, $dataId, $uid)
    {
        $conds = [];
        if ($appDir !== null) {
            $conds['app_dir'] = $appDir;
        }
        if ($dataId !== null) {
            $conds['data_id'] = $dataId;
        }
        if ($uid !== null) {
            $conds['uid'] = $uid;
        }

        return $conds;
    }
}
