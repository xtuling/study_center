<?php

/*
 * File name: PHPZip 文件压缩类
 * Author: wpp
 */
namespace Org\Net;

class PHPZip
{

    /**
     * 添加文件和子目录的文件到zip文件
     *
     * @param string $folder
     * @param ZipArchive $zipFile
     * @param int $exclusiveLength
     *            Number of text to be exclusived from the file path.
     */
    private static function folderToZip($folder, &$zipFile, $exclusiveLength)
    {
        $handle = opendir($folder);
        while (false !== $f = readdir($handle)) {
            if ($f != '.' && $f != '..') {
                $filePath = "$folder/$f";
                // Remove prefix from file path before add to zip.
                $localPath = substr($filePath, $exclusiveLength);
                if (is_file($filePath)) {
                    $zipFile->addFile($filePath, $localPath);
                } elseif (is_dir($filePath)) {
                    // 添加子文件夹
                    $zipFile->addEmptyDir($localPath);
                    self::folderToZip($filePath, $zipFile, $exclusiveLength);
                }
            }
        }
        closedir($handle);
    }

    /**
     * Zip a folder (include itself).
     * Usage:
     * HZip::zipDir('/path/to/sourceDir', '/path/to/out.zip');
     *
     * @param string $sourcePath
     *            Path of directory to be zip.
     * @param string $outZipPath
     *            Path of output zip file.
     */
    public function zipDir($sourcePath, $outZipPath)
    {
        // 判断目录是否存在，若存在则删除
        if (! file_exists($sourcePath)) {
            return false;
        }
        // 目标路径
        $outZipPath = iconv("UTF-8", "GBK", $outZipPath);
        $pathInfo = $this->path_info($sourcePath);
        $parentPath = $pathInfo['dirname'];
        $dirName = $pathInfo['basename'];
        $sourcePath = $parentPath . '/' . $dirName; // 防止传递'folder' 文件夹产生bug
        $z = new \ZipArchive();
        $z->open($outZipPath, \ZipArchive::CREATE); // 建立zip文件
        $z->addEmptyDir($dirName); // 建立文件夹
        self::folderToZip($sourcePath, $z, strlen("$parentPath/"));
        $z->close();
    }

    /**
     * 解决中文乱码
     */
    public function path_info($filepath)
    {
        $path_parts = array();
        $path_parts['dirname'] = rtrim(substr($filepath, 0, strrpos($filepath, '/')), "/") . "/";
        $path_parts['basename'] = ltrim(substr($filepath, strrpos($filepath, '/')), "/");
        $path_parts['extension'] = substr(strrchr($filepath, '.'), 1);
        $path_parts['filename'] = ltrim(substr($path_parts['basename'], 0, strrpos($path_parts['basename'], '.')), "/");

        return $path_parts;
    }
}
