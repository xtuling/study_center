<?php
/**
 * uploader
 * 通用上传类（post、base64、远程下载）
 * Create By Deepseath
 * $Author$
 * $Id$
 */
namespace Com;

class Upload
{

    /**
     * 文件上传成功
     */
    const ERR_OK = 0;

    /**
     * 上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值
     */
    const ERR_INI_SIZE = 1;

    /**
     * 上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值
     */
    const ERR_FORM_SIZE = 2;

    /**
     * 文件只有部分被上传
     */
    const ERR_PARTIAL = 3;

    /**
     * 没有文件被上传
     */
    const ERR_NO_FILE = 4;

    /**
     * 找不到临时文件夹
     */
    const ERR_NO_TMP_DIR = 6;

    /**
     * 文件写入失败
     */
    const ERR_CANT_WRITE = 7;

    /**
     * getimagesize() 图片格式映射
     */
    const GIF = 1;

    const JPG = 2;

    const PNG = 3;

    const SWF = 4;

    const PSD = 5;

    const BMP = 6;

    /**
     * intel byte order
     */
    const TIFF = 7;

    /**
     * motorola byte order
     */
    const TIFF2 = 8;

    const JPC = 9;

    const JP2 = 10;

    const JPX = 11;

    const JB2 = 12;

    const SWC = 13;

    const IFF = 14;

    const WBMP = 15;

    const XBM = 16;

    /**
     * 上传状态信息，可用于判断是否上传成功,SUCCESS为成功。也可以使用$this->get_file_info的'error_msg'键名来判断
     *
     * @var string
     */
    public $error_msg = '';

    /**
     * 文件域名
     *
     * @var string
     */
    private $_file_field;

    /**
     * 文件上传对象信息 $_FILES[$this->_file_field]
     *
     * @var array
     */
    private $_file;

    /**
     * 上传类型
     *
     * @var string
     */
    private $_upload_type;

    /**
     * 配置信息
     *
     * @var array
     * @example <pre>$config = array(
     *          'save_dir_path' = > '',// 必须配置。定义基本储存目录
     *          'allow_files' => array('png', 'jpg', 'jpeg', 'gif', 'bmp'),// 必须配置。允许上传的文件格式，默认为：array('png', 'jpg', 'jpeg', 'gif', 'bmp')
     *          'file_name_format' => '',// 定义文件名保存格式，见：$this->_get_full_name() 方法，默认为：auto（YYYY/mm/）
     *          'max_size' => '2048000',// 上传大小限制，单位B，默认：2048000
     *          'source_name' => 'remote.png',// 原始文件名，可不配置
     *          )</pre>
     */
    private $_config;

    /**
     * 上传的原始文件名
     *
     * @var string
     */
    private $_source_name;

    /**
     * 重命名后的纯文件名，不包含储存目录路径
     *
     * @var string
     */
    private $_file_name;

    /**
     * 重命名后的文件名，可能包含目录结构路径，不包含储存根目录路径信息
     *
     * @var string
     */
    private $_full_name;

    /**
     * 完整文件储存路径，（储存目录+储存文件名）
     *
     * @var string
     */
    private $_file_path;

    /**
     * 文件大小
     *
     * @var number
     */
    private $_file_size;

    /**
     * 是否为图片文件
     *
     * @var boolean
     */
    private $_is_image = false;

    /**
     * 如果为图片，图片的宽度
     *
     * @var number
     */
    private $_image_width = 0;

    /**
     * 如果为图片，图片的高度
     *
     * @var number
     */
    private $_image_height = 0;

    /**
     * 文件类型（无前导“.”）
     *
     * @var string
     */
    private $_file_type;

    /**
     * 当前进程允许的最大上传尺寸，单位：B
     *
     * @var number
     */
    private $_max_size = 1024000;

    /**
     * 当前进程允许的文件类型
     *
     * @var array
     */
    private $_allow_type = array();

    /**
     * 错误编码
     *
     * @var number
     */
    private $_error_code = - 1;

