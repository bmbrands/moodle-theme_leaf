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

function leaf_process_css($css, $theme) {
    global $CFG, $PAGE;

    if ($CFG->httpswwwroot) {
        $root = $CFG->httpswwwroot;
    } else {
        $root = $CFG->wwwroot;
    }

    $raleway = "
    @font-face {
        font-family: 'Raleway';
        src: url('".$root."/theme/leaf/font/Raleway-Regular.ttf') format('truetype');
        font-weight: normal;
        font-style: normal;
    }
    ";

    $zocial = "
    @font-face {
      font-family: 'zocial';
      src: url('".$root."/theme/leaf/font/zocial.eot?94486700');
      src: url('".$root."/theme/leaf/font/zocial.eot?94486700#iefix') format('embedded-opentype'),
           url('".$root."/theme/leaf/font/zocial.woff?94486700') format('woff'),
           url('".$root."/theme/leaf/font/social.ttf?94486700') format('truetype'),
           url('".$root."/theme/leaf/font/zocial.svg?94486700#fontello') format('svg');
      font-weight: normal;
      font-style: normal;
    }";

    $css .= $raleway . $zocial;

    if (!empty($theme->settings->customcss)) {
        $css .=  $theme->settings->customcss;
    }
    return $css;

    return $css;
}


function leaf_forum_get_discussions($cm, $forumsort="d.timemodified DESC", $fullpost=true, $unused=-1, $limit=-1, $userlastmodified=false, $page=-1, $perpage=0) {
    global $CFG, $DB, $USER;

    $timelimit = '';

    $now = round(time(), -2);
    $params = array($cm->instance);

    $modcontext = context_module::instance($cm->id);



    if (!empty($CFG->forum_enabletimedposts)) { /// Users must fulfill timed posts

        if (!has_capability('mod/forum:viewhiddentimedposts', $modcontext)) {
            $timelimit = " AND ((d.timestart <= ? AND (d.timeend = 0 OR d.timeend > ?))";
            $params[] = $now;
            $params[] = $now;
            if (isloggedin()) {
                $timelimit .= " OR d.userid = ?";
                $params[] = $USER->id;
            }
            $timelimit .= ")";
        }
    }

    if ($limit > 0) {
        $limitfrom = 0;
        $limitnum  = $limit;
    } else if ($page != -1) {
        $limitfrom = $page*$perpage;
        $limitnum  = $perpage;
    } else {
        $limitfrom = 0;
        $limitnum  = 0;
    }

    $groupmode    = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm);

    if ($groupmode) {
        if (empty($modcontext)) {
            $modcontext = context_module::instance($cm->id);
        }

        if ($groupmode == VISIBLEGROUPS or has_capability('moodle/site:accessallgroups', $modcontext)) {
            if ($currentgroup) {
                $groupselect = "AND (d.groupid = ? OR d.groupid = -1)";
                $params[] = $currentgroup;
            } else {
                $groupselect = "";
            }

        } else {
            //seprate groups without access all
            if ($currentgroup) {
                $groupselect = "AND (d.groupid = ? OR d.groupid = -1)";
                $params[] = $currentgroup;
            } else {
                $groupselect = "AND d.groupid = -1";
            }
        }
    } else {
        $groupselect = "";
    }


    if (empty($forumsort)) {
        $forumsort = "d.timemodified DESC";
    }
    if (empty($fullpost)) {
        $postdata = "p.id,p.subject,p.modified,p.discussion,p.userid";
    } else {
        $postdata = "p.*";
    }

    if (empty($userlastmodified)) {  // We don't need to know this
        $umfields = "";
        $umtable  = "";
    } else {
        $umfields = ", um.firstname AS umfirstname, um.lastname AS umlastname";
        $umtable  = " LEFT JOIN {user} um ON (d.usermodified = um.id)";
    }

    $sql = "SELECT $postdata, d.name, d.timemodified, d.usermodified, d.groupid, d.timestart, d.timeend,
                   u.firstname, u.lastname, u.email, u.picture, u.imagealt $umfields
              FROM {forum_discussions} d
                   JOIN {forum_posts} p ON p.discussion = d.id
                   JOIN {user} u ON p.userid = u.id
                   $umtable
             WHERE d.forum = ? AND p.parent = 0
             $timelimit $groupselect
          ORDER BY $forumsort";
             return $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
}



