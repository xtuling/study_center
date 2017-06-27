<?php
/**
 * CommonRecommenderModel.class.php
 * 推荐系统 Model
 * @author Deepseath
 * @version $Id$
 */
namespace Common\Model;

class CommonRecommenderModel extends AbstractModel
{

    /**
     * 推荐类型：首页条幅
     */
    const TYPE_BANNER = 1;

    /**
     * 推荐类型：栏目 ICON
     */
    const TYPE_ICON = 2;

    /**
     * 推荐类型：首页文章推荐
     */
    const TYPE_ARTICLE = 3;

    /**
     * 单次获取数据允许的最大条数
     */
    const LIMIT_MAX = 500;

    /**
     * 单次获取数据允许的最小条数
     */
    const LIMIT_MIN = 1;

    /**
     * 是否隐藏：不隐藏，显示
     *
     * @var unknown
     */
    const HIDE_NO = 1;

    /**
     * 是否隐藏：隐藏，不显示
     *
     * @var integer
     */
    const HIDE_YES = 2;

    /**
     * 是否系统内置：是
     */
    const SYSTEM_YES = 1;

    /**
     * 是否系统内置：否
     */
    const SYSTEM_NO = 2;

    /**
     * 显示顺序最小值
     */
    const VALUE_DISPLAYORDER_MIN = 1;

    /**
     * 显示顺序最大值
     */
    const VALUE_DISPLAYORDER_MAX = 9999;

    /**
     * 推荐系统 Model 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 计数 指定类型、应用、数据Id、分类Id 相关的推荐数据
     * @param number $type
     * @param string $appDir
     * @param string $dataId
     * @param string $dataCategoryId
     * @param number $ignoreRecommenderId 需要排除的数据 Id
     * @return array
     */
    public function countByTypeAppdirDataidCategoryid($type, $appDir = null, $dataId = null, $dataCategoryId = null, $ignoreRecommenderId = 0)
    {
        return $this->count_by_conds($this->__whereDuplicate($type, $appDir, $dataId, $dataCategoryId, $ignoreRecommenderId));
    }

    /**
     * 获取 指定类型、应用、数据Id、分类Id 相关的推荐数据
     *
     * @param number $type
     * @param string $appDir
     * @param string $dataId
     * @param string $dataCategoryId
     * @param number $ignoreRecommenderId 需要排除的数据 Id
     * @return array
     */
    public function getByTypeAppdirDataidCategoryid($type, $appDir = null, $dataId = null, $dataCategoryId = null, $ignoreRecommenderId = 0)
    {
        return $this->get_by_conds($this->__whereDuplicate($type, $appDir, $dataId, $dataCategoryId, $ignoreRecommenderId));
    }

    /**
     * 构造查询 指定类型、应用、数据 Id、分类 Id 相关的推荐数据
     * @param number $type
     * @param string $appDir
     * @param string $dataId
     * @param string $dataCategoryId
     * @param number $ignoreRecommenderId 需要排除的数据 Id
     */
    private function __whereDuplicate($type, $appDir, $dataId, $dataCategoryId, $ignoreRecommenderId)
    {
        $conds = [];
        $conds['type'] = $type;
        if ($appDir !== null) {
            $conds['app_dir'] = $appDir;
        }
        if ($dataId !== null) {
            $conds['data_id'] = $dataId;
        }
        if ($dataCategoryId !== null) {
            $conds['data_category_id'] = $dataCategoryId;
        }
        if ($ignoreRecommenderId) {
            // 需要忽略的数据 Id
            $conds['recommender_id != ?'] = $ignoreRecommenderId;
        }

        return $conds;
    }
}
