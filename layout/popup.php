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

echo $OUTPUT->doctype() ?>

<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->image_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses) ?>">

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php echo $OUTPUT->main_content() ?>

<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>
