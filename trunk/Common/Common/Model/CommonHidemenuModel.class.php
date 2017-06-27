<?php
/**
 * 定制化企业用户需要隐藏的菜单
 * @author tony
 * @time   2017-3-9 10:01:26
 */
namespace Common\Model;

class CommonHidemenuModel extends AbstractModel
{

    // 构造方法
    public function __construct()
    {

        parent::__construct();
    }

    /**
     * 根据企业标识获得隐藏菜单
     * @param $epEnumber
     * @return array
     */
    public function getMenus($epEnumber)
    {

        $sql = "SELECT menus FROM __TABLE__ WHERE status < ? and domain = ? limit 1";
        $param = [
            self::ST_DELETE,
            $epEnumber,
        ];

        return $this->_m->fetch_row($sql, $param);
    }
}

