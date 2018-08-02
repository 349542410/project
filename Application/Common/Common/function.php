<?php
/**
 * Created by PhpStorm.
 * User: 34884
 * Date: 2018/6/27
 * Time: 11:43
 */


/**
 * 查询admin_config 配置
 * @param $key
 * @param string $default
 * @return mixed|string
 */
function getConfig($key, $default = '')
{
    $value = M('admin_config')->where(['name' => $key])->getField('value');
    return $value ? $value : $default;
}
