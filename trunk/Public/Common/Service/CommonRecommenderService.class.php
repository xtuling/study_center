<?php
/**
 * CommonRecommenderService.class.php
 * 推荐系统 Service
 * @author Deepseath
 * @version $Id$
 */
namespace Common\Service;

use Common\Model\CommonRecommenderModel;
use Com\Validator;
use Common\Common\Setting;

class CommonRecommenderService extends AbstractService
{

    /**
     * 有效的推荐类型列表
     */
    public $typeList = [];

    /**
     * 应用配置
     */
    protected $_settingAppConfig = null;

    /**
     * 推荐系统 Service 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->_d = new CommonRecommenderModel();
        $this->typeList = [
            CommonRecommenderModel::TYPE_BANNER,
            CommonRecommenderModel::TYPE_ICON,
            CommonRecommenderModel::TYPE_ARTICLE
        ];
    }

    /**
     * 验证推荐类型是否合法
     * @param number $type
     * @return boolean
     */
    public function is_type($type)
    {
        if (!is_scalar($type) || !in_array($type, [
            CommonRecommenderModel::TYPE_BANNER,
            CommonRecommenderModel::TYPE_ICON,
            CommonRecommenderModel::TYPE_ARTICLE
        ])) {
            return false;
        }

        return true;
    }

    /**
     * 获取应用配置信息
     * @return mixed|object
     */
    public function getAppConfig()
    {
        if ($this->_settingAppConfig === null) {
            $set = &Setting::instance();
            $this->_settingAppConfig = $set->get('Common.appConfig');
            if (!is_array($this->_settingAppConfig)) {
                $this->_settingAppConfig = [];
            }
        }

        return $this->_settingAppConfig;
    }

    /**
     * 验证 app 标识合法性
     * @param unknown $app
     */
    public function is_app($app)
    {
        if (empty($app)) {
            return false;
        }

        $config = $this->getAppConfig();
        if (!is_array($config) || !isset($config[$app])) {
            return false;
        }

        return true;
    }

    /**
     * 计算指定推荐数据关联的个数，一般用于检测关联的数据是否存在
     * @param integer $type 待检查的数据类型，见 CommonRecommenderModel::TYPE_*
     * @param string $appDir 应用目录标识名
     * @param string $dataId 数据关联的所在应用数据 ID
     * @param string $dataCategoryId 数据关联的所在应用分类 ID
     * @param number $ignoreRecommenderId 需要排除的数据 Id
     * @return number
     */
    public function countDuplicate($type, $appDir, $dataId, $dataCategoryId = null, $ignoreRecommenderId = 0)
    {
        return (int) $this->_d->countByTypeAppdirDataidCategoryid($type, $appDir, $dataId, $dataCategoryId, $ignoreRecommenderId);
    }

    /**
     * 获取指定推荐数据关联的所有数据，一般用于查找重复的关联数据
     * @param integer $type 待检查的数据类型，见 CommonRecommenderModel::TYPE_*
     * @param string $appDir 应用目录标识名
     * @param string $dataId 数据关联的所在应用数据 ID
     * @param string $dataCategoryId 数据关联的所在应用分类 ID
     * @param number $ignoreRecommenderId 需要排除的数据 Id
     * @return number
     */
    public function getDuplicate($type, $appDir, $dataId, $dataCategoryId = null, $ignoreRecommenderId = 0)
    {
        return $this->_d->getByTypeAppdirDataidCategoryid($type, $appDir, $dataId, $dataCategoryId, $ignoreRecommenderId);
    }

    /**
     * 根据数据类型取列表
     * @param number $type 类型标记，见 \Common\Model\CommonRecommenderModel::TYPE_* 定义
     * @param number $hide 是否取出隐藏的数据，见 \Common\Model\CommonRecommenderModel::HIDE_* 定义，null=取出全部
     * @param number $start 开始数据行号
     * @param number $limit 取出数据条数
     * @return array( 'total' => '总数',
     *         'pages' => '总页码',
     *         'list' => array()
     *         )
     */
    public function listByType($type, $hide = null, $start = 0, $limit = 6)
    {
        $conds = [];
        $conds['type'] = $type;
        if ($hide != null) {
            $conds['hide'] = $hide;
        }
        if ($start < 0) {
            $start = 0;
        }
        if ($limit < 1) {
            $limit = 6;
        }
        $total = (int) $this->_d->count_by_conds($conds, 'recommender_id');
        $pages = ceil($total / $limit);
        $list = [];
        if ($total > 0) {
            $list = $this->_d->list_by_conds($conds, [
                $start,
                $limit
            ], [
                'displayorder' => 'DESC',
                'dateline' => 'DESC'
            ]);
        }

        return [
            'type' => $type,
            'total' => $total,
            'pages' => $pages,
            'limit' => $limit,
            'list' => $list
        ];
    }

    /**
     * 格式化数据输出，主要用于手机端
     * @param array $data
     * @return array( 'id' => '数据 ID',
     *         'title' => '标题',
     *         'pic' => '图片 URL',
     *         'url' => '链接地址',
     *         'time' => '发布时间戳',
     *         'key' => '数据唯一标识值'
     *         )
     */
    public function format($data)
    {
        return array(
            'id' => $data['recommender_id'],
            'title' => $data['title'],
            'pic' => $data['pic'],
            'url' => $data['url'],
            'time' => $data['dateline'],
            'key' => md5("Recommender" . "\t" . $data['type'] . "\t" . $data['recommender_id'])
        );
    }