    /**
     * 文件类型与扩展名的映射关系
     */
    private $_media_type_maps = array(

        // 音频、声音文件
        2 => array(
            'mp3'
        ),
        3 => array(
            'mp4'
        )
    );

    /**
     * 上传状态文字提示映射表
     *
     * @var array
     */
    private $_error_map = array(
        self::ERR_OK => 'SUCCESS', // 上传成功标记，不可更改！！(在UEditor中内不可改变，否则flash判断会出错)
        self::ERR_INI_SIZE => '文件大小超出 upload_max_filesize 限制',
        self::ERR_FORM_SIZE => '文件大小超出 MAX_FILE_SIZE 限制',
        self::ERR_PARTIAL => '文件未被完整上传',
        self::ERR_NO_FILE => '没有文件被上传',
        self::ERR_NO_TMP_DIR => '上传文件为空',
        self::ERR_CANT_WRITE => '文件写入失败',
        'ERROR_TMP_FILE' => '临时文件错误',
        'ERROR_TMP_FILE_NOT_FOUND' => '找不到临时文件',
        'ERROR_TMPFILE' => '非法上传的临时文件',
        'ERROR_SIZE_EXCEED' => '文件大小（%s）超出系统限制（%s）',
        'ERROR_TYPE_NOT_ALLOWED' => '文件类型不允许',
        'ERROR_CREATE_DIR' => '目录创建失败',
        'ERROR_DIR_NOT_WRITEABLE' => '目录没有写权限',
        'ERROR_FILE_MOVE' => '文件保存时出错',
        'ERROR_FILE_NOT_FOUND' => '找不到上传文件',
        'ERROR_WRITE_CONTENT' => '写入文件内容错误',
        'ERROR_UNKNOWN' => '未知错误',
        'ERROR_BASE64_NULL' => '文件内容不存在或不合法',
        'ERROR_DEAD_LINK' => '远程图片链接不可用',
        'ERROR_HTTP_LINK' => '远程图片链接不是 http 协议',
        'ERROR_HTTP_CONTENTTYPE' => '远程图片链接 contentType 不正确',
        'ERROR_HTTP_GET_FAILED' => '获取远程图片链接（%s）发生错误',
        'ERROR_SAVE_DIR_PATH_ERROR' => '储存目录未定义或不可写 %s',
        'ERROR_NOT_IMAGE' => '只允许图片格式',
        'ERROR_CREATE_TMP_FILE' => '创建临时文件发生错误',
        'ERROR_BASE64_NULL_LOCAL' => '文件内容不合法',
        'ERROR_CREATE_TMP_FILE_LOCAL' => '创建本地临时文件发生错误',
        'ERROR_FILE_MOVE_LOCAL' => '文件转移保存时出错'
    );

    /**
     * 构造函数，处理上传业务
     *
     * @param string $file_field
     *            上传文件表单控件名称 或 base64编码字符串表单控件名 或 远程图片url 或 直接传入$_FILES[key]的数组（此方式用于处理多个文件上传）
     * @param array $config
     *            配置项，详见成员初始化介绍
     *            <pre>
     *            + save_dir_path 必须。定义基本储存目录
     *            + allow_files 必须。允许上传的文件格式。默认为：array('png', 'jpg', 'jpeg', 'gif', 'bmp')
     *            + file_name_format 可选。@see upload::_get_full_name() 方法，默认为：auto（YYYY/mm/）
     *            + max_size 可选。上传大小限制。单位：B。默认：2048000
     *            + source_name 可选。原始文件名，可不配置
     *            </pre>
     * @param bool $type
     *            上传类型 remote|base64|upload|local，默认：upload。
     *            <pre>
     *            remote : 远程抓取图片。使用此类型，$file_field 表示远程图片的 url 地址
     *            base64 : 处理base64编码上传图片。使用此类型，$file_field 表示存放base64编码字符串的表单控件名，只接受$_POST方式
     *            local : 将文件流写入到附件内。使用此类型，$file_field 表示文件经base64编码后的字符串
     *            upload : 默认。处理普通上传的文件。使用此类型，$file_field 表示上传表单控件的名
     *            </pre>
     */
    public function __construct($file_field, $config, $type = 'upload')
    {
        $this->_file_field = $file_field;
        $this->_config = $config;
        $this->_upload_type = rstrtolower($type);
        if ($this->_upload_type == 'remote') {
            $this->_save_remote();
        } elseif ($this->_upload_type == 'base64') {
            $this->_upload_base64();
        } elseif ($this->_upload_type == 'local') {
            $this->_upload_local();
        } else {
            $this->_upload_file();
        }
    }

