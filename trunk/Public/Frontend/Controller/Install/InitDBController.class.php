<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 17/6/12
 * Time: 19:31
 */
namespace Frontend\Controller\Install;

class InitDBController extends AbstractController
{
    protected $_require_login = false;

    /**
     * 初始化数据库
     * @author zhonglei
     */
    public function Index()
    {
        $dirs = [];

        if (!$handle = opendir(ROOT_PATH)) {
            exit('无法读取目录：' . ROOT_PATH);
        }

        // 读取并过滤ROOT_PATH子目录
        while (($file = readdir($handle)) !== false) {
            if (is_dir(ROOT_PATH . $file) && !preg_match('/(Apidoc|Common|Demo|ThinkPHP|www)|(\.|_)+/i', $file)) {
                $dirs[] = $file;
            }
        }

        closedir($handle);

        if (empty($dirs)) {
            exit('目录不能为空：' . ROOT_PATH);
        }

        $db = \Think\Db::getInstance();

        foreach ($dirs as $dir) {
            $struc_file = sprintf('%s%s/Common/Sql/structure.php', ROOT_PATH, $dir);

            if (!file_exists($struc_file)) {
                exit("{$dir}未找到数据结构文件：{$struc_file}");
            }

            $sql = include($struc_file);

            if (empty($sql)) {
                continue;
            }

            try {
                $db->execute($sql);
            } catch (\Exception $e) {
                exit("{$dir}初始化数据结构失败：" . $e->getMessage());
            }

            $url = oaUrl('Frontend/Callback/Install', [], '', $dir);
            $result = file_get_contents($url);

            if (empty($result) || strtolower($result) != 'success') {
                exit("{$dir}默认数据安装失败");
            }
        }

        $db->close();
        
        exit('SUCCESS');
    }
}
