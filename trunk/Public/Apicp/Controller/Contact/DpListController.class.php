<?php
/**
 * 获取部门列表
 *
 */
namespace Apicp\Controller\Contact;


class DpListController extends AbstractController
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
            'list' => array(
                array(
                    'dpId' => "72A15A2A7F0000016C05A24F56B28853",
                    'dpName' => "开发中心",
                    'dpMem' => 10,

                ),
                array(
                    'dpId' => "72A15A327F0000016C05A24F5BF12FB6",
                    'dpName' => "西安组",
                    'dpMem' => 10,

                ),

            )
        );

        return $result;
    }


}
