<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Apicp\Controller\Integral;

abstract class AbstractController extends \Apicp\Controller\AbstractController
{
    /** 数据含义:开启 */
    const OPEN = 1;
    /** 数据含义:关闭 */
    const CLOSE = 2;
    /** 数据含义:是 */
    const TRUE = 1;
    /** 数据含义:否 */
    const FALSE = 2;
    /**
     * isOpen的数据范围
     * @var array
     */
    protected $isOpenArr = [
        self::OPEN,
        self::CLOSE
    ];
}