    /**
     * 获取当前上传成功文件的各项信息，该结果不可直接暴露在前端！输出给前端必须重新格式剔除某些路径信息
     *
     * @return array
     */
    public function get_file_info()
    {
        return array(
            'error_code' => $this->_error_code, // 只用于输出PHP内置的错误常量值
            'error' => $this->error_msg, // 错误信息，SUCCESS为成功，判断上传成功与否也可以直接使用$this->error_msg
            'save_path' => $this->_full_name, // 文件储存路径，包含储存目录路径，但不包含储存根目录
            'file_name' => $this->_file_name, // 重命名后的文件名
            'source_name' => $this->_source_name, // 原始文件名，对于远程读取以及base64此值无具体意义
            'file_type' => '.' . $this->_file_type, // 文件类型后缀，包含前缀“.”
            'file_size' => $this->_file_size, // 文件尺寸值，单位:B，数值
            'is_image' => $this->_is_image, // 是否为图片
            'width' => $this->_image_width, // 图片宽度
            'height' => $this->_image_height, // 图片高度
            'file_path' => $this->_file_path, // 文件的物理绝对路径
            'type_name' => $this->_file_type, // 文件类型
            'size_string' => size_count($this->_file_size), // 易读的文件尺寸字符串
            'upload_type' => $this->_upload_type, // 上传类型
            'config' => $this->_config
        ); // 经过验证后的配置信息

    }

    /**
     * 上传文件的主处理方法（普通上传）
     *
     * @return mixed
     */
    private function _upload_file()
    {
        // 检查上传配置参数
        if (! $this->_check_config()) {
            return;
        }
        if (is_array($this->_file_field) && isset($this->_file_field['error']) && isset($this->_file_field['tmp_name']) && isset($this->_file_field['name']) && $this->_file_field['size']) {
            // 传入的 $file_field 是一个上传的文件信息数组，则直接使用该值
            // 此方式对于处理多个上传文件可能更灵活一些
            $file = $this->_file = $this->_file_field;
        } else {
            // 传入的是上传表单控件名
            // 赋值上传对象数组
            $file = $this->_file = isset($_FILES[$this->_file_field]) ? $_FILES[$this->_file_field] : false;
            if (! $file || ! isset($file['error']) || ! isset($file['tmp_name']) || ! isset($file['name']) || ! isset($file['size'])) {
                $this->error_msg = $this->_get_error_msg('ERROR_FILE_NOT_FOUND');

                return;
            }
        }
        if ($this->_file['error']) {
            // 上传发生错误
            $this->error_msg = $this->_get_error_msg($this->_file['error']);

            return;
        } elseif (! is_file($file['tmp_name'])) {
            // 临时文件不存在
            $this->error_msg = $this->_get_error_msg('ERROR_TMP_FILE_NOT_FOUND');

            return;
        } elseif (! is_uploaded_file($file['tmp_name'])) {
            // 检查是否为上传的文件
            // TODO 可能某些环境此判断会过于严格
            $this->error_msg = $this->_get_error_msg('ERROR_TMPFILE');

            return;
        }
        // 尝试判断图片格式
        $_image_ext = $this->_get_image_ext($file['tmp_name']);
        $this->_is_image = $_image_ext ? true : false;
        // 如果是图片尝试使用真实的图片格式来命名原始文件名
        $this->__convert_real_image_ext($file['name'], $_image_ext);
        $this->_file_size = $file['size'];
        $this->_file_type = $this->_get_file_ext();
        $this->_full_name = $this->_get_full_name();
        $this->_file_path = $this->_get_file_path();
        $this->_file_name = $this->_get_file_name();
        // 检查文件的尺寸、并尝试创建储存目录
        if ($this->_check_file() !== true) {
            @unlink($file['tmp_name']);

            return;
        }
        // 移动文件
        // TODO 未使用 move_uploaded_file() 因某些环境下使用此函数会过于严格
        if (! rename($file['tmp_name'], $this->_file_path) && ! is_file($this->_file_path)) {
            // 移动失败
            $this->error_msg = $this->_get_error_msg('ERROR_FILE_MOVE');
        } else {
            // 移动成功
            $this->error_msg = $this->_get_error_msg(self::ERR_OK);
        }
        @unlink($file['tmp_name']);
    }

