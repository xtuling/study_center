<?php
/**
 * Api 文档接口解析操作
 * User: zhuxun37
 * Date: 2017/4/5
 * Time: 上午11:21
 */
namespace Common\Common;

class ApiDoc
{

    /**
     * 单例实例化
     *
     * @return ApiDoc
     */
    public static function &instance()
    {

        static $instance;
        if (empty($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    public function __construct()
    {

        // do nothing.
    }

    /**
     * 抽取 class 类, 导出并使用新类名
     * @param string $className  新类名
     * @param string $phpContent PHP文件内容
     * @return bool|string
     */
    public function extractClass($className, $phpContent)
    {

        // 匹配类文件
        if (!preg_match('/(abstract\s+)?class\s+([0-9a-z\_]+)\s+(extends\s+[0-9a-z\\\_]+)?\s*\{(.*)\}/is', $phpContent, $classes)) {
            E('_ERR_CLASS_NOT_EXIST');
            return false;
        }
        // 忽略 abstract 类
        if ('abstract' == substr($classes[1], 0, 8)) {
            E('_ERR_CLASS_IS_ABSTRACT');
            return false;
        }
        // 匹配 use
        preg_match_all('/\s*use\s+([0-9a-z\\\_]+)\s*\;/i', $phpContent, $uses);

        // 生成新文件
        return "<?php\n" . implode("\n", $uses[0]) . "\nclass {$className}\n" . '{' . $classes[4] . '}';
    }

    /**
     * 剔除注释符号
     * @param string $line 注释行
     * @return mixed|string
     */
    protected function _trimLineComment($line)
    {

        $line = trim($line);
        // 多空格替换成单空格
        $line = preg_replace('/\s+/i', ' ', $line);
        // 剔除 */ 注释符合
        $line = preg_replace('/\s*\*+\//i', '', $line);
        // 剔除 *|# 注释符号
        $line = preg_replace('/^(\*|\#)\s*/i', '', $line);
        // 剔除 /*|/** 注释符号
        $line = preg_replace('/^\/\*\s*\**\s*/i', '', $line);

        return $line;
    }

    /**
     * 把多行注释合并成单行注释, 并剔除注释符号
     * @param array $comments 注释行数组
     * @return array
     */
    protected function _mergeMultiComment($comments)
    {

        $return = array();
        // 剔除注释, 把多行注释合并成单行注释
        foreach ($comments as $_cmt) {
            // 剔除注释
            $_cmt = $this->_trimLineComment($_cmt);
            if (empty($_cmt)) {
                continue;
            }

            // 如果已经有解析的数据并且该行注释不是以 @ 开头, 则合并到上一行注释中
            if (!empty($return) && '@' != $_cmt{0}) {
                $count = count($return);
                $return[$count - 1] .= "\0" . $_cmt;
            } else {
                $return[] = $_cmt;
            }
        }

        return $return;
    }

    /**
     * 从注释中获取描述信息
     * @param string $comment 注释信息
     * @return string
     */
    public function parseComment($comment)
    {

        $comments = explode("\n", str_replace("\r", "\n", $comment));
        $comments = $this->_mergeMultiComment($comments);

        $return = array();
        // 根据注释符号对注释行进行解析
        foreach ($comments as $_cmt) {
            // 如果该行不是以 @ 开头
            if ('@' != $_cmt{0}) {
                $return['description'] = empty($return['description']) ? $_cmt : $return['description'];
                continue;
            }

            // 根据注释标识解析数据
            $tmpComments = explode(' ', $_cmt);
            if (2 > count($tmpComments)) {
                continue;
            }

            // 解析注释
            $func = '_parse' . substr($tmpComments[0], 1);
            if (method_exists($this, $func)) { // 如果解析方法存在
                $this->$func($return, $tmpComments);
            }
        }

        // 由于请求参数的特殊性, 所以需要重新再解析(解析是否必填以及默认值)
        $this->_parseParamReqDef($return['param']);

        return $return;
    }

    /**
     * 解析 @ param 注释
     * @param array $result 输入/输出参数
     * @param array $data   注释数据
     * @return bool
     */
    protected function _parseParam(&$result, $data)
    {

        return $this->_parseParamReturn($result, $data);
    }

    /**
     * 解析 @ desc 注释
     * @param array $result 输入/输出参数
     * @param array $data   注释数据
     * @return bool
     */
    protected function _parseDesc(&$result, $data)
    {

        $flag = substr($data[0], 1);
        $result[$flag] = implode(' ', array_slice($data, 1));
        return true;
    }

    /**
     * 解析 @ return 注释
     * @param array $result 输入输出参数
     * @param array $data   注释数据
     * @return bool
     */
    protected function _parseReturn(&$result, $data)
    {

        return $this->_parseParamReturn($result, $data);
    }

    /**
     * 检查脚本的语法错误
     * @param string $content PHP脚本
     * @return int
     */
    protected function _checkSyntax($content)
    {

        $file = get_sitedir() . '_syntax.php';
        file_put_contents($file, "<?php\n{$content};");
        exec("php -l {$file}", $result);
        @unlink($file);
        // No syntax errors detected
        return preg_match('/^No syntax errors detected/i', $result[0]);
    }

    /**
     * 解析 @ param, @ return 注释
     * @param array $result 输入/输出参数
     * @param array $data   注释数据
     * @return bool
     */
    protected function _parseParamReturn(&$result, $data)
    {

        $flag = substr($data[0], 1);
        $source = implode(' ', $data);
        $return = array();
        if (preg_match('/array\s*\((.*)\)/i', $source, $matches)) {
            list($field2comment, $source) = $this->_parseExampleComment($matches[0]);
            // 如果语法没有问题
            if (!$this->_checkSyntax($source)) {
                E('_ERR_PARAM_RETURN_SYNTAX_ERROR');
                return false;
            }
            eval('$return=' . $source . ';');
            if (!empty($return)) {
                $this->_array2ParamReturn($result, $flag, $field2comment, $return);
                return true;
            }
        }

        if (2 == count($data)) {
            /**
             * 如果数据为两项, 则说明该行注释格式为 @param $page
             */
            if (!empty($this->_types) && in_array($data[1], $this->_types)) {
                $param = array(
                    'type' => '',
                    'name' => $data[1]
                );
            } else {
                return true;
            }
        } else {
            /**
             * 如果数据为两项以上, 则说明该行注释格式为
             * @param int $page 分页数据
             */
            $comment = array_slice($data, 3);
            $param = array(
                'type' => $data[1],
                'name' => $data[2],
                'desc' => empty($comment) ? '' : implode(' ', $comment)
            );
        }

        if (empty($result[$flag])) {
            $result[$flag] = array();
        }

        $result[$flag][] = $param;
        return true;
    }

    /**
     * 把数组字串转成参数或返回值
     * @param array  $result        输入/输出参数
     * @param string $flag          参数标识
     * @param array  $field2comment 字段和注释对照表
     * @param string $prefix        键值前缀
     * @param array  $array         数组数据
     * @return bool
     */
    protected function _array2ParamReturn(&$result, $flag, $field2comment, $array, $prefix = '')
    {

        $cleared = false;
        foreach ($array as $_k => $_val) {
            $type = gettype($_val);
            $index = -1;
            if (preg_match('/^(\d+)\0/i', $_k, $matches)) {
                $_k = str_replace($matches[1] . "\0", '', $_k);
                $index = $matches[1];
            }

            $intK = (int)$_k;
            // 如果不是数组或对象
            if (empty($_val) || !in_array($type, array('object', 'array'))) {
                if ($_k == $intK && strlen($_k) == strlen($intK)) {
                    continue;
                }

                // 清除最后一条记录的默认值
                if (!$cleared) {
                    $this->_clearLastDefault($result[$flag]);
                    $cleared = true;
                }

                // 字段信息
                $result[$flag][] = array(
                    'name' => empty($prefix) ? $_k : $prefix . '.' . $_k,
                    'type' => $type,
                    'desc' => empty($field2comment[$index]) ? '' : $field2comment[$index],
                    'default' => $_val
                );
                continue;
            }

            // 清除最后一条记录的默认值
            if (!$cleared) {
                $this->_clearLastDefault($result[$flag]);
                $cleared = true;
            }

            // 以下是对值为数组数据的处理
            if ($_k == $intK && strlen($_k) == strlen($intK)) {
                $this->_array2ParamReturn($result, $flag, $field2comment, $_val, $prefix . '[]');
            } else {
                $result[$flag][] = array(
                    'name' => empty($prefix) ? $_k : $prefix . '.' . $_k,
                    'type' => $type,
                    'desc' => empty($field2comment[$index]) ? '' : $field2comment[$index],
                    'default' => var_export($_val, true)
                );
                $this->_array2ParamReturn($result, $flag, $field2comment, $_val, empty($prefix) ? $_k : $prefix . '.' . $_k);
            }

        }

        return true;
    }

    /**
     * 清理默认值
     * @param array $result 默认值数据
     * @return bool
     */
    protected function _clearLastDefault(&$result)
    {

        if (empty($result) || !is_array($result)) {
            return true;
        }

        $lastKey = count($result) - 1;
        if (empty($result[$lastKey]['default'])) {
            return true;
        }

        $result[$lastKey]['default'] = '';
        return true;
    }

    /**
     * 从示例中获取注释
     * 注意: 由于前面对示例参数处理时, 多行数据是以 \0 分隔
     * @param array $data 示例数据
     * @return array
     */
    protected function _parseExampleComment($data)
    {

        $field2comment = array();
        $lines = explode("\0", $data);
        foreach ($lines as &$_line) {
            $_line = trim($_line);
            // 如果该行为非字段信息, 则忽略
            if (!preg_match('/^(\'|\")(.*?)(\'|\")\s*\=\>/i', $_line) && !preg_match('/\d+\s*\=\>/i', $_line)) {
                continue;
            }

            // 把注释信息从行字串里剔除
            if (preg_match('/(\'|\"|\)|true|false|\d+)(\,?)\s*\/\/\s*(.*?)$/i', $_line, $matches)) { // 值为 int/string/bool 类型时的注释处理
                $comment = $matches[3];
                $_line = str_replace($matches[0], $matches[1] . $matches[2], $_line);
            } elseif (preg_match('/(array|array\s*\(|\()\s*\/\/\s*(.*?)$/i', $_line, $matches)) {
                $comment = $matches[2];
                $_line = str_replace($matches[0], $matches[1], $_line);
            } else {
                continue;
            }

            // 把注释推入数组
            $field2comment[] = $comment;
            // 给当前字段做标记
            $count = count($field2comment) - 1;
            $_line = preg_replace('/^(\d+)\s*\=\>/i', '\'' . "{$count}\0" . '$1\'=>', $_line);
            $_line = preg_replace('/^(\'|\")(.*?)(\'|\")\s*\=\>/i', '\'' . "{$count}\0" . '$2\'=>', $_line);
        }

        return array($field2comment, implode('', $lines));
    }

    /**
     * 如果分隔符在名称中存在, 则获取分隔符在描述中的最大位置
     * @param string $name 变量字串
     * @param string $desc 描述字串
     * @return mixed
     */
    protected function _getMaxSepPos($name, $desc)
    {

        $dPos = $sPos = $sBPos = $bPos = 0;
        // 如果名称中包含 "
        if (0 < (substr_count($name, '"') % 2)) {
            $dPos = stripos($desc, '"');
        }
        // 如果名称中包含 '
        if (0 < (substr_count($name, "'") % 2)) {
            $sPos = stripos($desc, "'");
        }
        // 如果名称中包含 [
        if (0 < (substr_count($name, '[') % 2)) {
            $sBPos = stripos($desc, "[");
        }
        // 如果名称中包含 {
        if (0 < (substr_count($name, '{') % 2)) {
            $bPos = stripos($desc, "{");
        }

        // 取最大值
        $max = max($dPos, $sBPos, $sPos, $bPos);

        return $max;
    }

    /**
     * 解析变量的默认值以及是否必填
     * @param array $params 注释信息
     * @return bool
     */
    protected function _parseParamReqDef(&$params)
    {

        foreach ($params as &$_param) {
            $name = $_param['name'];
            // 如果未指定默认值或是否必须
            if (false === stripos($name, ':')) {
                $_param['require'] = false;
                $_param['default'] = empty($_param['default']) ? '' : $_param['default'];
                continue;
            }

            // 取默认值的分隔符号最大值
            $max = $this->_getMaxSepPos($name, $_param['desc']);
            if (0 == $max) {
                $names = explode(':', $name);
            } else {
                // 把属于签名描述部分的字串, 合并回去
                $name .= ' ' . substr($_param['desc'], 0, $max + 1);
                // 获取描述
                $_param['desc'] = substr($_param['desc'], $max + 2);

                // 把名称按 : 切分
                $lastChar = $name{strlen($name) - 1};
                $fPos = stripos($name, $lastChar);

                // 整理变量切成3部分
                $default = substr($name, $fPos + 1, -1);
                $names = explode(':', substr($name, 0, $fPos - 1));
                $names[] = $default;
            }

            $count = count($names);
            $requireTrue = array('t', 'true');
            $requireTF = array('t', 'f', 'true', 'false');
            if (3 < $count) { // 大于3个值, 则不处理
                $_param['name'] = implode(':', $names);
            } elseif (2 == $count) { // 两个值时
                $_param['name'] = $names[0];
                if (empty($names[1])) { // 第二个值为空
                    // do nothing.
                } elseif (in_array(strtolower($names[1]), $requireTF)) { // 变量:requre
                    $_param['require'] = in_array(strtolower($names[1]), $requireTrue) ? 'true' : 'false';
                } else { // 变量:default
                    $_param['default'] = $names[1];
                }
            } elseif (3 == $count) {
                $_param['name'] = $names[0];
                $_param['require'] = in_array(strtolower($names[1]), $requireTrue) ? 'true' : 'false';
                $_param['default'] = $names[2];
            }
        }

        return true;
    }

    /**
     * 生成请求参数格式
     * @param array $params 请求参数
     * @return array
     */
    public function createParamExample($params)
    {

        $return = array();
        foreach ($params as $_p) {
            $ps = explode('.', $_p['name']);
            if (1 == count($ps)) {
                if (!isset($return[$_p['name']])) {
                    $return[$_p['name']] = $this->_createTypeDef($_p['type'], $_p['default']);
                }
                continue;
            }

            $this->_createArrayStruct($return, $ps, $_p['type'], $_p['default']);
        }

        return $return;
    }

    /**
     * 创建数组结构并初始化
     * @param array  $return  输入/输出参数
     * @param array  $keys    键值数组
     * @param string $type    数据类型
     * @param string $default 默认值
     * @return bool
     */
    protected function _createArrayStruct(&$return, $keys, $type, $default)
    {

        // 如果键值为空
        if (!is_array($keys) || empty($keys)) {
            return true;
        }

        // 取第一个键值
        $k = array_shift($keys);
        $trueKey = str_replace('[]', '', $k);
        if (!isset($return[$trueKey])) {
            $return[$trueKey] = array();
        }

        // 如果只剩最后一个键, 则赋默认值
        if (1 == count($keys)) {
            $lastKey = array_shift($keys);
            if ($k == $trueKey) { // 如果键值不是数组形式
                if (!isset($return[$trueKey][$lastKey])) {
                    $return[$trueKey][$lastKey] = $this->_createTypeDef($type, $default);
                }
            } else {
                // 键值是数组形式时, 初始值多加一级数组
                if (!isset($return[$trueKey][0])) {
                    $return[$trueKey][0] = array();
                }
                $return[$trueKey][0][$lastKey] = $this->_createTypeDef($type, $default);
            }
        } else { // 如果还有下级
            if ($k == $trueKey) { // 如果键值不是数组形式
                $this->_createArrayStruct($return[$trueKey], $keys, $type, $default);
            } else {
                $this->_createArrayStruct($return[$trueKey][0], $keys, $type, $default);
            }
        }

        return true;
    }

    /**
     * 根据类型获取默认值
     * @param string $type    类型字串
     * @param string $default 默认值
     * @return array|string
     */
    protected function _createTypeDef($type, $default)
    {

        // 如果有默认值
        if (!empty($default) || 0 < strlen($default)) {
            if ('array' == $type) {
                eval('$default = ' . $default . ';');
                return $default;
            }

            return $default;
        }

        switch ($type) {
            case 'array':
                return array();
            case 'string':
                return '';
            case 'int':
                return 0;
            default:
                return '';
        }
    }

}