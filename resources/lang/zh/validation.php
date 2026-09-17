<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 验证语言行
    |--------------------------------------------------------------------------
    |
    | 以下语言行包含验证器类使用的默认错误消息。
    | 其中一些规则有多个版本，例如尺寸规则。
    | 您可以在此自由调整每个消息。
    |
    */

    'accepted' => ':attribute 必须被接受。',
    'active_url' => ':attribute 不是有效的URL。',
    'after' => ':attribute 必须是 :date 之后的日期。',
    'after_or_equal' => ':attribute 必须是 :date 之后或等于 :date 的日期。',
    'alpha' => ':attribute 只能包含字母。',
    'alpha_dash' => ':attribute 只能包含字母、数字和短划线。',
    'alpha_num' => ':attribute 只能包含字母和数字。',
    'array' => ':attribute 必须是数组。',
    'before' => ':attribute 必须是 :date 之前的日期。',
    'before_or_equal' => ':attribute 必须是 :date 之前或等于 :date 的日期。',
    'between' => [
        'numeric' => ':attribute 必须在 :min 和 :max 之间。',
        'file' => ':attribute 必须在 :min 和 :max KB之间。',
        'string' => ':attribute 必须在 :min 和 :max 个字符之间。',
        'array' => ':attribute 必须在 :min 和 :max 项之间。',
    ],
    'boolean' => ':attribute 字段必须为 true 或 false。',
    'confirmed' => ':attribute 确认不匹配。',
    'date' => ':attribute 不是有效的日期。',
    'date_format' => ':attribute 与格式 :format 不匹配。',
    'different' => ':attribute 和 :other 必须不同。',
    'digits' => ':attribute 必须是 :digits 位数字。',
    'digits_between' => ':attribute 必须在 :min 和 :max 位数字之间。',
    'dimensions' => ':attribute 的图像尺寸无效。',
    'distinct' => ':attribute 字段有重复值。',
    'email' => ':attribute 必须是有效的邮箱地址。',
    'exists' => '所选 :attribute 无效。',
    'file' => ':attribute 必须是一个文件。',
    'filled' => ':attribute 字段是必填的。',
    'image' => ':attribute 必须是图片。',
    'in' => '所选 :attribute 无效。',
    'in_array' => ':attribute 字段在 :other 中不存在。',
    'integer' => ':attribute 必须是整数。',
    'ip' => ':attribute 必须是有效的IP地址。',
    'json' => ':attribute 必须是有效的JSON字符串。',
    'max' => [
        'numeric' => ':attribute 不能大于 :max。',
        'file' => ':attribute 不能大于 :max KB。',
        'string' => ':attribute 不能大于 :max 个字符。',
        'array' => ':attribute 不能超过 :max 项。',
    ],
    'mimes' => ':attribute 必须是类型为 :values 的文件。',
    'mimetypes' => ':attribute 必须是类型为 :values 的文件。',
    'min' => [
        'numeric' => ':attribute 必须至少为 :min。',
        'file' => ':attribute 必须至少为 :min KB。',
        'string' => ':attribute 必须至少为 :min 个字符。',
        'array' => ':attribute 必须至少有 :min 项。',
    ],
    'not_in' => '所选 :attribute 无效。',
    'numeric' => ':attribute 必须是数字。',
    'present' => ':attribute 字段必须存在。',
    'regex' => ':attribute 格式无效。',
    'required' => ':attribute 字段是必填的。',
    'required_if' => '当 :other 为 :value 时，:attribute 字段是必填的。',
    'required_unless' => '除非 :other 在 :values 中，否则 :attribute 字段是必填的。',
    'required_with' => '当 :values 存在时，:attribute 字段是必填的。',
    'required_with_all' => '当 :values 存在时，:attribute 字段是必填的。',
    'required_without' => '当 :values 不存在时，:attribute 字段是必填的。',
    'required_without_all' => '当 :values 都不存在时，:attribute 字段是必填的。',
    'same' => ':attribute 和 :other 必须匹配。',
    'size' => [
        'numeric' => ':attribute 必须为 :size。',
        'file' => ':attribute 必须为 :size KB。',
        'string' => ':attribute 必须为 :size 个字符。',
        'array' => ':attribute 必须包含 :size 项。',
    ],
    'string' => ':attribute 必须是字符串。',
    'timezone' => ':attribute 必须是有效的时区。',
    'unique' => ':attribute 已被占用。',
    'uploaded' => ':attribute 上传失败。',
    'url' => ':attribute 格式无效。',

    /*
    |--------------------------------------------------------------------------
    | 自定义验证属性
    |--------------------------------------------------------------------------
    |
    | 以下语言行用于将属性占位符替换为更易读的内容，
    | 例如将 "email" 替换为 "邮箱地址"。这有助于使消息更加清晰。
    |
    */

    'attributes' => [],

    // Pterodactyl 内部验证逻辑
    'internal' => [
        'variable_value' => ':env 变量',
        'invalid_password' => '为此账号提供的密码无效。',
    ],
];