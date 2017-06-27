<?php
$source = $result = '';
$type = 'json';
if (!empty($_POST['source'])) {
    $source = trim($_POST['source']);
    $type = isset($_POST['type']) ? $_POST['type'] : 'json';
    if (!in_array($type, ['json', 'serialize', 'array'])) {
        $type = 'json';
    }

    $data = [];
    switch($type) {
        case 'json':
            $data = json_decode($source);
        break;
        case 'serialize':
            $data = unserialize($source);
        break;
        case 'array':
            eval('$data = ' . $source. ';');
        break;
    }


    $resultArray = [];
    foreach ($data as $_k => $_v) {
        if (is_array($_v)) {
            _list($_v, $_k, $resultArray);
        } else {
            $resultArray[] = $_k . ':' .$_v;
        }
    }

    $result = implode("\n", $resultArray);
}

function _list($arr, $keyPrefix, &$result) {
    foreach ($arr as $_k => $_v) {
        if (is_array($_v)) {
            _list($_v, $keyPrefix . '[' . $_k . ']', $result);
        } else {
            $result[] = $keyPrefix . '[' . $_k . ']:' .$_v;
        }
    }
}
?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Postman 数据生成器</title>
<style type="text/css">
.box{width: 100%; text-align: center}
.left{float: left; width: 49%;}
.right{float: right; width: 49%}
.input{width: 100%; height: 500px;}
.submit{width: 100%; margin: 0 auto; cursor: pointer;}
label{cursor: pointer}
</style>
</head>
<body>
<form action="" method="post">
<div class="box">
    <div class="left">
        <h2>录入数据</h2>
        <textarea name="source" class="input"><?php echo htmlspecialchars($source);?></textarea>
        数据格式：
        <label><input type="radio" name="type" value="json"<?php echo $type == 'json' ? ' checked="checked"' : '';?> /> Json</label>
        <label><input type="radio" name="type" value="serialize"<?php echo $type == 'serialize' ? ' checked="checked"' : '';?> /> Serialize</label>
        <label><input type="radio" name="type" value="array"<?php echo $type == 'array' ? ' checked="checked"' : '';?> /> Array</label>
    </div>
    <div class="right">
        <h2>输出 postman 数据</h2>
        <textarea name="result" class="input"><?php echo htmlspecialchars($result);?></textarea>
    </div>
    <button type="submit" class="submit">Submit</button>
    <div style="clear: both"></div>
</div>
</form>
</body>
</html>