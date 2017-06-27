<?php
/**
 * @Author: ppker
 * @Date:   2015-09-16 18:29:28
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-09-17 10:31:06
 * @description: [地图类]
 */
namespace Com;

use Think\Log;

class Location
{
    /** 地球半径, 单位(米) */
    const EARTH_RADIUS = 6378137;
    /** 百度地图纠偏接口 URL配置 */
    const BAIDU_COORD_URL = 'http://api.map.baidu.com/ag/coord/convert?from=0&to=4&x=%s&y=%s';
    /** 百度地图反地址解析接口 URL配置 */
    const BAIDU_LOCATION_URL = 'http://api.map.baidu.com/geocoder/v2/?coordtype=bd09ll&output=json&location=%s&ak=%s';
    /** 腾讯地图 逆地址解析（坐标描述）接口 http://lbs.qq.com/webservice_v1/guide-gcoder.html */
    const QQ_GEOCODER_URL = 'http://apis.map.qq.com/ws/geocoder/v1?location=%s,%s&coord_type=%s&get_poi=%s&key=%s&output=json';
    /** 腾讯地图Key */
    const QQ_MAP_KEY = 'UY5BZ-5WHAW-KXORU-R6UUI-Q4WW7-Y4FUX';
    /** 腾讯坐标转换接口 */
    const QQ_TRANSLATE = 'http://apis.map.qq.com/ws/coord/v1/translate?locations=%s&type=%s&key=%s';
    /** 百度地图Key */
    const BAIDU_MAP_KEY = 'ZgwqnK2tl1y2k6oRI8DCyZ2kjmOcsz22';
    /** 百度坐标转换接口 */
    const BAIDU_TRANSLATE = 'http://api.map.baidu.com/geoconv/v1/?coords=%s&from=%s&to=%s&ak=%s';
    /** 地理位置类型 */
    public $_provider = '';

    public function __construct()
    {
        $this->_provider = cfg('LBS_PROVIDER');
    }

    /**
     * 纠正经纬偏移量
     *
     * @param mixed $url
     *            地图接口URL
     * @return void
     */
    protected function _change_map_coord($url)
    {
        // 使用 snoopy 进行发送
        $snoopy = new \Org\Net\Snoopy();
        $result = $snoopy->fetch($url);
        // 网络返回结果判断
        if ($result === false) {
            Log::record('error:偏移纠正处网络错误');

            return false;
        }
        // josn 解析判断
        $results = json_decode($snoopy->results, true);
        if ($results === null) {
            Log::record('error:偏移纠正处json解析错误');

            return false;
        }

        return $results;
    }

    /**
     * 反地址解析获得地理位置
     *
     * @param $lat 纬度
     * @param $lng 经度
     * @return void
     */
    protected function _get_address_by_latlng($lat, $lng)
    {
        // 使用 snoopy 进行发送
        $snoopy = new \Org\Net\Snoopy();
        // 反地址解析参数
        if (empty($lat) || empty($lng)) {
            Log::record('error:坐标转换接口出错，缺少lat、lng参数');

            return false;
        }
        $location = $lat . ',' . $lng;
        $url = sprintf(self::BAIDU_LOCATION_URL, $location, self::BAIDU_MAP_KEY);
        // snoopy fetch 方法
        $result = $snoopy->fetch($url);
        // 网络返回结果判断
        if ($result === false) {
            Log::record('error:返地址解析处网络错误' . "\t" . $this->errmsg . "\t" . $url);

            return false;
        }
        // josn 解析判断
        $results = json_decode($snoopy->results, true);
        if ($results === null) {
            Log::record('error:返地址解析处json解析错误' . "\t" . $url . "\t" . $snoopy->results);

            return false;
        }
        // 返回结果状态判断
        if (! isset($results['status'])) {
            Log::record('error:反地址解析状态缺少' . "\t" . $this->errmsg . "\t" . $url . "\t" . print_r($results, true));

            return false;
        }
        if ($results['status'] != 0) {
            Log::record('error:反地址解析错误' . "\t" . $this->errmsg . "\t" . $url . "\t" . print_r($result, true));

            return false;
        }
        // 构造统一的输出
        $results = array(
            'address' => isset($results['result']['formatted_address']) ? $results['result']['formatted_address'] : '',
            'source' => $results
        );

        // Log::record('百度解析地址：----------'.var_export($results, true));
        return $results;
    }

