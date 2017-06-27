<?php
/**
 * function.php
 * 项目全局方法
 * $Author$
 * $Id$
 */

/**
 * [is_in_range 判断一个数据是否在两个数据之间]
 *
 * @param [type] $x [description]
 * @param [type] $min [description]
 * @param [type] $max [description]
 *
 * @return boolean [description]
 */
function is_in_range($x, $min, $max)
{
    return $x > $min && $x < $max;
}

/**
 * 给出附件的at_id返回附件访问的绝对url地址
 *
 * @param number $at_id 附件id
 * @param int    $width 宽度
 *
 * @return string
 */
function attachment_url($at_id, $width = 0)
{
    $host = cfg('FILE_SERVER_URL');
    $url = $host . '/attachment/read/' . $at_id;
    if (0 < $width) {
        $url .= '/' . $width;
    }

    return $url;
}

/**
 * 解析响应头信息
 *
 * @param array $header  （引用返回）响应头列表
 * @param array $cookies （引用返回）响应的 Cookie 列表
 * @param array $headers 原始头信息数组
 *
 * @return boolean
 */
function parse_headers(&$header, &$cookies, $headers)
{

    // 头部参数
    $header = array();
    // 响应的 cookie
    $cookies = array();
    // 最后一个头信息参数
    $last_header = array();

    $header_lines = $headers;
    array_shift($header_lines);
    foreach ($header_lines as $header_line) {
        parse_header_line($header_line, $header, $last_header);
    }

    $set_cookie = 'set-cookie';
    if (array_key_exists($set_cookie, $header)) {
        if (is_array($header[$set_cookie])) {
            $cookies = $header[$set_cookie];
        } else {
            $cookies = array(
                $header[$set_cookie]
            );
        }

        foreach ($cookies as $cookie_str) {
            parse_cookie($cookie_str, $cookies);
        }

        unset($header[$set_cookie]);
    }

    foreach (array_keys($header) as $k) {
        if (is_array($header[$k])) {
            $header[$k] = implode(', ', $header[$k]);
        }
    }

    return true;
}

/**
 * Parses the line from HTTP response filling $headers array
 * The method should be called after reading the line from socket or receiving-
 * it into cURL callback.
 * Passing an empty string here indicates the end of
 * response headers and triggers additional processing, so be sure to pass an
 * empty string in the end.
 *
 * @param string $header_line Line from HTTP response
 * @param array  $header
 * @param string $last_header
 *
 * @return bool
 */
function parse_header_line($header_line, &$header, &$last_header)
{
    $header_line = trim($header_line, "\r\n");
    // string of the form header-name: header value
    if (preg_match('!^([^\x00-\x1f\x7f-\xff()<>@,;:\\\\"/\[\]?={}\s]+):(.+)$!', $header_line, $m)) {
        $name = strtolower($m[1]);
        $value = trim($m[2]);
        $header[$name] = $value;

        if (! is_array($header[$name])) {
            $header[$name] = array(
                $header[$name]
            );
        }

        $header[$name][] = $value;
        $last_header = $name;
    } elseif (preg_match('!^\s+(.+)$!', $header_line, $m) && $last_header) {
        if (! is_array($header[$this->_last_header])) {
            $header[$this->_last_header] .= ' ' . trim($m[1]);
        } else {
            $key = count($header[$this->_last_header]) - 1;
            $header[$this->_last_header][$key] .= ' ' . trim($m[1]);
        }
    }

    return true;
}

/**
 * Parses a Set-Cookie header to fill $cookies array
 *
 * @param string $cookie_str value of Set-Cookie header
 * @param array  $cookies
 *
 * @link http://cgi.netscape.com/newsref/std/cookie_spec.html
 * @return bool
 */
function parse_cookie($cookie_str, &$cookies)
{
    $cookie = array(
        'expires' => null,
        'domain' => null,
        'path' => null,
        'secure' => false
    );

    // Only a name=value pair
    if (! strpos($cookie_str, ';')) {
        $pos = strpos($cookie_str, '=');
        $cookie['name'] = trim(substr($cookie_str, 0, $pos));
        $cookie['value'] = trim(substr($cookie_str, $pos + 1));
    } else {
        // Some optional parameters are supplied
        $elements = explode(';', $cookie_str);
        $pos = strpos($elements[0], '=');
        $cookie['name'] = trim(substr($elements[0], 0, $pos));
        $cookie['value'] = trim(substr($elements[0], $pos + 1));

        for ($i = 1; $i < count($elements); $i ++) {
            if (false === strpos($elements[$i], '=')) {
                $el_name = trim($elements[$i]);
                $el_value = null;
            } else {
                list($el_name, $el_value) = array_map('trim', explode('=', $elements[$i]));
            }

            $el_name = strtolower($el_name);
            if ('secure' == $el_name) {
                $cookie['secure'] = true;
            } elseif ('expires' == $el_name) {
                $cookie['expires'] = str_replace('"', '', $el_value);
            } elseif ('path' == $el_name || 'domain' == $el_name) {
                $cookie[$el_name] = urldecode($el_value);
            } else {
                $cookie[$el_name] = $el_value;
            }
        }
    }

    $cookies[] = $cookie;

    return true;
}

/**
 * 返回系统完整的绝对 URL 路径
 *
 * @param string         $url    URL表达式，格式：'[模块/控制器/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param string|array   $vars   传入的参数，支持数组和字符串
 * @param string|boolean $suffix 伪静态后缀，默认为true表示获取配置值
 * @param bool           $domain 是否显示域名
 *
 * @return string
 */
function convertUrl($url = '', $vars = '', $suffix = false, $domain = true)
{
    return U($url, $vars, $suffix, $domain);
}
