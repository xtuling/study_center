<?php
/**
 * phpexcel易用封装
 * use excel2007 for 2007 format
 * Create By Deepseath
 * update by zhuxun, 2015-12-29
 * $Author$
 * $Id$
 */

namespace Com;

// 导入库文件
Vendor('PHPExcel.PHPExcel');

class Excel extends \PHPExcel
{

    /**
     * 标题栏文字颜色
     *
     * @var string default:FFFFFFFF
     */
    private $__title_text_color = '';

    /**
     * 标题栏背景颜色
     *
     * @var string default:FF808080
     */
    private $__title_background_color = '';

    /**
     * 是否已经设置过标题行
     *
     * @var unknown
     */
    private $__row_title_set = array();

    /**
     * 读取的文件名
     *
     * @var string
     */
    private $__file = null;

    /**
     * 导出文件备份
     *
     * @var unknown
     */
    private $__xls_bak_dir = 'backup';

    private $__read_sheet_index = null;

    // 默认属性值
    // 属性：作者
    private $__attr_creator = '';

    // 属性：最后一次保存者
    private $__attr_last_modified = '';

    // 属性：标题
    private $__attr_title = '';

    // 属性：主题
    private $__attr_subject = '';

    // 属性：备注
    private $__attr_description = '';

    // 属性：关键字
    private $__attr_keywords = '';

    // 属性：类别
    private $__attr_category = '';

    // 实例化
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

