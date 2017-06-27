<?php
/**
 * 文件系统操作
 * User: zhuxun37
 * Date: 2017/4/5
 * Time: 上午10:24
 */
namespace Common\Common;

class FileSystem
{

    /**
     * 单例实例化
     *
     * @return FileSystem
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
     * 获取文件真实路径
     * @param string $file 文件相对路径
     * @return string
     */
    public function getRealPath($file)
    {

        return APP_PATH . '../' . $this->amendPath($file);
    }

    /**
     * 生成缓存文件路径
     * @param string $file 文件相对路径
     * @return mixed
     */
    public function amendPath($file)
    {

        $file = trim($file);
        $file = preg_replace('/\.*\//i', '/', $file);
        $file = preg_replace('/\/+/i', '/', $file);
        $file = preg_replace('/\/*(.*?)\/*$/i', '$1', $file);
        return $file;
    }

    /**
     * 获取指定目录和文件后缀的所有文件列表
     * @param string $dir     目录, 相对于 APP_PATH
     * @param array  $fileExt 文件后缀列表
     * @param int    $depth   深度
     * @return array
     */
    public function scanFile($dir, $fileExt = array(), $depth = -1)
    {

        $dirFiles = array();
        $realDir = $this->getRealPath($dir);

        // 如果深度为 0, 则返回
        if (0 == $depth) {
            return $dirFiles;
        }

        $depth--;
        $fileExt = (array)$fileExt;
        // 如果打不开目录
        if (!$handle = opendir($realDir)) {
            return $dirFiles;
        }

        // 读取目录下的文件
        while (($file = readdir($handle)) !== false) {
            // 过滤隐藏文件
            $arrFileName = explode('.', $file);
            if ($file == ".." || $file == "." || empty($arrFileName[0])) {
                continue;
            }
            // 如果是目录, 则递归
            if (is_dir($realDir . "/" . $file)) {
                $dirFiles = array_merge($dirFiles, $this->scanFile($dir . '/' . $file, $fileExt, $depth));
                continue;
            }

            $currentExt = substr($file, strrpos($file, '.') + 1); // 获取后缀
            if (empty($fileExt) || in_array($currentExt, $fileExt)) {
                $dirFiles[] = $dir . '/' . $file;
            }
        }

        closedir($handle);
        return $dirFiles;
    }

    /**
     * 获取某目录下所有文件、目录名
     * @param string $dir 路径
     * @return array 返回
     */
    public function scanDir($dir)
    {

        $realDir = $this->getRealPath($dir) . '/';
        $files = array();
        // 如果打不开目录
        if (!$handle = opendir($realDir)) {
            return $files;
        }

        // 读取目录下的文件
        while (($file = readdir($handle)) !== false) {
            // 过滤隐藏文件
            $arrFileName = explode('.', $file);
            if ($file != ".." && $file != "." && !empty($arrFileName[0])) {
                if (is_dir($realDir . "/" . $file)) {
                    $files[] = array(
                        'name' => empty($dir) ? $file : $dir . '/' . $file,
                        'time' => rgmdate(@filemtime($realDir . $file), 'Y-m-d H:i:s'),
                        'type' => 'dir'
                    );
                } else {
                    $ext = substr($file, strrpos($file, '.') + 1); // 获取后缀
                    if ('php' == $ext) {
                        $files[] = array(
                            'name' => empty($dir) ? $file : $dir . '/' . $file,
                            'time' => rgmdate(@filemtime($realDir . $file), 'Y-m-d H:i:s'),
                            'type' => 'file'
                        );
                    }
                }
            }
        }

        closedir($handle);
        return $files;
    }

}