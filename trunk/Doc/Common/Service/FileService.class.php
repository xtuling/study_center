<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/5/18
 * Time: 11:36
 */
namespace Common\Service;

use Common\Common\Constant;
use Common\Model\FileModel;

class FileService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new FileModel();
    }

    /**
     * @desc 检查同级目录下文件、文件夹名称是否重复，重复则生成新文件、文件夹名称（引用）
     * @author tangxingguo
     * @param String $file_name 文件、文件夹名称
     * @param Int $parent_id 父级目录ID
     * @param Int $file_type 文件类型（1=文件夹；2=文件）
     * @param Int $file_id 文件ID，编辑文件时传入
     */
    public function checkFileName(&$file_name, $parent_id, $file_type, $file_id = 0)
    {
        // 同名文件出现的次数
        static $count;
        // 原始文件名
        static $rawName;
        // 同级目录下文件、文件夹列表
        static $list;
        // 同级目录下所有文件、文件夹名称
        static $fileNames;

        // 当前目录下所有文件夹或文件信息
        if (empty($list)) {
            $list = $this->list_by_conds(['parent_id' => $parent_id, 'file_type' => $file_type]);
            $list = array_combine_by_key($list, 'file_id');
            if ($file_id > 0) {
                unset($list[$file_id]);
            }
            if (!empty($list)) {
                $fileNames = array_column($list, 'file_name');
            }
        }

        // 没有文件
        if (empty($fileNames)) {
            return;
        }

        // 赋值原始文件名
        if (empty($rawName)) {
            $rawName = $file_name;
        }

        // 存在重复文件，根据重复次数，拼接文件名
        if (in_array($file_name, $fileNames)) {
            $count++;
            if ($file_type == Constant::FILE_TYPE_IS_DOC) {
                // 文件
                $suffix = end(explode('.', $rawName));
                $name = mb_substr($rawName, 0, mb_strrpos($rawName, '.'));
                $file_name = $name . '(' . $count . ').' . $suffix;
            } else {
                // 文件夹
                $file_name = $rawName . '(' . $count . ')';
            }

            $this->checkFileName($file_name, $parent_id, $file_type);
        }
    }

    /**
     * @desc 取目录下所有子文件夹ID（包含自身）
     * @author tangxingguo
     * @param array $file_ids 目录ID
     * @return array 子文件夹ID
     */
    public function getChildIds($file_ids)
    {
        $folders = $this->formatDBData(['file_type' => Constant::FILE_TYPE_IS_FOLDER]);

        // 取子文件夹ID（包含自身）
        if (!empty($folders)) {
            foreach ($file_ids as $value) {
                if (isset($folders[$value])) {
                    $groupData = $this->dataGrouping($folders[$value]);
                }
            }
        }

        return isset($groupData) ? $groupData['file_id'] : [];
    }

    /**
     * @desc 将多层级结构的数据根据字段分组返回
     * @author tangxingguo
     * @param array $arrTree 层级结构数组
     * @return array 分组数据
     *          + array file_id 所有子类ID（包含自身）
     *          + array file_name 所有子类名称（包含自身）
     */
    public function dataGrouping($arrTree)
    {
        static $data;
        if (!is_array($arrTree)) {
            return [];
        }
        foreach ($arrTree as $k => $v) {
            if (is_array($v)) {
                $this->dataGrouping($v);
            } else {
                $data[$k][] = $v;
            }
        }
        return $data;
    }

    /**
     * @desc 根据层级格式化数据库文件数据
     * @author tangxingguo
     * @param array $conds 数据库数据获取条件
     * @return array
     */
    public function formatDBData($conds = [])
    {
        $files = $this->list_by_conds($conds);
        if (!empty($files)) {
            $files = array_combine_by_key($files, 'file_id');
            foreach ($files as $k => $v) {
                $files[$v['parent_id']]['child'][] = &$files[$k];
            }
        } else {
            $files = [];
        }

        return $files;
    }

    /**
     * @desc 取当前文件所在目录层级
     * @author tangxingguo
     * @param int $file_id 文件ID
     * @return int
     */
    public function getLevel($file_id)
    {
        if ($file_id == 0) {
            return 0;
        }
        $files = $this->formatDBData([]);
        if (empty($files)) {
            return 0;
        }
        $level = 1;
        while ($files[$file_id]['parent_id'] > 0) {
            $level++;
            $file_id = $files[$file_id]['parent_id'];
        }
        return $level;
    }

    /**
     * @desc 取当前目录所在路径
     * @author tangxingguo
     * @param int $file_id 目录ID
     * @return array
     */
    public function getPaths($file_id)
    {
        $dir = [];
        $files = $this->formatDBData([]);
        if (empty($files)) {
            return $dir;
        }
        // 当前目录
        $dir[] = [
            'file_id' => $file_id,
            'file_name' => $files[$file_id]['file_name'],
        ];
        // 父级目录
        while ($files[$file_id]['parent_id'] > 0) {
            $file_id = $files[$file_id]['parent_id'];
            $dir[] = [
                'file_id' => $file_id,
                'file_name' => $files[$file_id]['file_name'],
            ];
        }
        
        // 反序
        return array_reverse($dir);
    }

    /**
     * @desc 取目录所有文件个数以及目录大小
     * @author tangxingguo
     * @param $file_id
     * @return array
     */
    public function getFolderInfo($file_id)
    {
        $totalSize = 0;
        $totalFile = 0;
        $fileServ = new FileService();
        $fileList = $fileServ->formatDBData([]);
        if (!empty($fileList) && isset($fileList[$file_id])) {
            $groupData = $fileServ->dataGrouping($fileList[$file_id]);
            // 累加子文件尺寸
            $totalSize = array_sum($groupData['at_size']);
            // 文件个数
            $totalFile = count($groupData['file_id']);
        }
        return [$totalSize, $totalFile];
    }

    /**
     * 区分文件夹和文件
     * @author liyifei
     * @param array $file_ids 文件夹及文件ID
     * @return array
     */
    public function diffFolderFile($file_ids)
    {
        $folders = [];
        $files = [];

        $all_file = $this->formatDBData();
        if (empty($file_ids) || empty($all_file)) {
            return [$folders, $files];
        }

        foreach ($file_ids as $file_id) {
            if (isset($all_file[$file_id])) {
                switch ($all_file[$file_id]['file_type']) {
                    case Constant::FILE_TYPE_IS_FOLDER:
                        $folders[] = $file_id;
                        break;

                    case Constant::FILE_TYPE_IS_DOC:
                        $files[] = $file_id;
                        break;
                }
            }
        }

        return [$folders, $files];
    }
}
