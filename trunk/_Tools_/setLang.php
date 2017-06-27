<?php
/**
 * setLang.php
 * 用于批量整理错误信息语言包工具
 * @desc 由于需要对代码进行重写操作，强烈建议执行前进行备份！！
 * @uses PHP setLang.php
 * @author Deepseath
 * @version $Id$
 */

$set = new setLang('Public');

class setLang
{
    /**
     * 开发时书写的错误信息语言文件标记符号
     * <pre>
     * 比如：$this->_set_error('ERRORCODE/_ERR_THIS_IS_ERROR/这是错误语言信息');
     * langTag = ERRORCODE/
     * </pre>
     */
    public $langTag = 'ERRORCODE/';

    /**
     * 当前执行整理的应用目录名
     */
    public $dirName = '';

    /**
     * 当前定位的错误码编号
     */
    protected $_curErrCode = null;

    /** 应用根目录 */
    protected $_baseDir = '';
    /** 进行寻找的模块目录列表 */
    protected $_moduleList = [];
    /** 当前处理的模块名 */
    protected $_curModule = '';

    /**
     * 构造方法
     */
    public function __construct($dirName, $langTag = '')
    {
        if (!$dirName) {
            return false;
        }
        $this->dirName = $dirName;
        $this->_baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . $this->dirName . DIRECTORY_SEPARATOR;
        $this->_moduleList = [
            'Api',
            'Apicp',
            'Frontend',
            'Rpc'
        ];

        foreach (scandir($this->_baseDir) as $_filename) {
            if ($_filename == '.' || $_filename == '..') {
                continue;
            }
            if (!in_array($_filename, $this->_moduleList)) {
                continue;
            }

            $files = [];
            $this->_readDir($this->_baseDir . DIRECTORY_SEPARATOR . $_filename, $files);
            //print_r($files);
            foreach ($files as $_file) {
                $this->_resetCodeFile($_file);
            }
        }
    }

    /**
     * 读取代码文件
     * @param unknown $file
     */
    protected function _resetCodeFile($file)
    {
        // 读取代码文件内容
        $codes = file_get_contents($file);
        if (!preg_match('/ERRORCODE\//', $codes)) {
            // 代码不包含错误码信息标记，则忽略该文件处理
            return false;
        }

        $moduleName = '';
        $match = [];
        if (!preg_match('/namespace\s*([^\\\]+)[^;]+;/is', $codes, $match)) {
            return false;
        }
        $moduleName = trim($match[1]);
        if (!in_array($moduleName, $this->_moduleList)) {
            // 如果模块不在指定的整理目录则不处理
            return false;
        }

        $dirName = strtoupper($this->dirName);
        $newLang = [];
        $rewriteLang = false;
        $langFile = '';
        $codes = preg_replace_callback('/\(\s*\'ERRORCODE\/([^\']+)\'\s*\)/is', function ($matches) use ($file, $dirName, $moduleName, &$newLang, &$langFile, &$rewriteLang) {
            if ($this->_curModule && $this->_curModule == $moduleName) {
                // 当前模块未变化，则继续错误码编码不重置，累加使用
                $this->_curErrCode = $this->_curErrCode + 1;
                $langFile = $this->_baseDir . DIRECTORY_SEPARATOR . $this->_curModule . DIRECTORY_SEPARATOR . 'Lang' . DIRECTORY_SEPARATOR . 'zh-cn.php';
            } else {
                // 当前处理的模块已经发生变化，则重新计算错误码
                $langFile = $this->_baseDir . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'Lang' . DIRECTORY_SEPARATOR . 'zh-cn.php';
                if (is_file($langFile)) {
                    $langData = include($langFile);
                    $_max = 0;
                    foreach ($langData as $_msg) {
                        $match = [];
                        if (preg_match('/^\s*(\d+)\s*:/is', $_msg, $match)) {
                            if ($match[1] > $_max) {
                                $_max = $match[1];
                            }
                        }
                    }
                    $this->_curErrCode = $_max + 1;
                    unset($_max);
                } else {
                    $this->_curErrCode = 90000;
                }
                $this->_curModule = $moduleName;
            }

            if (!is_file($langFile)) {
                $langCode = <<<EOF
<?php
/**
 * zh-cn.php
 *
 * Create By Deepseath
 * \$Author\$
 * \$Id\$
 */

return [];

EOF;
                @mkdir(dirname($langFile), 0777, true);
                file_put_contents($langFile, $langCode);
                //$oldLang = [];
            } else {
                //$oldLang = include($langFile);
            }

            if (preg_match('/([^\/]+)\/([^\/]+)/is', trim($matches[1]), $match)) {
                $errVariable = trim($match[1]) . '_' . $this->_curErrCode;
                $newLang[$errVariable] = $this->_curErrCode . ':' . trim($match[2]);
                $rewriteLang = true;

                return "('{$errVariable}')";
            }

            return $matches[0];

        }, $codes);

        if ($rewriteLang === true) {
            // 重写语言包文件
            $langData = file_get_contents($langFile);
            if (preg_match('/^(.+?)\\nreturn\s+/is', $langData, $match)) {
                file_put_contents($langFile, $match[1] . "\nreturn " . var_export(array_merge(include($langFile), $newLang), true) . ";\n");
            }
        }

        // 重写代码文件
        file_put_contents($file, $codes);
    }

    protected function _readDir($path, &$files)
    {
        if (is_dir($path)) {
            $dp = dir($path);
            while ($file = $dp->read()) {
                if ($file != "." && $file != "..") {
                    $this->_readDir($path . DIRECTORY_SEPARATOR . $file, $files);
                }
            }
            $dp->close();
        }
        if (is_file($path)) {
            $files[] = $path;
        }
    }
}
