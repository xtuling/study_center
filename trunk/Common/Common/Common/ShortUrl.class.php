<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2016/11/14
 * Time: 17:31
 */

namespace Common\Common;

class ShortUrl
{

    /**
     * 创建短网址
     * @param string $url   真实网址
     * @param string $title 网址标题
     * @return string
     */
    public static function create($url, $title = '')
    {

        $postdata = [
            // 帐号
            'username' => cfg('SHORTURL_USERNAME'),
            // 密码
            'password' => cfg('SHORTURL_PASSWORD'),
            // 固定值
            'action' => 'shorturl',
            // 输出格式
            'format' => 'json',
            // 要进行转换的长网址
            'url' => $url,
            // 长网址网页标题
            'title' => $title,
        ];

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => http_build_query($postdata),
                // 超时时间，单位秒
                'timeout' => cfg('SHORTURL_TIMEOUT_SECOND'),
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents(cfg('SHORTURL_APIURL'), false, $context);
        $data = json_decode($result, true);

        if (!is_array($data) || !isset($data['statusCode']) || intval($data['statusCode']) != 200) {
            // 公共类错误代码定义放到 ThinkPHP/Lang
            E('_ERROR_SHORTURL_FAIL');
        }

        return $data['shorturl'];
    }
}
