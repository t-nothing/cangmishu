<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */ 

    'accepted'             => 'The :attribute 必须接受。',
    'active_url'           => 'The :attribute 不是一个有效的网址。',
    'after'                => 'The :attribute 必须要晚于 :date。',
    'after_or_equal'       => 'The :attribute 必须要等于 :date 或更晚。',
    'alpha'                => 'The :attribute 只能由字母组成。',
    'alpha_dash'           => 'The :attribute 只能由字母、数字和斜杠组成。',
    'alpha_num'            => 'The :attribute 只能由字母和数字组成。',
    'array'                => 'The :attribute 必须是一个数组。',
    'before'               => 'The :attribute 必须要早于 :date。',
    'before_or_equal'      => 'The :attribute 必须要等于 :date 或更早。',
    'between'              => [
        'numeric' => ':attribute 必须介于 :min - :max 之间。',
        'file'    => ':attribute 必须介于 :min - :max KB 之间。',
        'string'  => ':attribute 必须介于 :min - :max 个字符之间。',
        'array'   => ':attribute 必须只有 :min - :max 个单元。',
    ],
    'boolean'              => ':attribute 必须为布尔值。',
    'confirmed'            => ':attribute 两次输入不一致。',
    'date'                 => ':attribute 不是一个有效的日期。',
    'date_format'          => ':attribute 的格式必须为 :format。',
    'different'            => ':attribute 和 :other 必须不同。',
    'digits'               => ':attribute 必须是 :digits 位的数字。',
    'digits_between'       => ':attribute 必须是介于 :min 和 :max 位的数字。',
    'dimensions'           => ':attribute 图片尺寸不正确。',
    'distinct'             => ':attribute 重复了',
    'email'                => ':attribute 不是一个合法的邮箱。',
    'exists'               => ':attribute 不存在。',
    'file'                 => ':attribute 必须是文件。',
    'filled'               => ':attribute 不能为空。',
    'image'                => ':attribute 必须是图片。',
    'in'                   => '已选的属性 :attribute 非法。',
    'in_array'             => ':attribute 没有在 :other 中。',
    'integer'              => ':attribute 必须是整数。',
    'ip'                   => ':attribute 必须是有效的 IP 地址。',
    'json'                 => ':attribute 必须是正确的 JSON 格式。',
    'max'                  => [
        'numeric' => ':attribute 不能大于 :max。',
        'file'    => ':attribute 不能大于 :max KB。',
        'string'  => ':attribute 不能大于 :max 个字符。',
        'array'   => ':attribute 最多只有 :max 个单元。',
    ],
    'mimes'                => ':attribute 必须是一个 :values 类型的文件。',
    'mimetypes'            => ':attribute 必须是一个 :values 类型的文件。',
    'min'                  => [
        'numeric' => ':attribute 必须大于等于 :min。',
        'file'    => ':attribute 大小不能小于 :min KB。',
        'string'  => ':attribute 至少为 :min 个字符。',
        'array'   => ':attribute 至少有 :min 个单元。',
    ],
    'not_in'               => '已选的属性 :attribute 非法。',
    'numeric'              => ':attribute 必须是一个数字。',
    'present'              => ':attribute 必须存在。',
    'regex'                => ':attribute 格式不正确。',
    'required'             => ':attribute，必填。',// 不能为空
    'required_if'          => '当 :other 为 :value 时 :attribute，必填。',// 不能为空
    'required_unless'      => '当 :other 不为 :value 时 :attribute，必填。',// 不能为空
    'required_with'        => '当 :values 存在时 :attribute，必填。',// 不能为空
    'required_with_all'    => '当 :values 存在时 :attribute，必填。',// 不能为空
    'required_without'     => '当 :values 不存在时 :attribute，必填。',// 不能为空
    'required_without_all' => '当 :values 都不存在时 :attribute，必填。',// 不能为空
    'same'                 => ':attribute 和 :other 必须相同',
    'size'                 => [
        'numeric' => ':attribute 大小必须为 :size。',
        'file'    => ':attribute 大小必须为 :size KB。',
        'string'  => ':attribute 必须是 :size 个字符。',
        'array'   => ':attribute 必须为 :size 个单元。',
    ],
    'string'               => ':attribute 必须是一个字符串。',
    'timezone'             => ':attribute 必须是一个合法的时区值。',
    'unique'               => ':attribute 已经存在。',
    'uploaded'             => ':attribute 上传失败。',
    'url'                  => ':attribute 格式不正确。',
    'captcha'              => ':attribute 不正确。',
    'captcha_api'          => ':attribute 不正确。',
    'mobile'               => ':attribute 不正确。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'name'                    => '名称',
        'name_cn'                 => '中文名',
        'name_en'                 => '英文名',
        'username'                => '用户名',
        'email'                   => '邮箱',
        'first_name'              => '名',
        'last_name'               => '姓',
        'password'                => '密码',
        'password_confirmation'   => '确认密码',
        'city'                    => '城市',
        'country'                 => '国家',
        'address'                 => '地址',
        'phone'                   => '电话',
        'mobile'                  => '手机',
        'age'                     => '年龄',
        'sex'                     => '性别',
        'gender'                  => '性别',
        'day'                     => '天',
        'month'                   => '月',
        'year'                    => '年',
        'hour'                    => '时',
        'minute'                  => '分',
        'second'                  => '秒',
        'title'                   => '标题',
        'content'                 => '内容',
        'description'             => '描述',
        'excerpt'                 => '摘要',
        'date'                    => '日期',
        'time'                    => '时间',
        'available'               => '可用的',
        'size'                    => '大小',
        'code'                    => '编号',
        'is_enabled'              => '是否启用',
        'warehouse_id'            => '仓库', 
        'warehouse_area_id'       => '货区',
        'warehouse_feature_id'    => '特性',
        'verify_num'              => '验货数量',
        'production_batch_number' => '生产批次号',
        'expiration_date'         => '保质期',
        'best_before_date'        => '最佳食用期',
        'relevance_code'          => '外部编码',
        'distributor_code'        => '供应商货号',
        'application_email'       => '申请人邮箱',
        'product_stock.*.relevance_code'   => '外部编码',
        'product_stock.*.need_num'         => '入库数量',
        'product_stock.*.distributor_code' => '供应商货号',
        'distributor_id'          =>'供应商',
        'type_id'                 =>'类型',
        'status'                  =>'状态',
        'batch_code'              =>'入库单编码',
        'product_stock'           =>'库存记录',
        'batch_id'                =>'入库单',
        'captcha'                 =>'验证码',
        'mobile'                  =>'手机号',
        'captcha_key'             =>'验证码标识',
        'created_at_b'            =>'开始日期',
        'created_at_e'            =>'结束日期',
        'keywords'                =>'关键词',
        'page'                    =>'页码',
        'page_size'               =>'分页大小',
        'express_code'            =>'快递公司',
        'express_num'            =>'快递单号',
        'shop_remark'            =>'备注',
        'pay_status'                => '支付状态',
        'pay_type'                  => '支付类型',
        'sub_pay'                   => '实收金额',
        'payment_account_number'    => '流水单号',

    ],
];
