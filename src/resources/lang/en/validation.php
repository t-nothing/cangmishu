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

    'accepted'             => 'The :attribute must be accepted.',
    'active_url'           => 'The :attribute is not a valid URL.',
    'after'                => 'The :attribute must be a date after :date.',
    'after_or_equal'       => 'The :attribute must be a date after or equal to :date.',
    'alpha'                => 'The :attribute may only contain letters.',
    'alpha_dash'           => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num'            => 'The :attribute may only contain letters and numbers.',
    'array'                => 'The :attribute must be an array.',
    'before'               => 'The :attribute must be a date before :date.',
    'before_or_equal'      => 'The :attribute must be a date before or equal to :date.',
    'between'              => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'The :attribute must be between :min and :max characters.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'              => 'The :attribute field must be true or false.',
    'confirmed'            => 'The :attribute confirmation does not match.',
    'date'                 => 'The :attribute is not a valid date.',
    'date_format'          => 'The :attribute does not match the format :format.',
    'different'            => 'The :attribute and :other must be different.',
    'digits'               => 'The :attribute must be :digits digits.',
    'digits_between'       => 'The :attribute must be between :min and :max digits.',
    'dimensions'           => 'The :attribute has invalid image dimensions.',
    'distinct'             => 'The :attribute field has a duplicate value.',
    'email'                => 'The :attribute must be a valid email address.',
    'exists'               => 'The selected :attribute is invalid.',
    'file'                 => 'The :attribute must be a file.',
    'filled'               => 'The :attribute field is required.',
    'image'                => 'The :attribute must be an image.',
    'in'                   => 'The selected :attribute is invalid.',
    'in_array'             => 'The :attribute field does not exist in :other.',
    'integer'              => 'The :attribute must be an integer.',
    'ip'                   => 'The :attribute must be a valid IP address.',
    'json'                 => 'The :attribute must be a valid JSON string.',
    'max'                  => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file'    => 'The :attribute may not be greater than :max kilobytes.',
        'string'  => 'The :attribute may not be greater than :max characters.',
        'array'   => 'The :attribute may not have more than :max items.',
    ],
    'mimes'                => 'The :attribute must be a file of type: :values.',
    'mimetypes'            => 'The :attribute must be a file of type: :values.',
    'min'                  => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'not_in'               => 'The selected :attribute is invalid.',
    'numeric'              => 'The :attribute must be a number.',
    'present'              => 'The :attribute field must be present.',
    'regex'                => 'The :attribute format is invalid.',
    'required'             => 'The :attribute field is required.',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_unless'      => 'The :attribute field is required unless :other is in :values.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_with_all'    => 'The :attribute field is required when :values is present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'string'               => 'The :attribute must be a string.',
    'timezone'             => 'The :attribute must be a valid zone.',
    'unique'               => 'The :attribute has already been taken.',
    'uploaded'             => 'The :attribute failed to upload.',
    'url'                  => 'The :attribute format is invalid.',

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
        'name'                    => 'name',
        'name_cn'                 => 'name Cn',
        'name_en'                 => 'name En',
        'username'                => 'username',
        'email'                   => 'email',
        'first_name'              => 'first name',
        'last_name'               => 'last name',
        'password'                => 'password',
        'password_confirmation'   => 'password confirmation',
        'city'                    => 'city',
        'country'                 => 'Country',
        'address'                 => 'address',
        'phone'                   => 'phone',
        'mobile'                  => 'mobile',
        'age'                     => 'age',
        'sex'                     => 'sex',
        'gender'                  => 'gender',
        'day'                     => 'day',
        'month'                   => 'month',
        'year'                    => 'year',
        'hour'                    => 'hour',
        'minute'                  => 'minute',
        'second'                  => 'second',
        'title'                   => 'title',
        'content'                 => 'content',
        'description'             => 'description',
        'excerpt'                 => 'excerpt',
        'date'                    => 'date',
        'time'                    => 'time',
        'available'               => 'available',
        'size'                    => 'size',
        'code'                    => 'code',
        'is_enabled'              => 'is enabled',
        'warehouse_id'            => 'warehouse', 
        'warehouse_area_id'       => 'warehouse area',
        'warehouse_feature_id'    => 'warehouse feature',
        'verify_num'              => 'verify qty',
        'production_batch_number' => 'production batch number',
        'expiration_date'         => 'expiration date',
        'best_before_date'        => 'best before date',
        'relevance_code'          => 'relevance code',
        'distributor_code'        => 'distributor code',
        'application_email'       => 'application email',
        'product_stock.*.relevance_code'   => 'sku code',
        'product_stock.*.need_num'         => 'qty',
        'product_stock.*.distributor_code' => 'distributor code',
        'distributor_id'          =>'distributor',
        'type_id'                 =>'type',
        'status'                  =>'status',
        'batch_code'              =>'batch code',
        'product_stock'           =>'stock',
        'batch_id'                =>'inbound',
        'captcha'                 =>'captcha',
        'mobile'                  =>'mobile',
        'captcha_key'             =>'captcha key',
        'created_at_b'            =>'begin date',
        'created_at_e'            =>'end date',
        'keywords'                =>'keywords',
        'page'                    =>'page number',
        'page_size'               =>'page size',
        'express_code'            =>'express',
        'express_num'            =>'tracking number',
        'shop_remark'            =>'remark',
        'pay_status'                => 'pay status',
        'pay_type'                  => 'payment method',
        'sub_pay'                   => 'paid',
        'payment_account_number'    => 'transaction no.',

    ],

];
