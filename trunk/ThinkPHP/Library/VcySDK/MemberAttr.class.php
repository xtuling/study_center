<?php
/**
 * Member.class.php
 * 用户接口操作类
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhonglei
 * @version    1.0.0
 */
namespace VcySDK;

class MemberAttr
{
    /**
     * 属性类型，默认属性
     */
    const ATTR_TYPE_DEFAULT = 1;

    /**
     * 属性类型，扩展属性
     */
    const ATTR_TYPE_EXT = 2;

    /**
     * 获取扩展属性列表
     *
     * @return array
     */
    public function getExtList()
    {
        $user_attrs = cfg('USER_ATTRS');
        $ext_attrs = [];

        foreach ($user_attrs as $k => $v) {
            if ($v['attr_type'] == self::ATTR_TYPE_EXT) {
                $ext_attrs[$k] = $v;
            }
        }

        return $ext_attrs;
    }

    /**
     * 分离用户数据中的默认属性和扩展属性
     *
     * @param array $data 用户数据
     * @return array
     */
    public function splitAttr($data)
    {
        $ext_attrs = self::getExtList();
        $array = array_intersect(array_keys($data), array_keys($ext_attrs));

        if ($array) {
            $ext_data = [];

            foreach ($array as $k) {
                $ext_data[$k] = $data[$k];
                unset($data[$k]);
            }

            return [$data, $ext_data];
        } else {
            return [$data, []];
        }
    }

    /**
     * 格式化扩展属性数据
     *
     * @param string $uid 用户UID
     * @param array $data 扩展属性数据
     * @return array
     */
    public function formatExtData($uid, $data)
    {
        if ($uid) {
            $extData = [];

            foreach ($data as $k => $v) {
                $extData[] = [
                    'memUid' => $uid,
                    'extKey' => $k,
                    'extValue' => $v,
                ];
            }

            return $extData;
        }

        return [];
    }
}
