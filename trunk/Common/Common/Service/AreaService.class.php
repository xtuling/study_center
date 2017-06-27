<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 2016/10/18
 * Time: 14:58
 */
namespace Common\Service;

class AreaService
{

    /**
     * 获取地区数据
     * @author zhonglei
     * @return array
     */
    public function getData()
    {

        static $areas;

        if (!$areas) {
            cfg(load_config(CONF_PATH . 'area' . CONF_EXT));
            $areas = cfg('AREADATA');

            foreach ($areas as $k => $v) {
                $areas[$v['parent_id']]['children'][$k] = &$areas[$k];
            }
        }

        return $areas;
    }

    /**
     * 根据父级ID获取地区列表
     * @author zhonglei
     * @param int $parent_id 父级ID
     * @return array
     */
    public function list_by_parent($parent_id = 0)
    {

        $data = $this->getData();
        $list = [];

        if (isset($data[$parent_id])) {
            foreach ($data[$parent_id]['children'] as $v) {
                $list[] = [
                    'area_id' => $v['area_id'],
                    'areaname' => $v['areaname'],
                ];
            }
        }

        return $list;
    }
}
