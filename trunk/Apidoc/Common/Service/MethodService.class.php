<?php
/**
 * MethodService.class.php
 * $author$
 */

namespace Common\Service;

use Common\Common\ApiDoc;
use Common\Common\FileSystem;

class MethodService extends AbstractService
{

    protected $_types = array();

    /**
     * 构造方法
     */
    public function __construct()
    {

        $this->_types = array("boolean", "integer", "double", "float", "string", "array", "object");
        parent::__construct();
    }

    /**
     * 清除类方法缓存
     * @param string $dir 目录
     * @return array|bool
     */
    public function clearMethodCache($dir)
    {

        // 如果文件目录为空, 则不显示接口
        if (empty($dir)) {
            return array();
        }

        $root = dirname(APP_PATH);
        $cacheDir = str_replace($root, '', get_sitedir());
        $files = FileSystem::instance()->scanFile(substr($cacheDir, 1, -1), 'php', 1);
        $filename = $this->_getCacheFileName($dir);
        foreach ($files as $_file) {
            if (!preg_match('/' . $filename . '(_|\.)/i', $_file)) {
                continue;
            }

            @unlink($root . '/' . $_file);
        }

        return true;
    }

    /**
     * 获取指定目录的接口列表
     * @param string $dir 当前文件/目录
     * @return array
     */
    public function listMethodByDir($dir)
    {

        // 如果文件目录为空, 则不显示接口
        if (empty($dir)) {
            return array();
        }

        $methods = array();
        $root = dirname(APP_PATH);
        $cacheDir = str_replace($root, '', get_sitedir());
        $files = FileSystem::instance()->scanFile(substr($cacheDir, 1, -1), 'php', 1);
        $filename = $this->_getCacheFileName($dir);
        foreach ($files as $_file) {
            if (!preg_match('/' . $filename . '(_|\.)/i', $_file)) {
                continue;
            }

            $fileMethods = include $root . '/' . $_file;
            $fileMethods = array_values($fileMethods);
            if (empty($fileMethods)) {
                continue;
            }
            $methods = array_merge($methods, $fileMethods);
        }

        return $methods;
    }

    /**
     * 获取类中的方法数据
     * @param string $class       类名
     * @param string $file        文件路径
     * @param array  $fileMethods 类方法列表
     * @return mixed
     */
    public function getMethodData($class, $file, $fileMethods = array())
    {

        // 获取类中所有方法
        $methods = get_class_methods($class);
        $apiMethods = array();
        if (empty($methods)) {
            return $apiMethods;
        }

        // 遍历所有方法
        foreach ($methods as $_method) {
            $rflMethod = new \Reflectionmethod($class, $_method);
            if (!$rflMethod->isPublic() || strpos($_method, '__') === 0 || in_array($_method, array('getRules', 'before_action'))) {
                continue;
            }

            $title = '//请检测函数注释';
            $desc = '//请使用 @desc 注释';

            $docComment = $rflMethod->getDocComment(); // 获取评论
            $comments = ApiDoc::instance()->parseComment($docComment);
            $service = $class . '.' . $_method;
            $apiMethods[$service] = array(
                'service' => $this->file2api($file, $_method),
                'title' => empty($comments['description']) ? $title : $comments['description'],
                'desc' => empty($comments['desc']) ? $desc : $comments['desc'],
                'file' => $file,
                'method' => $service,
                'shortMethod' => substr($class, strrpos($class, '_') + 1) . '.' . $_method
            );
        }

        $cacheFile = get_sitedir() . $this->_getCacheFileName($file);
        $content = file_get_contents($cacheFile);
        if (!empty($fileMethods)) {
            $content = str_replace("\n\nreturn " . var_export($fileMethods, true) . ";", '', $content);
        }

        $content .= "\n\nreturn " . var_export($apiMethods, true) . ";";
        file_put_contents($cacheFile, $content);

        return $apiMethods;
    }

    /**
     * 文件路径和方法名转换成api地址
     * @param string $file   文件名称
     * @param string $method 方法名称
     * @return string
     */
    public function file2api($file, $method)
    {

        static $file2api = array();
        if (!isset($file2api[$file])) {
            $file2api[$file] = str_replace(array('Controller.class.php', '/Controller'), '', $file);
        }

        $method = preg_replace('/_(get|post|delete|put)$/i', '', $method);

        return $file2api[$file] . ('index' == strtolower($method) ? '' : '/' . $method);
    }

    /**
     * 获取数组的维度
     * @param array $array 数组
     * @return int
     */
    protected function _array_depth($array)
    {

        if (!is_array($array)) return 0;
        $max_depth = 1;
        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = $this->_array_depth($value) + 1;

                if ($depth > $max_depth) {
                    $max_depth = $depth;
                }
            }
        }

        return $max_depth;
    }

    /**
     * 生成文件
     * @param string $file 文件名
     * @return array
     */
    public function makeFile($file = '')
    {

        if (!preg_match('/([0-9a-z\_\-]\/(api|apicp)\/)/i', $file)) {
            E('_ERR_NOT_API_FILE');
            return false;
        }

        // 步骤:分析原来的类文件,将继承去掉并获取类名
        $path = FileSystem::instance()->getRealPath($file);
        $phpContent = file_get_contents($path);
        if (empty($phpContent)) {
            die('empty:' . $path);
        }

        $filename = $this->_getCacheFileName($file);
        $className = preg_replace('/\.(.*?)php$/i', '', $filename);

        // 生成新文件
        $newFileName = get_sitedir() . $filename;
        $phpContent = ApiDoc::instance()->extractClass($className, $phpContent);
        if (file_exists($newFileName)) {
            unlink($newFileName) or die ('删除文件:' . $newFileName . '失败');
        }
        file_put_contents($newFileName, $phpContent) or die ('写入文件:' . $newFileName . '失败');

        return array(
            'fileName' => $newFileName,
            'className' => $className
        );
    }

    /**
     * 生成缓存文件路径
     * @param string $file 文件相对路径
     * @return mixed
     */
    protected function _getCacheFileName($file)
    {

        return str_replace('/', '_', FileSystem::instance()->amendPath($file));
    }

}
