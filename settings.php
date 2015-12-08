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

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    global $CFG;
    $bgpath = $CFG->dirroot . '/theme/leaf/pix/backgrounds/';

    $backgroundfolders = array();

    $directories = scandir($bgpath);
    foreach($directories as $directory){
        if($directory=='.' || $directory=='..' ){
        }else{
            $backgroundfolders[$directory] = $directory;
        }
    }

    $name = 'theme_leaf/backgroundfolder';
    $title = get_string('backgroundfolder','theme_leaf');
    $description = get_string('backgroundfolderdesc', 'theme_leaf');
    $setting = new admin_setting_configselect($name, $title, $description, 0, $backgroundfolders);
    $settings->add($setting);

    // twitter
    $name = 'theme_leaf/twitter';
    $title = get_string('twitter','theme_leaf');
    $description = get_string('twitterdesc', 'theme_leaf');
    $setting = new admin_setting_configtext($name, $title, $description, 'https://twitter.com/denverseminary');
    $settings->add($setting);

    // facebook
    $name = 'theme_leaf/facebook';
    $title = get_string('facebook','theme_leaf');
    $description = get_string('facebookdesc', 'theme_leaf');
    $setting = new admin_setting_configtext($name, $title, $description, 'https://www.facebook.com/denverseminary');
    $settings->add($setting);

    $name = 'theme_leaf/helpinfo';
    $title = get_string('helpinfo','theme_leaf');
    $description = get_string('helpinfodesc', 'theme_leaf');
    $default = 'For technical help with this site, please call the
          Denver Seminary Help Desk at 303-376-6983 from off-campus,
          or x2020 from on-campus. Or send an email to <a mailto:"helpdesk@denverseminary.edu">
          helpdesk@denverseminary.edu</a>.
          <br>
          Help Desk hours:<br>
          Mon-Thurs: 8:00am to 5:00pm<br>
          Friday: 8:00am to 5:00pm<br>
          Saturday: 7:30am to 9:30am';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $settings->add($setting);

    $name = 'theme_leaf/helpusername';
    $title = get_string('helpusername','theme_leaf');
    $description = get_string('helpusernamedesc', 'theme_leaf');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $settings->add($setting);

    // extra1
    $name = 'theme_leaf/extra1';
    $title = get_string('extra1','theme_leaf');
    $default = 'Student Resources';
    $setting = new admin_setting_configtext($name, $title, '', $default);
    $settings->add($setting);

    $name = 'theme_leaf/extra1text';
    $title = get_string('extra1text','theme_leaf');
    $description = get_string('extra1textdesc', 'theme_leaf');
    $default = 'These tutorials were created by Educational
          Technology to help students and faculty as they work with Moodle. If you have an idea for a
          tutorial or have feedback please send it to <a mailto:"EducationalTechnologies@denverseminary.edu">
          EducationalTechnologies@denverseminary.edu</a>.<br>
          <br>
          Not all classes have Moodle sites. For your most accurate course schedule, acces MyDenSem.';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $settings->add($setting);

    // extra2
    $name = 'theme_leaf/extra2';
    $title = get_string('extra2','theme_leaf');
    $default = 'Faculty Resources';
    $setting = new admin_setting_configtext($name, $title, '', $default);
    $settings->add($setting);

    $name = 'theme_leaf/extra2text';
    $title = get_string('extra2text','theme_leaf');
    $description = get_string('extra2textdesc', 'theme_leaf');
    $default = 'If you forget your Moodle password, use the "Lost password?" link in the right column.<br>
    <br>
    Moodle tutorial 10 explains why Moodle sites from the previous semester stay available for a few weeks into the next semester.
    Moodle tutorials are in the left-hand sidebar.';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $settings->add($setting);

    $name = 'theme_leaf/customcss';
    $title = get_string('customcss', 'theme_leaf');
    $description = get_string('customcssdesc', 'theme_leaf');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
}
