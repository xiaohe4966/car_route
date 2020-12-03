<?php

namespace app\admin\model;

use think\Model;


class AddrSchool extends Model
{

    

    

    // 表名
    protected $name = 'addr_school';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







}