function leaf_get_newsitem($d) {
    global $DB, $USER, $CFG, $OUTPUT;
    static $str;

    require_once($CFG->libdir . '/filelib.php');

    $discussion = $DB->get_record('forum_discussions', array('id' => $d,'course'=>1), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $discussion->course), '*', MUST_EXIST);
    $forum = $DB->get_record('forum', array('id' => $discussion->forum), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('forum', $forum->id, $course->id, false, MUST_EXIST);

    $parent = $discussion->firstpost;
    $post = forum_get_post_full($parent);
    $displaymode = -1;
    $canreply = false;
    $canrate = false;
    $sort = "p.created ASC";
    $posts = forum_get_all_discussion_posts($discussion->id, $sort, false);

    foreach ($posts as $post) {
        $post->course = $course->id;
        $post->forum  = $forum->id;
        $postuser = new stdClass;
        $postuser->id        = $post->userid;
        $postuser->firstname = $post->firstname;
        $postuser->lastname  = $post->lastname;
        $postuser->imagealt  = $post->imagealt;
        $postuser->picture   = $post->picture;
        $postuser->email     = $post->email;
        $postuser->fullname    = fullname($postuser);

        $output = '';

        $output .= html_writer::tag('a', '', array('id'=>'p'.$post->id));
        $output .= html_writer::start_tag('div', array('class'=>'forumpost clearfix firstpost starter'));
        $output .= html_writer::start_tag('div', array('class'=>'row header clearfix'));
        $output .= html_writer::start_tag('div', array('class'=>'left picture'));
        $output .= $OUTPUT->user_picture($postuser, array('courseid'=>$course->id));
        $output .= html_writer::end_tag('div');


        $output .= html_writer::start_tag('div', array('class'=>'topic  firstpost starter'));

        $postsubject = $post->subject;
        if (empty($post->subjectnoformat)) {
            $postsubject = format_string($postsubject);
        }
        $output .= html_writer::tag('div', $postsubject, array('class'=>'subject'));


        $output .= html_writer::tag('div', $postuser->fullname, array('class'=>'author'));

        $output .= html_writer::end_tag('div'); //topic
        $output .= html_writer::end_tag('div'); //row

        $output .= html_writer::start_tag('div', array('class'=>'row maincontent clearfix'));
        $output .= html_writer::start_tag('div', array('class'=>'left'));

        $output .= html_writer::tag('div', '&nbsp;', array('class'=>'grouppictures'));

        $output .= html_writer::end_tag('div'); //left side
        $output .= html_writer::start_tag('div', array('class'=>'no-overflow'));
        $output .= html_writer::start_tag('div', array('class'=>'content'));


        $options = new stdClass;
        $options->para    = false;
        $options->trusted = $post->messagetrust;

        // Prepare whole post
        $postclass    = 'fullpost';
        $postcontent  = format_text($post->message, $post->messageformat, $options, $course->id);

        // Output the post content
        $output .= html_writer::tag('div', $postcontent, array('class'=>'posting '.$postclass));
        $output .= html_writer::end_tag('div'); // Content
        $output .= html_writer::end_tag('div'); // Content mask
        $output .= html_writer::end_tag('div'); // Row

        $output .= html_writer::start_tag('div', array('class'=>'row side'));
        $output .= html_writer::tag('div','&nbsp;', array('class'=>'left'));
        $output .= html_writer::start_tag('div', array('class'=>'options clearfix'));


        // Close remaining open divs
        $output .= html_writer::end_tag('div'); // content
        $output .= html_writer::end_tag('div'); // row
        $output .= html_writer::end_tag('div'); // forumpost
        echo $output;
    }
}

/**
 * Loads the JavaScript for the zoom function.
 *
 * @param moodle_page $page Pass in $PAGE.
 */
function theme_leaf_initialise_zoom(moodle_page $page) {
    user_preference_allow_ajax_update('theme_leaf_zoom', PARAM_TEXT);
    $page->requires->yui_module('moodle-theme_leaf-zoom', 'M.theme_leaf.zoom.init', array());
}

/**
 * Get the user preference for the zoom function.
 */
function theme_leaf_get_zoom() {
    global $SESSION;
    if (isset($SESSION->justloggedin)) {
        set_user_preferences(array('theme_leaf_zoom' => 'nozoom'));
    }
    return get_user_preferences('theme_leaf_zoom', '');
}


