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

if (isset($CFG->httpswwwroot)) {
    $root = $CFG->httpswwwroot;
} else {
    $root = $CFG->wwwroot;
}

$themerenderer = $PAGE->get_renderer('theme_leaf', 'widgets');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('bootstrap', 'theme_leaf');
$PAGE->requires->jquery_plugin('leaf', 'theme_leaf');
$PAGE->requires->jquery_plugin('backstretch', 'theme_leaf');
$PAGE->requires->jquery_plugin('twitter', 'theme_leaf');

echo $OUTPUT->doctype() ?>

<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
    <?php echo $themerenderer->leaf_login_background();?>
    </script>
</head>

<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses) ?>">

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php echo $themerenderer->leaf_header();?>



<div id="page">
    <div id="main-content" class="container clearfix">

        <div id="page-content" class="row-fluid">
            <div class="span6">
                <?php echo $OUTPUT->main_content() ?>
            </div>
            <div class="span6">
                <?php echo $themerenderer->leaf_frontpage_carrousel('desktop');?>
            </div>
        </div>
    </div>
    <div id="footer">
        <div class="container mobileonly">
             <div class="row-fluid">
                <div class="span12">
                    <div>
                        <?php echo $themerenderer->leaf_frontpage_carrousel('mobile');?>
                    </div>
                </div>
             </div>
        </div>
        <?php echo $themerenderer->leaf_footer(); ?>
        <?php echo $OUTPUT->standard_footer_html(); ?>
    </div>
</div>

<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>