    /**
     * 处理base64编码的图片上传
     *
     * @return mixed
     */
    private function _upload_base64()
    {
        // 检查上传配置参数
        if (! $this->_check_config()) {
            return;
        }
        $base64Data = ! empty($_POST[$this->_file_field]) ? (string) $_POST[$this->_file_field] : '';
        $img = base64_decode($base64Data);
        if ($base64Data === '' || $img === false) {
            $this->error_msg = $this->_get_error_msg('ERROR_BASE64_NULL');

            return;
        }
        // @ 创建临时文件
        $tmp_file = $this->_create_tmpfile($img);
        if (! $tmp_file) {
            $this->error_msg = $this->_get_error_msg('ERROR_CREATE_TMP_FILE');

            return;
        }
        // 判断图片文件类型
        $_image_ext = $this->_get_image_ext($tmp_file);
        if (! $_image_ext) {
            $this->error_msg = $this->_get_error_msg('ERROR_NOT_IMAGE');
        }
        $this->_is_image = true;
        $this->__convert_real_image_ext($this->_config['source_name'], $_image_ext);
        $this->_file_size = strlen($img);
        $this->_file_type = $this->_get_file_ext();
        $this->_full_name = $this->_get_full_name();
        $this->_file_path = $this->_get_file_path();
        $this->_file_name = $this->_get_file_name();
        // 检查文件的格式、尺寸、并尝试创建储存目录
        if ($this->_check_file() !== true) {
            @unlink($tmp_file);

            return;
        }
        // 移动文件
        if (! rename($tmp_file, $this->_file_path) && ! is_file($this->_file_path)) {
            // 移动失败
            $this->error_msg = $this->_get_error_msg('ERROR_FILE_MOVE');
        } else {
            // 移动成功
            $this->error_msg = $this->_get_error_msg(self::ERR_OK);
        }
        @unlink($tmp_file);
    }

