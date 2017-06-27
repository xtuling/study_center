<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Apicp\Controller\Bank;

use Common\Service\BankService;

abstract class AbstractController extends \Apicp\Controller\AbstractController
{
    /**
     * @var  BankService  实例化题库表对象
     */
    protected $bank_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        $this->bank_serv = new BankService();

        return true;
    }
}