    /**
     * 获得地理位置（百度地图接口）
     *
     * @param float $lng
     *            经度
     * @param float $lat
     *            纬度
     */
    protected function _get_address_by_baidu($lng, $lat)
    {
        $this->conver_to_baidu($lat, $lng);

        return $this->_get_address_by_latlng($lat, $lng);
    }

    /**
     * 获得地理位置（腾讯地图接口）
     *
     * @param float $lng
     *            经度
     * @param float $lat
     *            纬度
     * @return string
     */
    protected function _get_address_by_qq($lng, $lat, $coord_type = 1)
    {
        $get_poi = 0;
        $url = sprintf(self::QQ_GEOCODER_URL, $lat, $lng, $coord_type, $get_poi, self::QQ_MAP_KEY);
        // 使用 snoopy 进行发送
        $snoopy = new \Org\Net\Snoopy();
        // snoopy fetch 方法
        $result = $snoopy->fetch($url);
        // 网络返回结果判断
        if ($result === false) {
            Log::record('error:读取逆地址解析错误' . "\t" . $this->errmsg . "\t" . $url);

            return false;
        }
        // josn 解析判断
        $results = json_decode($snoopy->results, true);
        if ($results === null) {
            Log::record('error:逆地址解析读取json解析错误' . "\t" . $this->errmsg . "\t" . $url . "\t" . $snoopy->results);

            return false;
        }
        // 返回结果状态判断
        if (! isset($results['status'])) {
            Log::record('error:逆地址解析返回状态错误' . "\t" . $this->errmsg . "\t" . $url . "\t" . print_r($results, true));

            return false;
        }
        if ($results['status'] != 0) {
            Log::record('error:解析地理位置数据出错' . "\t" . $this->errmsg . "\t" . $url . "\t" . print_r($result, true));

            return false;
        }
        // 构造统一的输出
        $results = array(
            'address' => isset($results['result']['formatted_address']) ? $results['result']['formatted_address'] : $results['result']['address'],
            'source' => $results
        );

        // Log::record('腾讯解析地址：----------'.var_export($results, true));
        return $results;
    }

    /**
     * 获取地理位置信息
     *
     * @param unknown $lng
     *            经度
     * @param unknown $lat
     *            纬度
     * @param string $provider
     * @return array|false + source
     */
    public function get_address($lng, $lat, $provider = '')
    {
        $provider = empty($provider) ? $this->_provider : $provider;
        switch ($provider) {
            case 'baidu':
                return $this->_get_address_by_baidu($lng, $lat);
                break;
            case 'sogou':
                return $this->_get_address_by_sogou($lng, $lat);
                break;
            case 'qq':
                return $this->_get_address_by_qq($lng, $lat);
                break;
            default:
                return $this->_get_address_by_baidu($lng, $lat);
                break;
        }
    }

    /**
     * 转换为腾讯坐标
     *
     * @param $lat 引用结果
     * @param $lng 引用结果
     * @param int $type
     *            类型
     *            1 GPS坐标
     *            2 sogou经纬度
     *            3 baidu经纬度
     *            4 mapbar经纬度
     *            5 [默认]腾讯、google、高德坐标
     *            6 sogou墨卡托
     */
    public function conver_to_tencent(&$lat, &$lng, $type = 1)
    {
        $url = sprintf(self::QQ_TRANSLATE, $lat . ',' . $lng, $type, self::QQ_MAP_KEY);
        // 使用 snoopy 进行发送
        $snoopy = new \Org\Net\Snoopy();
        // snoopy fetch 方法
        $result = $snoopy->fetch($url);
        // 网络返回结果判断
        if ($result === false) {
            Log::record('error:访问坐标转换接口错误' . "\t" . $this->errmsg . "\t" . $url);

            return false;
        }
        // josn 解析判断
        $results = json_decode($snoopy->results, true);
        if ($results === null) {
            Log::record('error:坐标转换返回结果json解析错误' . "\t" . $this->errmsg . "\t" . $url . "\t" . $snoopy->results);

            return false;
        }
        // 返回结果状态判断
        if (! isset($results['status'])) {
            Log::record('error:坐标转换返回状态错误' . "\t" . $this->errmsg . "\t" . $url . "\t" . print_r($results, true));

            return false;
        }
        if ($results['status'] != 0) {
            Log::record('error:坐标转换返回数据错误' . "\t" . $this->errmsg . "\t" . $url . "\t" . print_r($result, true));

            return false;
        }
        $cur_location = reset($results['locations']);
        $lat = $cur_location['lat'];
        $lng = $cur_location['lng'];

        return true;
    }