    /**
     * 检查字段数据 标题 是否合法
     * @param string $title 标题
     * @param number $maxlength 限制最大长度
     * @return boolean
     */
    public function verifyFieldTitle($title, $maxlength = 4)
    {
        return \Com\Validator::is_string_count_in_range($title, 1, $maxlength);
    }

    /**
     * 检查字段数据 URL 是否合法
     * @param string $url URL
     * @return boolean
     */
    public function verifyFieldUrl($url)
    {
        if (!isset($url{0}) || isset($url{255})) {
            return false;
        }
        return true;
    }

    /**
     * 检查字段数据 描述 是否合法
     * @param string $description 描述内容
     * @return boolean
     */
    public function verifyFieldDescription($description)
    {
        return empty($description) || \Com\Validator::is_string_count_in_range($description, 0, 140) === true;
    }

    /**
     * 更新、添加 一条推荐数据
     * @param array $data 推荐数据
     * @param null|number $remmenderId 待更新的推荐数据 ID
     * @return \Think\mixed|string
     */
    public function remmenderUpdate($data, $remmenderId = null)
    {
        $values = [];
        $fields = [
            'type',
            'displayorder',
            'hide',
            'system',
            'title',
            'attach_id',
            'pic',
            'url',
            'description',
            'data_category_id',
            'app_dir',
            'app_identifier',
            'data_id',
            'data',
            'dateline',
            'adminer_id',
            'adminer'
        ];

        $values = [];
        foreach ($fields as $_field) {
            if (isset($data[$_field])) {
                $values[$_field] = $data[$_field];
            }
        }

        if (isset($data['data'])) {
            $values['data'] = serialize($values['data']);
        }

        if ($remmenderId === null) {
            return $this->_d->insert($values);
        } else {
            $this->_d->update($remmenderId, $values);
            return $remmenderId;
        }
    }

    /**
     * 栏目数据格式化输出，主要用于管理后台
     * @param array $data
     * @return array
     */
    public function iconDataFormat($data)
    {
        $config = $this->getAppConfig();
        if (isset($config[$data['app_dir']])) {
            $appName = $config[$data['app_dir']]['name'];
        } else {
            $appName = '';
        }
        return [
            'id' => $data['recommender_id'],
            'hide' => $data['hide'],
            'system' => $data['system'],
            'title' => $data['title'],
            'attachId' => $data['attach_id'],
            'pic' => $data['pic'],
            'url' => $data['url'],
            'description' => $data['description'],
            'dataCategoryId' => $data['data_category_id'],
            'dateline' => $data['dateline'],
            'adminer' => $data['adminer'],
            'app' => $data['app_dir'],
            'appName' => $appName,
            'data' => unserialize($data['data'])
        ];
    }

    /**
     * 栏目数据格式化输出，主要用于管理后台
     * @param array $data
     * @return array
     */
    public function bannerDataFormat($data)
    {
        if ($this->_settingAppConfig === null) {
            $set = &Setting::instance();
            $this->_settingAppConfig = $set->get('Common.appConfig');
        }

        return [
            'id' => $data['recommender_id'],
            'hide' => $data['hide'],
            'system' => $data['system'],
            'title' => $data['title'],
            'attachId' => $data['attach_id'],
            'pic' => $data['pic'],
            'url' => $data['url'],
            // 'description' => $data['description'],
            'appName' => isset($this->_settingAppConfig[$data['app_dir']]) ? $this->_settingAppConfig[$data['app_dir']]['name'] : '',
            'dataId' => $data['data_id'],
            'dataCategoryId' => $data['data_category_id'],
            'dateline' => $data['dateline'],
            'adminer' => $data['adminer'],
            'data' => unserialize($data['data'])
        ];
    }

    /**
     * 单数据排序移动操作（向上或者向下移动1位）
     * <strong>使用本方法需要确保排序号是唯一的</strong>
     * @param number $recommenderId 待移动的数据 ID
     * @param number $type 数据类型，见 CommonRecommenderModel::TYPE_** 定义
     * @param string $upDown 移动方向，up=向上；down=向下
     * @param array $conds 额外的其他查询条件
     * @param array $order 自定义的排序方式
     * @return boolean
     */
    public function updateOrder($recommenderId, $type, $upDown = 'up', $conds = [], $order = [])
    {
        // 当前待移动的对象信息
        $current = $this->_d->get($recommenderId);
        if (empty($current)) {
            return null;
        }

        if ($upDown == 'up') {
            $equal = '>=';
            $desc = 'ASC';
        } else {
            $equal = '<=';
            $desc = 'DESC';
        }

        // 获取其移动方向的紧邻数据信息
        $conds['type'] = $type;
        $conds['displayorder' . $equal . '?'] = $current['displayorder'];
        $order = array_merge([
            'displayorder' => $desc
        ], $order);
        $op = [];
        foreach ($this->_d->list_by_conds($conds, [
            0,
            2
        ], $order) as $_data) {
            $op[] = [
                'recommender_id' => $_data['recommender_id'],
                'displayorder' => $_data['displayorder']
            ];
        }
        if (isset($op[0]) && isset($op[1])) {
            // 交换两个数据的排序号码
            $this->_d->update($op[0]['recommender_id'], [
                'displayorder' => $op[1]['displayorder']
            ]);
            $this->_d->update($op[1]['recommender_id'], [
                'displayorder' => $op[0]['displayorder']
            ]);
            return true;
        }

        return false;
    }
}
