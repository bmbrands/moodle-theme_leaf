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

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hasheader = (empty($PAGE->layout_options['noheader']));
$hascolumns = (!empty($PAGE->layout_options['hascolumns']));
$hasbackground = (!empty($PAGE->layout_options['hasbackground']));
$hasnews = (!empty($PAGE->layout_options['hasnews']));
$myhome =  (!empty($PAGE->layout_options['myhome']));

$themerenderer = $PAGE->get_renderer('theme_leaf', 'widgets');

$hassidepre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));

$showsidepre = ($hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT));

if ($showsidepre) {
    theme_leaf_initialise_zoom($PAGE);
}

if ($PAGE->user_is_editing()) {
    if ($PAGE->blocks->is_known_region('side-pre')) {
        $showsidepre = true;
    }
    if ($PAGE->blocks->is_known_region('side-post')) {
        $showsidepost = true;
    }
}

$haslogo = (!empty($PAGE->theme->settings->logo));

$hasfootnote = (!empty($PAGE->theme->settings->footnote));

$courseheader = $coursecontentheader = $coursecontentfooter = $coursefooter = '';

if (empty($PAGE->layout_options['nocourseheaderfooter'])) {
    $courseheader = $OUTPUT->course_header();
    $coursecontentheader = $OUTPUT->course_content_header();
    if (empty($PAGE->layout_options['nocoursefooter'])) {
        $coursecontentfooter = $OUTPUT->course_content_footer();
        $coursefooter = $OUTPUT->course_footer();
    }
}

$layout = 'pre-and-post';
if ($showsidepre) {
    $layout = 'side-pre-only';
} else {
    $layout = 'content-only';
}
if ($hasbackground) {
    $bodyclasses[] = 'hasbackground';
}

$bodyclasses[] = $layout;
$bodyclasses[] = theme_leaf_get_zoom();

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

    <link rel="stylesheet" href="<?php echo $CFG->wwwroot;?>/theme/leaf/style/leaf-socialicoregular.css">

    <?php if ($hasbackground) { ?>
    <style>
    #page {
        background-image: url('<?php echo $OUTPUT->login_background();?>');
        background-repeat: no-repeat;
    }
    </style>
    <?php }?>

</head>

<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">

<?php echo $OUTPUT->standard_top_of_body_html() ?>
<?php if (isloggedin()) {
     echo $themerenderer->leaf_social();
}?>
<?php echo $themerenderer->leaf_header();?>


<?php if ($hasnews) { ?>
<?php echo $themerenderer->leaf_frontpage_carrousel('desktop');?>
<?php } ?>

<div id="page">
    <div id="main-content" class="clearfix">
        <?php if ($hasheader) { ?>
        <div id="page-header" class="clearfix">
            <h2 class="short_headline"><span><?php echo $PAGE->title ?></span></h2>
            <?php if (!empty($courseheader)) { ?>
                <div id="course-header"><?php echo $courseheader; ?></div>
            <?php } ?>
        </div>
        <?php } ?>

        <?php if ($hasnavbar) { ?>
        <div class="">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="breadcrumb-button">
                            <?php echo $PAGE->button; ?>
                        </div>
                        <?php echo $themerenderer->navbar(); ?>

                    </div>
                </div>
        </div>
        <?php } ?>
        <div id="page-content" class="row-fluid">


            <?php if ($layout === 'side-pre-only') { ?>
            <div id="region-main" class="span9 pull-right">


            <?php } else if ($layout === 'content-only') { ?>
            <div id="region-main" class="span12">

            <?php } ?>

                <?php if ($hasheader) { ?>
                <div id="page-header" class="clearfix">
                    <?php if (!empty($courseheader)) { ?>
                        <div id="course-header"><?php echo $courseheader; ?>
                        </div>
                    <?php } ?>
                </div>
                <?php } ?>

            <?php echo $coursecontentheader; ?>
            <?php if ($layout === 'side-pre-only') { 
                echo $themerenderer->content_zoom();
            } ?>
            <?php echo $OUTPUT->main_content() ?>
            <?php echo $coursecontentfooter; ?>
            </div> <!-- end spanx -->

            <?php if ($layout === 'side-pre-only') { ?>
            <div class="span3 pull-left desktop-first-column">
                <div id="region-post" class="block-region">
                    <div class="region-content">
                               <?php
                                  if($hassidepre){
                                      echo $OUTPUT->blocks_for_region('side-pre');
                                  }
                              ?>
                    </div>
                </div>
            </div>
            <?php } ?>

            </div> <!-- end row-fluid -->
            </div>



    <div id="footer">
        <div class="container mobileonly">
             <div class="row-fluid">
                <div class="span12">
                    <div>
                    <?php if ($hasnews) { ?>
                        <?php echo $themerenderer->leaf_frontpage_carrousel('mobile');?>
                    <?php } ?>
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