        parent::__construct();
    }

    /**
     * 生成一个可下载的Excel文件
     *
     * @param string $filename 下载的文件名
     * @param array  $titles   标题栏文字数组
     * @param array  $widths   标题栏宽度数组
     * @param array  $row_data 每行数据数组
     * @param array  $options
     * @param array  $attrs
     * @return bool
     */
    public function make_excel_download($filename, $titles, $widths, $row_data, $options = array(), $attrs = array())
    {

        // 设置环境选项
        $this->__set_options($options);
        // 设置文件属性
        $this->__set_attributes($attrs);
        // 设置默认标签
        $this->setActiveSheetIndex(0)->setTitle($filename); // PHPExcel的方法
        // 设置标题
        $this->__set_row_title($titles);
        // 设置宽度
        $this->__set_row_width($widths);
        // 设置 Excel 数据
        $this->__set_rows($row_data);
        // 下载 Excel 文件
        $this->__download_file($filename . '.xls');

        return true;
    }

    /**
     * 读取XSL文件，并返回数组
     *
     * @param string $filename       xsl文件的本地绝对物理路径
     * @param int    $set_read_index 读取xsl的标签页，默认为0
     * @param array  $options        配置
     * @param array  $attrs
     * @return array
     */
    public function read_from_xsl($filename, $set_read_index = 0, $options = array(), $attrs = array())
    {

        // 设置环境选项
        $this->__set_options($options);
        // 设置文件属性
        $this->__set_attributes($attrs);
        // 设置文件名
        $this->__set_file($filename);
        // 设置读取的标签索引
        $this->__set_read_index($set_read_index);

        // 读取文件
        return $this->__read_xls();
    }

    /**
     * 生成 excel 文件
     *
     * @see excel::make_excel_download()
     */
    public function mk_excel_file($filename, $titles, $widths, $row_data, $options = array(), $attrs = array())
    {

        return $this->make_excel_download($filename, $titles, $widths, $row_data, $options, $attrs);
    }

    /**
     * 生成一个Excel文件
     *
     * @param string $filename 下载的文件名
     * @param array  $titles   标题栏文字数组
     * @param array  $widths   标题栏宽度数组
     * @param array  $row_data 每行数据数组
     * @return bool
     */
    public function make_tmp_excel_download($filename, $file_excel, $titles, $widths, $row_data, $options = array(), $attrs = array())
    {

        // 设置环境选项
        $this->__set_options($options);
        // 设置文件属性
        $this->__set_attributes($attrs);
        // 设置默认标签索引
        $this->setActiveSheetIndex(0)->setTitle($filename); // PHPExcel的方法
        // 设置标题
        $this->__set_row_title($titles);
        // 设置列宽
        $this->__set_row_width($widths);
        // 设置行数据
        $this->__set_rows($row_data);
        // 设置文件名称
        $this->__set_file($file_excel);
        // 保持文件
        $this->__save_file();

        return true;
    }

    /**
     * 以字段形式输出格式化的数据列表
     *
     * @param string $filename
     *                                   xsl文件的本地绝对物理路径
     * @param int    $set_read_index     读取xsl的标签页，默认为0
     * @param array  $field_options      字段定义
     *                                   + array(
     *                                   '字段名' => array('name'=>'中文名', 'width'=>宽度[一个字符可按6来计算],),
     *                                   ...
     *                                   )
     * @param int    $title_row_num      标题栏行号（起始行=0）
     * @param int    $data_start_row_num 数据开始的行号（起始行=0）
     * @param array  $options            配置
     * @param array  $attrs
     * @return bool|array(字段映射关系数组, 读取的数据列表)
     */
    public function parse_xsl($filename, $set_read_index = 0, $field_options = array(), $title_row_num = 0, $data_start_row_num = 0, $options = array(), $attrs = array())
    {

        // 如果不可读
        if (!is_readable($filename)) {
            E('_ERR_PHPEXCEL_READABLE_NO');

            return false;
        }
        // 读取文件
        $data = @file_get_contents($filename, false, null, 0, 8);
        if ($data != pack('CCCCCCCC', 0xd0, 0xcf, 0x11, 0xe0, 0xa1, 0xb1, 0x1a, 0xe1)) {
            E('_ERR_PHPEXCEL_NOT_VOA_EXCEL_TPL');

            return false;
        }
        // 读取数据
        $list = $this->read_from_xsl($filename, $set_read_index, $options, $attrs);
        // 没有读取到数据
        if (empty($list)) {
            E('_ERR_PHPEXCEL_DATA_IS_EMPTY');

            return false;
        }
        // 字段中文名与表字段名对应关系
        $name2field = array();
        foreach ($field_options as $_k => $_arr) {
            $name2field[rstrtolower($_arr['name'])] = $_k;
        }
        unset($_k, $_arr);
        // 自标题栏行读取标记与字段名之间的对应关系
        $col2field = array();
        if (!isset($list[$title_row_num])) {
            E('_ERR_PHPEXCEL_TITLE_IS_EMPTY');

            return false;
        }
        foreach ($list[$title_row_num] as $_col_num => $_col_name) {
            $_col_name = rstrtolower($_col_name);
            if (!isset($name2field[$_col_name])) {
                continue;
            }
            // 如果该行未注释
            if (false === strpos($name2field[$_col_name], '#')) {
                $col2field[$_col_num] = $name2field[$_col_name];
            }
        }
        // 读取数据行
        $list = array_slice($list, $data_start_row_num, count($list), true);
        // 过滤空的数据行
        foreach ($list as $_k => $_row) {
            $_row = array_filter($_row);
            if (empty($_row)) {
                unset($list[$_k]);
            }
        }

        return array(
            $col2field,
            $list
        );
    }

    /**
     * 设置读取xls的标签页
     *
     * @param int $i
     */
    private function __set_read_index($i)
    {

        $this->__read_sheet_index = $i;
    }

    /**
     * 设置Excel的属性值
     *
     * @param array $attrs
     * @return void
     */
    private function __set_attributes($attrs = array())
    {

        $allowed_attrs = array(
            'creator', // 作者
            'last_modified', // 最后一次保存者
            'title', // 标题
            'subject', // 主题
            'description', // 备注
            'keywords', // 关键字
            'category'
        ); // 类别

        foreach ($allowed_attrs as $_key) {
            if (!isset($attrs[$_key]) || !is_scalar($attrs[$_key])) {
                $attrs[$_key] = $this->{'__attr_' . $_key};
            }
        }
        $this->getProperties()
            ->setCreator($attrs['creator'])
            ->setLastModifiedBy($attrs['last_modified'])
            ->setTitle($attrs['title'])
            ->setSubject($attrs['subject'])
            ->setDescription($attrs['description'])
            ->setKeywords($attrs['keywords'])
            ->setCategory($attrs['category']);
    }

    /**
     * 设置一些环境变量
     *
     * @param array $options
     * @return void
     */
    private function __set_options($options = array())
    {

        $allowed_options = array(
            'title_text_color',
            'title_background_color'
        );
        foreach ($options as $_key => $_val) {
            if (in_array($_key, $allowed_options) && null !== $_val) {
                $this->{'__' . $_key} = $_val;
            }
        }
        // 未设置标题栏颜色，则使用 PHPExcel 黑色
        if ('' == $this->__title_text_color) {
            $this->__title_text_color = \PHPExcel_Style_Color::COLOR_BLACK;
        }
        // 未设置标题栏背景色，则使用 PHPExcel 深黄色
        if ('' == $this->__title_background_color) {
            $this->__title_background_color = \PHPExcel_Style_Color::COLOR_DARKYELLOW;
        }

        return true;
    }

    /**
     * 读取xls内容
     *
     * @param int $start_row 开始行数
     * @param int $max_row   最大行数
     * @return mixed
     */
    private function __read_xls($start_row = 1, $max_row = 10000)
    {

        // 如果文件为空
        if (!$this->__file) {
            E('_ERR_PHPEXCEL_FILE_NOT_EXISTS');

            return false;
        }
        // use excel2007 for 2007 format
        $obj_reader = \PHPExcel_IOFactory::createReader('Excel5');
        $obj_phpexcel = $obj_reader->load($this->__file);
        if (!is_null($this->__read_sheet_index)) {
            $obj_worksheet = $obj_phpexcel->getSheet($this->__read_sheet_index);
        } else {
            $obj_worksheet = $obj_phpexcel->getActiveSheet();
        }
        // 取得总行数
        $highest_row = $obj_worksheet->getHighestRow();
        $highest_column = $obj_worksheet->getHighestColumn();
        // 总列数
        $highest_column_index = \PHPExcel_Cell::columnIndexFromString($highest_column);
        // 避免超过设置的最大行数
        if ($highest_row > $max_row) {
            $highest_row = $max_row;
        }
        $arr_return = array();
        for ($row = $start_row; $row <= $highest_row; $row++) {
            // 注意highestColumnIndex的列数索引从0开始
            $rowdata = array();
            for ($col = 0; $col < $highest_column_index; $col++) {
                // getValue() getCalculatedValue()
                $cell = $obj_worksheet->getCellByColumnAndRow($col, $row)->getValue();
                // 富文本转换字符串
                if ($cell instanceof \PHPExcel_RichText) {
                    $cell = $cell->__toString();
                }
                // 公式
                if ('=' == substr($cell, 0, 1)) {
                    $cell = $obj_worksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
                }
                $rowdata[$col] = $this->__excel_data_to_string($cell);
            }
            $arr_return[] = $rowdata;
        }

        return $arr_return;
    }

    /**
     * 设置标题
     *
     * @param array $titles = array('A'=>'ID' ,'B'=>'中文', 'D'=>'英文') | array('ID' ,'中文', '英文')
     * @return bool
     */
    private function __set_row_title($titles)
    {

        $index = $this->getActiveSheetIndex();
        $this->__row_title_set[$index] = true;
        if ('assoc' == $this->__array_type($titles)) {
            foreach ($titles as $_column => $_val) {
                $this->getActiveSheet()->setCellValue($_column . '1', $_val);
                $this->getActiveSheet()
                    ->getStyle($_column . '1')
                    ->getFont()
                    ->setBold(true);
                $this->getActiveSheet()
                    ->getStyle($_column . '1')
                    ->getFont()
                    ->getColor()
                    ->setARGB($this->__title_text_color);
                $this->getActiveSheet()
                    ->getStyle($_column . '1')
                    ->getFill()
                    ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $this->getActiveSheet()
                    ->getStyle($_column . '1')
                    ->getFill()
                    ->getStartColor()
                    ->setARGB($this->__title_background_color);
                $this->getActiveSheet()
                    ->getStyle($_column)
                    ->getNumberFormat()
                    ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            }
        } else {
            $start = 'A';
            for ($i = 0; $i < count($titles); $i++) {
                $_column = $start++;
                $this->getActiveSheet()->setCellValue($_column . '1', $titles[$i]);
                $this->getActiveSheet()
                    ->getStyle($_column . '1')
                    ->getFont()
                    ->setBold(true);
                $this->getActiveSheet()
                    ->getStyle($_column . '1')
                    ->getFont()
                    ->getColor()
                    ->setARGB($this->__title_text_color);
                $this->getActiveSheet()
                    ->getStyle($_column . '1')
                    ->getFill()
                    ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $this->getActiveSheet()
                    ->getStyle($_column . '1')
                    ->getFill()
                    ->getStartColor()
                    ->setARGB($this->__title_background_color);
                $this->getActiveSheet()
                    ->getStyle($_column)
                    ->getNumberFormat()
                    ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            }
        }

        return true;
    }

    /**
     * 设置行内容
     *
     * @param array $xls_rows
     *            e.g. $xls_rows = array(
     *            array('content1','content2','content3'),
     *            array('A'=>'content1','B'=>'content2','C'=>'content3'),
     *            ...
     *            )
     * @return bool
     */
    private function __set_rows($xls_rows)
    {

        $index = $this->getActiveSheetIndex();
        $n = $this->__row_title_set[$index] ? 2 : 1;
        foreach ($xls_rows as $_row) {
            if ('assoc' == $this->__array_type($_row)) { // 关联
                foreach ($_row as $_column => $_val) {
                    $this->getActiveSheet()->setCellValueExplicit($_column . $n, $_val, $this->__set_data_type($_val));
                    $this->getActiveSheet()
                        ->getStyle($_column . $n)
                        ->getAlignment()
                        ->setWrapText(true)
                        ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                }
            } else {
                $start = 'A';
                for ($i = 0; $i < count($_row); $i++) {
                    $_column = $start++;
                    $this->getActiveSheet()->setCellValueExplicit($_column . $n, $_row[$i], $this->__set_data_type($_row[$i]));
                    $this->getActiveSheet()
                        ->getStyle($_column . $n)
                        ->getAlignment()
                        ->setWrapText(true)
                        ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                }
            }
            $n++;
            /**
             * #横向|竖向 对齐方式 setHorizontal | setVertical (\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
             * //也可生成EXCEL后手动设置也方便
             * # HORIZONTAL_RIGHT | HORIZONTAL_LEFT | HORIZONTAL_CENTER 参考PHPExcel/Style/Alignment.php
             * # VERTICAL_RIGHT | VERTICAL_LEFT | VERTICAL_CENTER 参考PHPExcel/Style/Alignment.php
             */
        }

        return true;
    }

    /**
     * 设置标题宽度
     *
     * @param array $widths = array('A'=>8 ,'B'=>60, 'C'=>60,'D'=>'auto','E'=>0) | array(8,60,60,0,0)
     * @return bool
     */
    private function __set_row_width($widths = array())
    {

        if ('assoc' == $this->__array_type($widths)) {
            // 关联
            foreach ($widths as $_column => $_val) {
                if ('auto' == $_val || 0 == $_val) {
                    $this->getActiveSheet()
                        ->getColumnDimension($_column)
                        ->setAutoSize(true);
                } else {
                    $this->getActiveSheet()
                        ->getColumnDimension($_column)
                        ->setWidth($_val . "pt");
                }
                // $this->getActiveSheet()->getStyle($_column)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                // $this->getActiveSheet()->getStyle($_column)->getFill()->getStartColor()->setARGB('FF808080');
            }
        } else {
            $start = 'A';
            for ($i = 0; $i < count($widths); $i++) {
                $_column = $start++;
                $_val = $widths[$i];
                if ('auto' == $_val || 0 == $_val) {
                    $this->getActiveSheet()
                        ->getColumnDimension($_column)
                        ->setAutoSize(true);
                } else {
                    $this->getActiveSheet()
                        ->getColumnDimension($_column)
                        ->setWidth($_val . "pt");
                }
                // $this->getActiveSheet()->getStyle($_column)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                // $this->getActiveSheet()->getStyle($_column)->getFill()->getStartColor()->setARGB('FF808080');
            }
        }

        return true;
    }

    /**
     * 设置要保存的文件,测试文件是否可以被打开
     *
     * @param unknown $file_excel
     * @throws Exception
     * @return bool
     */
    private function __set_file($file_excel)
    {

        $file_excel = riconv($file_excel, 'UTF-8', 'GBK');
        if (!($fp = fopen($file_excel, 'a+'))) {
            E('_ERR_PHPEXCEL_FILE_CAN_NOT_OPEN');

            return false;
        }
        if ($fp) {
            fclose($fp);
        }
        $this->__file = $file_excel;

        return true;
    }

    /**
     * 保存文件
     * 使用该方法前，必须使用 $this->__set_file();方法设置文件名
     */
    private function __save_file()
    {

        $file_excel = $this->__file;
        $obj_writer = \PHPExcel_IOFactory::createWriter($this, 'Excel5');
        $obj_writer->save($file_excel);

        return true;
    }

    /**
     * 下载生成的Excel文件
     *
     * @param string $download_file
     *            下载的文件名
     */
    private function __download_file($download_file)
    {

        $filename = riconv($download_file, 'UTF-8', 'GBK');
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="' . $filename . '"');
        header("Content-Transfer-Encoding:binary");
        $obj_writer = \PHPExcel_IOFactory::createWriter($this, 'Excel5');
        $obj_writer->save('php://output');
        exit();
    }

    /**
     * 将单元格内的数据转为可读的字符串
     *
     * @param unknown $string
     * @return unknown|string
     */
    private function __excel_data_to_string($string)
    {

        if (false === stripos($string, 'e')) {
            return $string;
        }
        if (!preg_match('/^[0-9\.e\-\+]+$/i', $string)) {
            return $string;
        }
        // 科学计数法，还原成字符串
        $string = trim(preg_replace('/[=\'"]/', '', $string, 1), '"');
        $result = '';
        while ($string > 0) {
            $v = $string - floor($string / 10) * 10;
            $string = floor($string / 10);
            $result = $v . $result;
        }

        return $result;
    }

    /**
     * 数据
     *
     * @param array $types
     *            数据类型
     * @return string
     */
    private function __array_type($types)
    {

        $c = count($types);
        $in = array_intersect_key($types, range(0, $c - 1));
        if (count($in) == $c) { // 索引数组
            return 'index';
        } elseif (empty($in)) { // 关联数组
            return 'assoc';
        } else { // 混合数组
            return 'mix';
        }
    }

    /**
     * 根据数据类型自动判断单元格的数据格式
     *
     * @param string $str
     *            数据
     * @return string
     */
    private function __set_data_type($str)
    {

        return 'str';
        if (is_numeric($str)) {
            if (false !== strpos($str, '.')) {
                return 'b';
            } elseif (!isset($str{3})) {
                return 'n';
            } else {
                return 'str';
            }
        } else {
            return 'str';
        }
    }
}
