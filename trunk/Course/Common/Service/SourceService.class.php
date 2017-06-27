<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/12
 * Time: 10:46
 */
namespace Common\Service;

use Common\Model\SourceModel;
use Common\Common\Constant;

class SourceService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new SourceModel();
    }

    /**
     * 创建素材标识
     * @author zhonglei
     * @param array $source 素材数据
     * @return string
     */
    public function createSourceKey($source)
    {
        $prefixs = [
            Constant::SOURCE_TYPE_IMG_TEXT => 'I',
            Constant::SOURCE_TYPE_AUDIO_IMG => 'A',
            Constant::SOURCE_TYPE_VEDIO => 'V',
            Constant::SOURCE_TYPE_FILE => 'F',
            Constant::SOURCE_TYPE_LINK => 'L',
        ];

        $source_type = $source['source_type'];
        $source_key = $prefixs[$source_type] . rgmdate(MILLI_TIME, 'ymdHi') . rand(10, 99);
        $count = $this->count_by_conds(['source_key' => $source_key]);

        if ($count == 0) {
            return $source_key;
        }

        return $this->createSourceKey($source);
    }

    /**
     * 根据条件，获取素材列表
     * @author liyifei
     * @param array $postData 请求参数
     * @return array
     */
    public function listSource($postData)
    {
        // 分页默认值
        $postData['page'] = isset($postData['page']) ? $postData['page'] : Constant::PAGING_DEFAULT_PAGE;
        $postData['limit'] = isset($postData['limit']) ? $postData['limit'] : Constant::PAGING_DEFAULT_LIMIT;
        list($start, $perpage) = page_limit($postData['page'], $postData['limit']);

        return $this->_d->listSource($postData, [$start, $perpage]);
    }

    /**
     * 根据条件，获取素材总数
     * @author liyifei
     * @param array $postData 请求参数
     * @return array
     */
    public function countSource($postData)
    {
        return $this->_d->countSource($postData);
    }
}
