<?php

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        'header_comment' => [
            'header' => '',
            'separate' => 'none',
        ],
        'single_blank_line_before_namespace' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'break',
                'continue',
                'extra',
                'return',
                'throw',
                'use',
                'parenthesis_brace_block',
                'square_brace_block',
                'curly_brace_block'
            ]
        ],
        'phpdoc_align' => false,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'cast_spaces' => true,
        'binary_operator_spaces' => false,
        'increment_style' => false,
        'yoda_style' => false,
        'trailing_comma_in_multiline' => true,
        'single_line_throw' => false,
    ])
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->exclude([
                'vendor',
            ])
            ->in(__DIR__)
    );