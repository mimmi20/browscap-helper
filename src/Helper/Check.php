<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2017, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Helper;

/**
 * Class Check
 *
 * @author  Thomas Mueller <mimmi20@live.de>
 */
class Check
{
    public const MINIMUM = 'minimum';
    public const MEDIUM  = 'medium';

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param string $checklevel
     *
     * @return array
     */
    public function getChecks(string $checklevel): array
    {
        $checks = [
            'Browser' => [
                'key' => 'mobile_browser',
            ],
            'Browser Version' => [
                'key' => 'mobile_browser_version',
            ],
            'Browser Modus' => [
                'key' => 'mobile_browser_modus',
            ],
            'Browser Bits' => [
                'key' => 'mobile_browser_bits',
            ],
            'Browser Typ' => [
                'key' => 'browser_type',
            ],
            'Browser Hersteller' => [
                'key' => 'mobile_browser_manufacturer',
            ],
            'Engine' => [
                'key' => 'renderingengine_name',
            ],
            'Engine Version' => [
                'key' => 'renderingengine_version',
            ],
            'Engine Hersteller' => [
                'key' => 'renderingengine_manufacturer',
            ],
            'OS' => [
                'key' => 'device_os',
            ],
            'OS Version' => [
                'key' => 'device_os_version',
            ],
            'OS Bits' => [
                'key' => 'device_os_bits',
            ],
            'OS Hersteller' => [
                'key' => 'device_os_manufacturer',
            ],
            'Device Brand Name' => [
                'key' => 'brand_name',
            ],
            'Device Marketing Name' => [
                'key' => 'marketing_name',
            ],
            'Device Model Name' => [
                'key' => 'model_name',
            ],
            'Device Hersteller' => [
                'key' => 'manufacturer_name',
            ],
            'Device Typ' => [
                'key' => 'device_type',
            ],
//            'Desktop'               => [
//                'key'         => ['isDesktop'],
//            ],
//            'TV'                    => [
//                'key'         => ['isTvDevice'],
//            ],
//            'Mobile'                => [
//                'key'         => ['isMobileDevice'],
//            ],
//            'Tablet'                => [
//                'key'         => ['isTablet'],
//            ],
//            'Bot'                   => [
//                'key'         => ['isCrawler'],
//            ],
//            'Console'               => [
//                'key'         => ['isConsole'],
//            ],
//            'Transcoder'            => [
//                'key'         => 'is_transcoder',
//            ],
//            'Syndication-Reader'    => [
//                'key'         => 'is_syndication_reader',
//            ],
            'pointing_method' => [
                'key' => 'pointing_method',
            ],
            'has_qwerty_keyboard' => [
                'key' => 'has_qwerty_keyboard',
            ],
            // display
            'resolution_width' => [
                'key' => 'resolution_width',
            ],
            'resolution_height' => [
                'key' => 'resolution_height',
            ],
            'dual_orientation' => [
                'key' => 'dual_orientation',
            ],
            'colors' => [
                'key' => 'colors',
            ],
        ];

        if (self::MEDIUM === $checklevel) {
            $checks += [
                // product info
                'can_skip_aligned_link_row' => [
                    'key' => 'can_skip_aligned_link_row',
                ],
                'device_claims_web_support' => [
                    'key' => 'device_claims_web_support',
                ],
                'can_assign_phone_number' => [
                    'key' => 'can_assign_phone_number',
                ],
                'nokia_feature_pack' => [
                    'key' => 'nokia_feature_pack',
                ],
                'nokia_series' => [
                    'key' => 'nokia_series',
                ],
                'nokia_edition' => [
                    'key' => 'nokia_edition',
                ],
                'ununiqueness_handler' => [
                    'key' => 'ununiqueness_handler',
                ],
                'uaprof' => [
                    'key' => 'uaprof',
                ],
                'uaprof2' => [
                    'key' => 'uaprof2',
                ],
                'uaprof3' => [
                    'key' => 'uaprof3',
                ],
                'unique' => [
                    'key' => 'unique',
                ],
                'model_extra_info' => [
                    'key' => 'model_extra_info',
                ],
                // display
                'physical_screen_width' => [
                    'key' => 'physical_screen_width',
                ],
                'physical_screen_height' => [
                    'key' => 'physical_screen_height',
                ],
                'columns' => [
                    'key' => 'columns',
                ],
                'rows' => [
                    'key' => 'rows',
                ],
                'max_image_width' => [
                    'key' => 'max_image_width',
                ],
                'max_image_height' => [
                    'key' => 'max_image_height',
                ],
                // markup
                'utf8_support' => [
                    'key' => 'utf8_support',
                ],
                'multipart_support' => [
                    'key' => 'multipart_support',
                ],
                'supports_background_sounds' => [
                    'key' => 'supports_background_sounds',
                ],
                'supports_vb_script' => [
                    'key' => 'supports_vb_script',
                ],
                'supports_java_applets' => [
                    'key' => 'supports_java_applets',
                ],
                'supports_activex_controls' => [
                    'key' => 'supports_activex_controls',
                ],
                'preferred_markup' => [
                    'key' => 'preferred_markup',
                ],
                'html_web_3_2' => [
                    'key' => 'html_web_3_2',
                ],
                'html_web_4_0' => [
                    'key' => 'html_web_4_0',
                ],
                'html_wi_oma_xhtmlmp_1_0' => [
                    'key' => 'html_wi_oma_xhtmlmp_1_0',
                ],
                'wml_1_1' => [
                    'key' => 'wml_1_1',
                ],
                'wml_1_2' => [
                    'key' => 'wml_1_2',
                ],
                'wml_1_3' => [
                    'key' => 'wml_1_3',
                ],
                'xhtml_support_level' => [
                    'key' => 'xhtml_support_level',
                ],
                'html_wi_imode_html_1' => [
                    'key' => 'html_wi_imode_html_1',
                ],
                'html_wi_imode_html_2' => [
                    'key' => 'html_wi_imode_html_2',
                ],
                'html_wi_imode_html_3' => [
                    'key' => 'html_wi_imode_html_3',
                ],
                'html_wi_imode_html_4' => [
                    'key' => 'html_wi_imode_html_4',
                ],
                'html_wi_imode_html_5' => [
                    'key' => 'html_wi_imode_html_5',
                ],
                'html_wi_imode_htmlx_1' => [
                    'key' => 'html_wi_imode_htmlx_1',
                ],
                'html_wi_imode_htmlx_1_1' => [
                    'key' => 'html_wi_imode_htmlx_1_1',
                ],
                'html_wi_w3_xhtmlbasic' => [
                    'key' => 'html_wi_w3_xhtmlbasic',
                ],
                'html_wi_imode_compact_generic' => [
                    'key' => 'html_wi_imode_compact_generic',
                ],
                'voicexml' => [
                    'key' => 'voicexml',
                ],
                // chtml
                'chtml_table_support' => [
                    'key' => 'chtml_table_support',
                ],
                'imode_region' => [
                    'key' => 'imode_region',
                ],
                'chtml_can_display_images_and_text_on_same_line' => [
                    'key' => 'chtml_can_display_images_and_text_on_same_line',
                ],
                'chtml_displays_image_in_center' => [
                    'key' => 'chtml_displays_image_in_center',
                ],
                'chtml_make_phone_call_string' => [
                    'key' => 'chtml_make_phone_call_string',
                ],
                'emoji' => [
                    'key' => 'emoji',
                ],
                // xhtml
                'xhtml_select_as_radiobutton' => [
                    'key' => 'xhtml_select_as_radiobutton',
                ],
                'xhtml_avoid_accesskeys' => [
                    'key' => 'xhtml_avoid_accesskeys',
                ],
                'xhtml_select_as_dropdown' => [
                    'key' => 'xhtml_select_as_dropdown',
                ],
                'xhtml_supports_iframe' => [
                    'key' => 'xhtml_supports_iframe',
                ],
                'xhtml_supports_forms_in_table' => [
                    'key' => 'xhtml_supports_forms_in_table',
                ],
                'xhtmlmp_preferred_mime_type' => [
                    'key' => 'xhtmlmp_preferred_mime_type',
                ],
                'xhtml_select_as_popup' => [
                    'key' => 'xhtml_select_as_popup',
                ],
                'xhtml_honors_bgcolor' => [
                    'key' => 'xhtml_honors_bgcolor',
                ],
                'xhtml_file_upload' => [
                    'key' => 'xhtml_file_upload',
                ],
                'xhtml_preferred_charset' => [
                    'key' => 'xhtml_preferred_charset',
                ],
                'xhtml_supports_css_cell_table_coloring' => [
                    'key' => 'xhtml_supports_css_cell_table_coloring',
                ],
                'xhtml_autoexpand_select' => [
                    'key' => 'xhtml_autoexpand_select',
                ],
                'accept_third_party_cookie' => [
                    'key' => 'accept_third_party_cookie',
                ],
                'xhtml_make_phone_call_string' => [
                    'key' => 'xhtml_make_phone_call_string',
                ],
                'xhtml_allows_disabled_form_elements' => [
                    'key' => 'xhtml_allows_disabled_form_elements',
                ],
                'xhtml_supports_invisible_text' => [
                    'key' => 'xhtml_supports_invisible_text',
                ],
                'cookie_support' => [
                    'key' => 'cookie_support',
                ],
                'xhtml_send_mms_string' => [
                    'key' => 'xhtml_send_mms_string',
                ],
                'xhtml_table_support' => [
                    'key' => 'xhtml_table_support',
                ],
                'xhtml_display_accesskey' => [
                    'key' => 'xhtml_display_accesskey',
                ],
                'xhtml_can_embed_video' => [
                    'key' => 'xhtml_can_embed_video',
                ],
                'xhtml_supports_monospace_font' => [
                    'key' => 'xhtml_supports_monospace_font',
                ],
                'xhtml_supports_inline_input' => [
                    'key' => 'xhtml_supports_inline_input',
                ],
                'xhtml_document_title_support' => [
                    'key' => 'xhtml_document_title_support',
                ],
                'xhtml_support_wml2_namespace' => [
                    'key' => 'xhtml_support_wml2_namespace',
                ],
                'xhtml_readable_background_color1' => [
                    'key' => 'xhtml_readable_background_color1',
                ],
                'xhtml_format_as_attribute' => [
                    'key' => 'xhtml_format_as_attribute',
                ],
                'xhtml_supports_table_for_layout' => [
                    'key' => 'xhtml_supports_table_for_layout',
                ],
                'xhtml_readable_background_color2' => [
                    'key' => 'xhtml_readable_background_color2',
                ],
                'xhtml_send_sms_string' => [
                    'key' => 'xhtml_send_sms_string',
                ],
                'xhtml_format_as_css_property' => [
                    'key' => 'xhtml_format_as_css_property',
                ],
                'opwv_xhtml_extensions_support' => [
                    'key' => 'opwv_xhtml_extensions_support',
                ],
                'xhtml_marquee_as_css_property' => [
                    'key' => 'xhtml_marquee_as_css_property',
                ],
                'xhtml_nowrap_mode' => [
                    'key' => 'xhtml_nowrap_mode',
                ],
                // image format
                'jpg' => [
                    'key' => 'jpg',
                ],
                'gif' => [
                    'key' => 'gif',
                ],
                'bmp' => [
                    'key' => 'bmp',
                ],
                'wbmp' => [
                    'key' => 'wbmp',
                ],
                'gif_animated' => [
                    'key' => 'gif_animated',
                ],
                'png' => [
                    'key' => 'png',
                ],
                'greyscale' => [
                    'key' => 'greyscale',
                ],
                'transparent_png_index' => [
                    'key' => 'transparent_png_index',
                ],
                'epoc_bmp' => [
                    'key' => 'epoc_bmp',
                ],
                'svgt_1_1_plus' => [
                    'key' => 'svgt_1_1_plus',
                ],
                'svgt_1_1' => [
                    'key' => 'svgt_1_1',
                ],
                'transparent_png_alpha' => [
                    'key' => 'transparent_png_alpha',
                ],
                'tiff' => [
                    'key' => 'tiff',
                ],
                // security
                'https_support' => [
                    'key' => 'https_support',
                ],
                // storage
                'max_url_length_bookmark' => [
                    'key' => 'max_url_length_bookmark',
                ],
                'max_url_length_cached_page' => [
                    'key' => 'max_url_length_cached_page',
                ],
                'max_url_length_in_requests' => [
                    'key' => 'max_url_length_in_requests',
                ],
                'max_url_length_homepage' => [
                    'key' => 'max_url_length_homepage',
                ],
                // ajax
                'ajax_support_getelementbyid' => [
                    'key' => 'ajax_support_getelementbyid',
                ],
                'ajax_xhr_type' => [
                    'key' => 'ajax_xhr_type',
                ],
                'ajax_support_event_listener' => [
                    'key' => 'ajax_support_event_listener',
                ],
                'ajax_support_javascript' => [
                    'key' => 'ajax_support_javascript',
                ],
                'ajax_manipulate_dom' => [
                    'key' => 'ajax_manipulate_dom',
                ],
                'ajax_support_inner_html' => [
                    'key' => 'ajax_support_inner_html',
                ],
                'ajax_manipulate_css' => [
                    'key' => 'ajax_manipulate_css',
                ],
                'ajax_support_events' => [
                    'key' => 'ajax_support_events',
                ],
                'ajax_preferred_geoloc_api' => [
                    'key' => 'ajax_preferred_geoloc_api',
                ],
                // pdf
                'pdf_support' => [
                    'key' => 'pdf_support',
                ],
                // third_party
                'jqm_grade' => [
                    'key' => 'jqm_grade',
                ],
                'is_sencha_touch_ok' => [
                    'key' => 'is_sencha_touch_ok',
                ],
                // html
                'image_inlining' => [
                    'key' => 'image_inlining',
                ],
                'canvas_support' => [
                    'key' => 'canvas_support',
                ],
                'viewport_width' => [
                    'key' => 'viewport_width',
                ],
                'html_preferred_dtd' => [
                    'key' => 'html_preferred_dtd',
                ],
                'viewport_supported' => [
                    'key' => 'viewport_supported',
                ],
                'viewport_minimum_scale' => [
                    'key' => 'viewport_minimum_scale',
                ],
                'viewport_initial_scale' => [
                    'key' => 'viewport_initial_scale',
                ],
                'mobileoptimized' => [
                    'key' => 'mobileoptimized',
                ],
                'viewport_maximum_scale' => [
                    'key' => 'viewport_maximum_scale',
                ],
                'viewport_userscalable' => [
                    'key' => 'viewport_userscalable',
                ],
                'handheldfriendly' => [
                    'key' => 'handheldfriendly',
                ],
                // css
                'css_spriting' => [
                    'key' => 'css_spriting',
                ],
                'css_gradient' => [
                    'key' => 'css_gradient',
                ],
                'css_gradient_linear' => [
                    'key' => 'css_gradient_linear',
                ],
                'css_border_image' => [
                    'key' => 'css_border_image',
                ],
                'css_rounded_corners' => [
                    'key' => 'css_rounded_corners',
                ],
                'css_supports_width_as_percentage' => [
                    'key' => 'css_supports_width_as_percentage',
                ],
                // bugs
                'empty_option_value_support' => [
                    'key' => 'empty_option_value_support',
                ],
                'basic_authentication_support' => [
                    'key' => 'basic_authentication_support',
                ],
                'post_method_support' => [
                    'key' => 'post_method_support',
                ],
                // rss
                'rss_support' => [
                    'key' => 'rss_support',
                ],
                // sms
                'sms_enabled' => [
                    'key' => 'sms_enabled',
                ],
                // chips
                'nfc_support' => [
                    'key' => 'nfc_support',
                ],
            ];
        }

        return $checks;
    }
}
