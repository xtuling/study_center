<?php
/**
 * identifier.php
 *
 * @author Deepseath
 * @version $Id$
 */

return array(
    // 定义统一的应用唯一标识符
    // 如果为空，则按应用自身的定义 app.php
    // 如果使用{***}定义，则表示为 app.php 定义的标识符统一加前缀***
    // 其他字符串，则表示所有应用统一的标识符
    'identifier' => 'studycenter_app',

    // 强制定义应用自己的标识符，而不使用公共的标识符，此设置优先级低于 [APP]/app.php 的特性定义
    'app' => array(
        // 通讯录使用自己的标识符
        // key = 应用的目录名小写
        // value = 应用自己的标识符
        'contact' => 'userinvite'
    )
);