    /**
     * 转换为百度坐标
     *
     * @param $lat 纬度
     * @param $lng 经度
     * @param int $from
     *            源坐标类型
     *            1：GPS设备获取的角度坐标，wgs84坐标;
     *            2：GPS获取的米制坐标、sogou地图所用坐标;
     *            3：google地图、soso地图、aliyun地图、mapabc地图和amap地图所用坐标，国测局坐标;
     *            4：3中列表地图坐标对应的米制坐标;
     *            5：百度地图采用的经纬度坐标;
     *            6：百度地图采用的米制坐标;
     *            7：mapbar地图坐标;
     *            8：51地图坐标
     * @param int $to
     *            目的坐标类型
     *            5：bd09ll(百度经纬度坐标),
     *            6：bd09mc(百度米制经纬度坐标);
     */
    public function conver_to_baidu(&$lat, &$lng, $from = 1, $to = 5)
    {
        $url = sprintf(self::BAIDU_TRANSLATE, $lng . ',' . $lat, $from, $to, self::BAIDU_MAP_KEY);
        // 使用 snoopy 进行发送
        $snoopy = new \Org\Net\Snoopy();
        // snoopy fetch 方法
        $result = $snoopy->fetch($url);
        // 网络返回结果判断
        if ($result === false) {
            Log::record('error:访问百度地图坐标转换接口错误' . "\t" . $this->errmsg . "\t" . $url);

            return false;
        }
        // josn 解析判断
        $results = json_decode($snoopy->results, true);
        if ($results === null) {
            Log::record('error:百度地图坐标转换返回结果json解析错误' . "\t" . $this->errmsg . "\t" . $url . "\t" . $snoopy->results);

            return false;
        }
        // 返回结果状态判断
        if (! isset($results['status'])) {
            Log::record('error:百度地图坐标转换返回状态错误' . "\t" . $this->errmsg . "\t" . $url . "\t" . print_r($results, true));

            return false;
        }
        if ($results['status'] != 0) {
            Log::record('error:坐标地图转换返回数据错误' . "\t" . $this->errmsg . "\t" . $url . "\t" . print_r($result, true));

            return false;
        }
        $cur_location = reset($results['result']);
        $lat = $cur_location['y'];
        $lng = $cur_location['x'];

        return true;
    }

    /**
     * 角度 => 弧度
     */
    public function rad($dis)
    {
        return round($dis * (M_PI / 180), 6);
    }

    /**
     * 计算经纬度之间的距离
     *
     * @param float $lat1
     *            经度
     * @param float $lng1
     *            纬度
     * @param float $lat2
     *            经度
     * @param float $lng2
     *            纬度
     */
    public function get_distance($lat1, $lng1, $lat2, $lng2)
    {
        $lat1 = round($lat1, 6);
        $lng1 = round($lng1, 6);
        $lat2 = round($lat2, 6);
        $lng2 = round($lng2, 6);
        $radLat1 = $this->rad($lat1);
        $radLat2 = $this->rad($lat2);
        $a = $radLat1 - $radLat2;
        $b = $this->rad($lng1) - $this->rad($lng2);
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s = round($s * self::EARTH_RADIUS, 0);

        return $s;
    }
}
