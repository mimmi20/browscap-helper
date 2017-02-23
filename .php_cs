<?php
$header = <<<EOF
This file is part of the browscap-helper package.

(c) Thomas Mueller <mimmi20@live.de>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
;

ini_set('memory_limit', '-1');

return Symfony\CS\Config\Config::create()
    ->level(\Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers(
        array(
        // PSR-0
            'psr0',
        // PSR-1
            'encoding',
            'short_tag',
            'full_opening_tag',
        // PSR-2
            'braces',
            'elseif',
            'eof_ending',
            'function_call_space',
            'function_declaration',
            'indentation',
            'line_after_namespace',
            'linefeed',
            'lowercase_constants',
            'lowercase_keywords',
            'method_argument_space',
            'multiple_use',
            'parenthesis',
            'php_closing_tag',
            'single_line_after_imports',
            'trailing_spaces',
            'visibility',
        // Symfony
            'duplicate_semicolon',
            'extra_empty_lines',
            'join_function',
            'object_operator',
            'remove_lines_between_uses',
            'standardize_not_equal',
            'unused_use',
            'whitespacy_lines',
        // Contrib
            'align_double_arrow',
            'align_equals',
            'concat_with_spaces',
            'list_commas',
            'namespace_no_leading_whitespace',
            'no_blank_lines_after_class_opening',
            'phpdoc_indent',
            'phpdoc_no_access',
            'phpdoc_no_empty_return',
            'phpdoc_no_package',
            'phpdoc_params',
            'phpdoc_scalar',
            'phpdoc_trim',
            'phpdoc_types',
            'phpdoc_var_without_name',
            'return',
            'self_accessor',
            'short_array_syntax',
            'single_quote',
            'spaces_before_semicolon',
            'spaces_cast',
            'ternary_spaces',
            'trim_array_spaces',
            'array_element_no_space_before_comma',
            'array_element_white_space_after_comma',
            'blankline_after_open_tag',
            'function_typehint_space',
            'include',
            'multiline_array_trailing_comma',
            'new_with_braces',
            'operators_spaces',
            'phpdoc_inline_tag',
            'pre_increment',
            'print_to_echo',
            'remove_leading_slash_use',
            'short_bool_cast',
            'single_array_no_trailing_comma',
            'single_blank_line_before_namespace',
            'ereg_to_preg',
            'multiline_spaces_before_semicolon',
            'newline_after_open_tag',
            'ordered_use',
            'phpdoc_order',
            'short_echo_tag',
            'strict',
            'combine_consecutive_unsets',
            'no_useless_else',
            'no_useless_return',
            'php_unit_construct',
            'php_unit_strict',
        )
    )
    ->finder($finder);

