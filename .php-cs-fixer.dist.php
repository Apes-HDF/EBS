<?php

declare(strict_types=1);

// https://cs.symfony.com/

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('tmp')
    ->exclude('node_modules')
;

return (new PhpCsFixer\Config())->setRules([
    '@Symfony' => true,
    'array_syntax' => ['syntax' => 'short'],    // https://cs.symfony.com/doc/rules/array_notation/array_syntax.html
    'declare_strict_types' => true,             // https://cs.symfony.com/doc/rules/strict/declare_strict_types.html
    'php_unit_fqcn_annotation' => false,        // https://cs.symfony.com/doc/rules/php_unit/php_unit_fqcn_annotation.html
    'yoda_style' => false,                      // https://cs.symfony.com/doc/rules/control_structure/yoda_style.html
    'phpdoc_to_comment' => false,               // https://cs.symfony.com/doc/rules/phpdoc/phpdoc_to_comment.html # Needed for PHPStan @var annotations
    'native_function_invocation' => [           // https://cs.symfony.com/doc/rules/function_notation/native_function_invocation.html
        'include' => ['@compiler_optimized'],   // https://cs.symfony.com/doc/rules/function_notation/native_function_invocation.html#include
        'scope' => 'namespaced',                // https://cs.symfony.com/doc/rules/function_notation/native_function_invocation.html#scope
        'strict' => true                        // https://cs.symfony.com/doc/rules/function_notation/native_function_invocation.html#strict
        ],
    ])
->setFinder($finder);
