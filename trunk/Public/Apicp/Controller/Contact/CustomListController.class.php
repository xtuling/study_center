<?php
/**
 * 获取成员属性列表接口
 *
 */
namespace Apicp\Controller\Contact;


class CustomListController extends AbstractController
{
    public function Index()
    {


        return $this->_result = array();
    }

    public function Test()
    {

        // 返回值
        $result = $this->result();

        return $this->_result = $result;
    }

    public function result()
    {


        // 构造返回值
        $result = array(
            'total' => 2,
            'page' => 1,
            'limit' => 12,
            'list' => array(
                array(
                    'fieldName' => "mem_ext1",
                    'level' => 3,
                    'name' => "职位",
                    'number' => 12,
                    'open' => 1,
                    'required' => 0,
                    'type' => 1,
                    'view' => 0,

                ),
                array(
                    'fieldName' => "mem_ext2",
                    'level' => 3,
                    'name' => "岗位",
                    'number' => 12,
                    'open' => 1,
                    'required' => 0,
                    'type' => 1,
                    'view' => 0,

                ),
            )
        );

        return $result;
    }

}