    /**
     * 获取远程图片
     *
     * @return mixed
     */
    private function _save_remote()
    {
        // 检查上传配置参数
        if (! $this->_check_config()) {
            return;
        }
        $imgUrl = htmlspecialchars($this->_file_field);
        $imgUrl = str_replace('&amp;', '&', $imgUrl);
        // http开头验证
        if (strpos($imgUrl, 'http') !== 0 || ($parse_url = parse_url($imgUrl)) === false || ! isset($parse_url['scheme']) || ! isset($parse_url['host'])) {
            $this->error_msg = $this->_get_error_msg('ERROR_HTTP_LINK');

            return;
        }
        // 获取请求头并检测死链
        $heads = get_headers($imgUrl, 1);
        if ($heads === false || (! (stristr($heads[0], '200') && stristr($heads[0], 'OK')))) {
            $this->error_msg = $this->_get_error_msg('ERROR_DEAD_LINK');

            return;
        }
        // 通过url链接来判断格式
        // TODO 扩展名验证可能过于严格，某些动态路径的图片可能无法被获取到
        $file_type = $this->_get_file_ext($imgUrl);
        /*
         * if (!in_array($file_type, $this->_config['allow_files'])) {
         * $this->error_msg = $this->_get_error_msg('ERROR_HTTP_CONTENTTYPE');
         * return;
         * }
         */
        // 格式验证(扩展名验证和Content-Type验证)
        if (! isset($heads['Content-Type']) || ! stristr($heads['Content-Type'], 'image')) {
            $this->error_msg = $this->_get_error_msg('ERROR_HTTP_CONTENTTYPE');

            return;
        }
        // 根据 Content-type 格式重构文件名
        if (preg_match('/image\/(\w+)/i', $heads['Content-Type'], $match)) {
            $this->_config['source_name'] = 'remote.' . trim($match[1]);
        }
        // 伪造来路url避免被防盗链屏蔽
        $referer = $parse_url['scheme'] . '://' . $parse_url['host'] . '/';
        // 伪造浏览器：Accept
        $browser_accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
        // 伪造 User-Agent
        $browser_user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Windows NT 5.1; rv:29.0) Gecko/20100101 Firefox/29.0';
        // 获取远程图片
        // TODO 关于 stream context 的配置，后面可考虑也加入配置里，方便根据环境灵活使用调用
        $context = stream_context_create(array(
            'http' => array(
                'follow_location' => false, // don't follow redirects
                'timeout' => 30,
                'header' => array(
                    'Referer: ' . $referer . "\r\n" . 'Accept: ' . $browser_accept . "\r\n" . 'User-Agent: ' . $browser_user_agent
                )
            )
        ));
        $img = file_get_contents($imgUrl, false, $context);
        if ($img === false) {
            $this->error_msg = $this->_get_error_msg('ERROR_HTTP_GET_FAILED', rhtmlspecialchars($imgUrl));

            return;
        }
        // @ 创建临时文件
        $tmp_file = $this->_create_tmpfile($img);
        if (! $tmp_file) {
            $this->error_msg = $this->_get_error_msg('ERROR_CREATE_TMP_FILE');

            return;
        }
        // 判断图片文件类型
        $_image_ext = $this->_get_image_ext($tmp_file);
        if (! $_image_ext) {
            $this->error_msg = $this->_get_error_msg('ERROR_NOT_IMAGE');
        }
        $this->_is_image = true;
        $this->__convert_real_image_ext($this->_config['source_name'], $_image_ext);
        $this->_file_size = strlen($img);
        $this->_file_type = $this->_get_file_ext();
        $this->_full_name = $this->_get_full_name();
        $this->_file_path = $this->_get_file_path();
        $this->_file_name = $this->_get_file_name();
        // 检查文件的格式、尺寸、并尝试创建储存目录
        if ($this->_check_file() !== true) {
            @unlink($tmp_file);

            return;
        }
        // 移动文件
        if (! rename($tmp_file, $this->_file_path) || ! is_file($this->_file_path)) {
            // 移动失败
            $this->error_msg = $this->_get_error_msg('ERROR_FILE_MOVE');
        } else {
            // 移动成功
            $this->error_msg = $this->_get_error_msg(self::ERR_OK);
        }
        @unlink($tmp_file);
    }

    /**
     * 检查上传配置参数
     *
     * @return boolean
     */
    private function _check_config()
    {
        // 检查储存目录是否定义且是否可写
        if (empty($this->_config['save_dir_path']) || ! is_writable($this->_config['save_dir_path'])) {
            $this->error_msg = $this->_get_error_msg('ERROR_SAVE_DIR_PATH_ERROR', ! isset($this->_config['save_dir_path']) ? 'null' : $this->_config['save_dir_path']);

            return false;
        }
        // 未设置文件储存名格式，则使用自动设置"auto"
        if (empty($this->_config['file_name_format'])) {
            $this->_config['file_name_format'] = 'auto';
        }
        // 未定义允许上传的文件类型，则使用空数组
        if (empty($this->_config['allow_files'])) {
            $this->_config['allow_files'] = array();
        } else {
            // 尝试剔除定义的扩展名前缀“.”，并转为小写字符
            foreach ($this->_config['allow_files'] as &$ext) {
                if (($s = strpos($ext, '.')) !== false) {
                    $ext = trim($ext);
                    $ext = trim(substr($ext, $s + 1));
                }
                $ext = rstrtolower($ext);
            }
        }
        // 未定义允许上传的最大尺寸，则默认为：1m
        if (empty($this->_config['max_size'])) {
            $this->_config['max_size'] = 1024000; // 1m
        }
        // 如果未定义原文件名，则给出一个假的文件名
        if (empty($this->_config['source_name'])) {
            $this->_config['source_name'] = 'unknow.unknow';
        }

        return true;
    }

    /**
     * 检查文件的限制：尺寸、尝试创建目录
     */
    private function _check_file()
    {
        // @ 检查是否不允许的文件格式
        if (! $this->_check_type()) {
            $this->error_msg = $this->_get_error_msg('ERROR_TYPE_NOT_ALLOWED');

            return;
        }
        // @ 检查文件大小是否超出限制
        if (! $this->_check_size()) {
            $this->error_msg = $this->_get_error_msg('ERROR_SIZE_EXCEED', size_count($this->_file_size), size_count($this->_max_size));

            return;
        }
        // 储存路径的目录
        $dirname = dirname($this->_file_path);
        // @ 创建目录
        rmkdir($dirname, 0777, true);
        // 创建目录失败
        if (! is_dir($dirname)) {
            $this->error_msg = $this->_get_error_msg('ERROR_CREATE_DIR');

            return;
        }
        // 目录不可写
        if (! is_writeable($dirname)) {
            $this->error_msg = $this->_get_error_msg('ERROR_DIR_NOT_WRITEABLE');

            return;
        }

        return true;
    }

    /**
     * 上传错误检查
     *
     * @param
     *            $errCode
     * @return string
     */
    private function _get_error_msg($errCode)
    {
        $this->_error_code = $errCode;
        if ($errCode == 0) {
            return $this->_error_map[$errCode];
        }
        $args = func_get_args();
        $keys = is_array($args) ? $args : explode(',', preg_replace('/[\s|\'|\']*/e', '', $args));
        if (! empty($keys)) {
            for ($i = 0; $i < sizeof($keys); $i ++) {
                if (! empty($keys[$i]) && $keys[$i] != '') {
                    $keys[$i] = isset($this->_error_map[$keys[$i]]) ? $this->_error_map[$keys[$i]] : $keys[$i];
                }
            }
            $format = array_shift($keys);
            $flen = sizeof(preg_split('/\%\w/i', $format));
            while (sizeof($keys) < $flen) {
                // 使数组元素总数与占位符数目相对应,否则会报错
                $keys[] = '';
            }

            return vsprintf($format, $keys);
        }

        return empty($keys) ? $this->_error_map['ERROR_UNKNOWN'] : join('-', $keys);
    }

    /**
     * 获取文件扩展名
     *
     * @return string
     */
    private function _get_file_ext($filename = null)
    {
        // 未指定文件名则使用原始文件名
        if ($filename === null) {
            $filename = $this->_source_name;
        }

        // 取得文件扩展名
        return addslashes(rstrtolower(substr(strrchr($filename, '.'), 1, 10)));
    }

    /**
     * 重命名文件并确定储存目录
     *
     * @return string
     */
    private function _get_full_name()
    {
        // @ 储存目录和文件名路径（相对储存根目录的文件路径），输出此变量
        $_full_name = '';
        // 当前系统时间戳
        $timestamp = NOW_TIME;
        // 当前文件扩展名
        $file_ext = '.' . $this->_get_file_ext();
        if (strtolower($this->_config['file_name_format']) == 'auto') {
            // 使用系统自动创建储存名和目录
            // 构造形如：2014/05/2817534012345678
            $format = '{yyyy}{dir}{mm}{dir}{dd}{hh}{ii}{ss}{rand:8}';
        } else {
            // 使用config配置的自定义方式创建储存文件名和目录
            $format = $this->_config['file_name_format'];
        }
        // 当前时间变量值
        $d = explode('-', rgmdate($timestamp, 'Y-y-m-d-H-i-s'));
        // 替换关于时间标记的字符
        $format = str_replace('{yyyy}', $d[0], $format);
        $format = str_replace('{yy}', $d[1], $format);
        $format = str_replace('{mm}', $d[2], $format);
        $format = str_replace('{dd}', $d[3], $format);
        $format = str_replace('{hh}', $d[4], $format);
        $format = str_replace('{ii}', $d[5], $format);
        $format = str_replace('{ss}', $d[6], $format);
        // 替换时间戳
        $format = str_replace('{time}', $timestamp, $format);
        // 是否加入文件名
        if (strpos($format, '{filename}') !== false) {
            // 取得无扩展名的文件名
            $source_name = substr($this->_source_name, 0, strrpos($this->_source_name, '.'));
            // 剔除非法的文件名字符
            $source_name = preg_replace('/[\|\?"\<\>\/\*\\\\]+/', '', $source_name);
            // 替换标记
            $format = str_replace('{filename}', $source_name, $format);
        }
        // 是否需要加入随机数
        if (preg_match('/\{rand\:([\d]*)\}/i', $format, $match)) {
            // 获取随机数需要的位数
            $rand_count = $match[1] ? $match[1] : 8;
            // 真实的随机数
            $randNum = sprintf("%0{$rand_count}s", substr(mt_rand(1, 10000000000) . mt_rand(1, 10000000000), 0, $rand_count));
            // 替换标记\/:*?"<>|
            $format = str_replace($match[0], $randNum, $format);
        }
        // 替换非法文件名字符
        $format = preg_replace('/[\|\?"\<\>\/\*\\\\]+/', '', $format);
        // 替换目录符号
        $format = str_replace('{dir}', '/', $format);
        // 构造新文件名和扩展名
        $_full_name = $format . $file_ext;

        return $_full_name;
    }

    /**
     * 获取文件名
     *
     * @return string
     */
    private function _get_file_name()
    {
        return basename($this->_file_path);

        return substr($this->_file_path, strrpos($this->_file_path, DIRECTORY_SEPARATOR) + 1);
    }

    /**
     * 获取文件完整储存路径（储存目录+储存文件名）
     *
     * @return string
     */
    private function _get_file_path()
    {
        $fullname = $this->_full_name;
        $root_path = $this->_config['save_dir_path'];

        return $this->_format_path($root_path . '/' . $fullname);
    }

    /**
     * 文件类型检测
     *
     * @return bool
     */
    private function _check_type()
    {
        $this->_allow_type = $this->_config['allow_files'];

        return $this->_config['allow_files'] && in_array($this->_get_file_ext(), $this->_config['allow_files']);
    }

    /**
     * 文件大小检测
     *
     * @return bool
     */
    private function _check_size()
    {
        return ($this->_file_size) <= ($this->_proc_allow_max_size());
    }

    /**
     * 返回当前进程允许的最大文件大小（获取所有配置内的最小值），单位：B
     *
     * @return number
     */
    private function _proc_allow_max_size()
    {
        $max_size = 0;
        $system_max_size = 0;
        // 系统环境的大小限制
        if (function_exists('ini_get')) {
            $max_size = min(count_size(ini_get('memory_limit')), count_size(ini_get('post_max_size')), count_size(ini_get('upload_max_filesize')));
            $system_max_size = $max_size;
        }
        // 获取上传表单设置的 MAX_FILE_SIZE 尺寸
        if (isset($_POST['MAX_FILE_SIZE']) && is_scalar($_POST['MAX_FILE_SIZE'])) {
            $max_size = min(count_size($_POST['MAX_FILE_SIZE']), $max_size);
        }
        // 自行配置的上传最大尺寸
        if (isset($this->_config['max_size'])) {
            $max_size = min(count_size($this->_config['max_size']), $max_size);
        }
        if ($max_size <= 0) {
            $max_size = $system_max_size ? $system_max_size : count_size('1m');
        }

        return $this->_max_size = $max_size;
    }

    /**
     * 格式化路径分隔符号为系统符号
     *
     * @param string $path
     * @return string
     */
    private function _format_path($path)
    {
        return str_replace('.' . DIRECTORY_SEPARATOR, '', preg_replace(array(
            '/\/+/',
            '/\\\+/'
        ), DIRECTORY_SEPARATOR, $path));
    }

    /**
     * 获取图片文件扩展名
     *
     * @param string $filename
     * @return string
     */
    private function _get_image_ext($filename)
    {
        if (! is_file($filename)) {
            return false;
        }
        $get_image_size = getimagesize($filename);
        if ($get_image_size && is_array($get_image_size) && ! empty($get_image_size[2])) {
            $this->_image_width = $get_image_size[0];
            $this->_image_height = $get_image_size[1];
            switch ($get_image_size[2]) {
                case self::GIF:
                    return 'gif';
                case self::JPG:
                    return 'jpg';
                case self::PNG:
                    return 'png';
                case self::SWF:
                    return 'swf';
                case self::PSD:
                    return 'psd';
                case self::BMP:
                    return 'bmp';
                case self::TIFF:
                    return 'tiff';
                case self::TIFF2:
                    return 'tiff';
                case self::JPC:
                    return 'jpc';
                case self::JP2:
                    return 'jp2';
                case self::JPX:
                    return 'jpx';
                case self::JB2:
                    return 'jb2';
                case self::SWC:
                    return 'swc';
                case self::IFF:
                    return 'iff';
                case self::WBMP:
                    return 'wbmp';
                case self::XBM:
                    return 'xbm';
            }
        }
        unset($get_image_size);

        return false;
    }

    /**
     * 创建一个临时文件，用于远程抓取或者base64上传的情况
     *
     * @param unknown $data
     * @return string | boolean
     */
    private function _create_tmpfile($data)
    {
        // 建立一个临时文件，文件名使用时间戳+4位随机数，储存在系统临时目录内
        $temp_file = tempnam(sys_get_temp_dir(), md5(NOW_TIME) . sprintf('%04s', mt_rand(1, 9999)));
        // 写入数据到此临时文件
        if (! is_writable($temp_file) || file_put_contents($temp_file, $data) === false || ! is_file($temp_file)) {
            return false;
        }
        unset($data);

        // 返回该临时文件的路径
        return $temp_file;
    }

    /**
     * 为源文件名加上真实的图片扩展名
     *
     * @param string $filename
     * @param string $image_ext
     * @return boolean
     */
    private function __convert_real_image_ext($filename, $image_ext)
    {
        if (! $this->_is_image) {
            $this->_source_name = $filename;

            return true;
        }
        if (strpos($filename, '.') === false) {
            $this->_source_name = $filename . '.' . $image_ext;

            return true;
        }
        $this->_source_name = preg_replace('/[^\.]+$/s', $image_ext, $filename);

        return true;
    }

    /**
     * 将本地文件写入到附件
     */
    protected function _upload_local()
    {
        // 检查上传配置参数
        if (! $this->_check_config()) {
            return;
        }
        $file_content = base64_decode($this->_file_field);
        if (empty($this->_file_field) || $file_content === false) {
            $this->error_msg = $this->_get_error_msg('ERROR_BASE64_NULL_LOCAL');

            return;
        }
        // @ 创建临时文件
        $tmp_file = $this->_create_tmpfile($file_content);
        if (! $tmp_file) {
            $this->error_msg = $this->_get_error_msg('ERROR_CREATE_TMP_FILE_LOCAL');

            return;
        }
        // 判断图片文件类型
        $_image_ext = $this->_get_image_ext($tmp_file);
        if ($_image_ext) {
            $this->_is_image = true;
        } else {
            $this->_is_image = false;
        }
        $this->__convert_real_image_ext($this->_config['source_name'], $_image_ext);
        $this->_file_size = strlen($file_content);
        $this->_file_type = $this->_get_file_ext();
        $this->_full_name = $this->_get_full_name();
        $this->_file_path = $this->_get_file_path();
        $this->_file_name = $this->_get_file_name();
        // 检查文件的格式、尺寸、并尝试创建储存目录
        if ($this->_check_file() !== true) {
            @unlink($tmp_file);

            return;
        }
        // 移动文件
        if (! rename($tmp_file, $this->_file_path) && ! is_file($this->_file_path)) {
            // 移动失败
            $this->error_msg = $this->_get_error_msg('ERROR_FILE_MOVE_LOCAL');
        } else {
            // 移动成功
            $this->error_msg = $this->_get_error_msg(self::ERR_OK);
        }
        @unlink($tmp_file);
    }
}
