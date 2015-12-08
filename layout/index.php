<?php
/**
 * Theme Leaf, an theme build on Bootstrap
 *
 *
 * For more information about themes by Bas Brands, see:
 * http://theming.sonsbeekmedia.nl
 *
 * @package   theme_leaf
 * @copyright 2013 Bas Brands, basbrands.nl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot . '/theme/leaf/lib.php');
$text = optional_param('page', '', PARAM_TEXT);
$d = optional_param('d', '', PARAM_TEXT);


$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url('/theme/leaf/layout/index.php');
$PAGE->set_heading(format_string("Full Page View"));
$PAGE->blocks->show_only_fake_blocks();
echo $OUTPUT->header();

if (!empty($d)) {
    leaf_get_newsitem($d);
}

if (isset($PAGE->theme->settings->$text)) {
    echo ($PAGE->theme->settings->$text);
}

echo $OUTPUT->footer();