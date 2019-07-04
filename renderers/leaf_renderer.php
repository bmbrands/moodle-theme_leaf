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

class theme_leaf_widgets_renderer extends core_renderer {

    /*
     * This renders the navbar.
     * Uses bootstrap compatible html.
     */
    public function navbar() {
        $items = $this->page->navbar->get_items();
        $breadcrumbs = array();
        foreach ($items as $item) {
            $item->hideicon = true;
            $breadcrumbs[] = $this->render($item);
        }
        $divider = '<span class="divider">/</span>';
        $list_items = '<li>'.join(" $divider</li><li>", $breadcrumbs).'</li>';
        $title = '<span class="accesshide">'.get_string('pagepath').'</span>';
        return $title . "<ul class=\"breadcrumb\">$list_items</ul>";
    }
    /**
     * Renders the login background transition view
     *
     */
    public function leaf_login_background() {
        global $CFG;
        if (isset($CFG->httpswwwroot)) {
            $root = $CFG->httpswwwroot;
        } else {
            $root = $CFG->wwwroot;
        }
        $myimages = array();

        $selectedfolder = 'fall';
        if (!empty($this->page->theme->settings->backgroundfolder)) {
            $selectedfolder = $this->page->theme->settings->backgroundfolder;
        }

        $directory = $CFG->dirroot . '/theme/leaf/pix/backgrounds/' . $selectedfolder . '/';
        $imagecount = 0;
        if ( $dirhandle = opendir($directory) ) {
            while (false !== ($entry = readdir($dirhandle))) {
                if (!preg_match('/JPG/i',$entry)) {continue;}
                $imagecount++;
                $myimages[$imagecount] = $entry;
            }
        }


        if ($imagecount == 0 ){
            return '';
        }

        sort($myimages);

        $javascript = '
          jQuery(document).ready(function($) {
          $.backstretch([';

        foreach ($myimages as $myimage) {
            $javascript .= '"' . $root . '/theme/leaf/pix/backgrounds/' . $selectedfolder .'/'. $myimage . '",';
        }
        $javascript .= '], {duration: 10000, fade: 750});
        });';
        return $javascript;
    }

    /*
     * Overriding the custom_menu function ensures the custom menu is
     * always shown, even if no menu items are configured in the global
     * theme settings page.
     * We use the sitename as the first menu item
     */
    public function custom_menu($custommenuitems = '') {
        global $CFG;

        if (!empty($CFG->custommenuitems)) {
            $custommenuitems .= $CFG->custommenuitems;
        }
        $custommenu = new custom_menu($custommenuitems, current_language());
        return $this->render_custom_menu($custommenu);
    }

    /*
     * This renders the bootstrap top menu
     * This renderer is needed to enable the Bootstrap style navigation
     *
     */
    protected function render_custom_menu(custom_menu $menu) {
        global $COURSE, $PAGE, $CFG, $USER;

        $content = '';
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item, 1);
        }
        $content .= '';

        return $content;
    }

    /*
     * This code renders the custom menu items for the
     * bootstrap dropdown menu
     */
    protected function render_custom_menu_item(custom_menu_item $menunode, $level = 0 ) {
        static $submenucount = 0;

        if ($menunode->has_children()) {

            $dropdowntype = ($level == 1) ? 'parent dcjq-parent-li' : 'parent dcjq-parent-li';

            $content = html_writer::start_tag('li', array('class'=>$dropdowntype));

            // If the child has menus, render it as a sub menu.
            $submenucount++;
            $url = ($menunode->get_url() !== null)
            ? $menunode->get_url()
            : '#cm_submenu_' . $submenucount
            ;

            $content .= html_writer::start_tag('a', array('href'=>'javascript:void(0)', 'class'=>'dcjq-parent'));
            $content .= $menunode->get_title();


            $content .= html_writer::tag('i', '');
            $content .= html_writer::tag('span', '',array('class'=>'dcjq-icon'));
            $content .= html_writer::tag('span', '',array('class'=>'dcjq-icon'));

            $content .= html_writer::end_tag('a');

            $content .= html_writer::start_tag('ul');
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode, 0);
            }

            $content .= html_writer::end_tag('ul');

        } else {

            $content = html_writer::start_tag('li');

            // The node doesn't have children so produce a final menuitem.
            $url = ($menunode->get_url() !== null) ? $menunode->get_url() : '#';

            $content .= html_writer::link($url, $menunode->get_text(), array('title'=>$menunode->get_title()));
        }
        return $content;
    }

    public function leaf_frontpage_carrousel($device) {
        global $COURSE, $CFG;

        if (isloggedin()  && !is_siteadmin() && $COURSE->id != 1) {
            return '';
        }

        if (!(($this->page->bodyid == 'page-login-index') || ($this->page->bodyid == 'page-my-index'))) {
            return '';
        }


        require_once($CFG->dirroot.'/mod/forum/lib.php');   // We'll need this

        $content = '';

        if (!$forum = forum_get_course_forum($COURSE->id, 'news')) {
            return '';
        }

        $modinfo = get_fast_modinfo($COURSE);
        if (empty($modinfo->instances['forum'][$forum->id])) {
            return '';
        }
        $cm = $modinfo->instances['forum'][$forum->id];

        if (!$cm->uservisible) {
            return '';
        }

        $context = context_module::instance($cm->id);

        /// First work out whether we can post to this group and if so, include a link
        $groupmode    = groups_get_activity_groupmode($cm);
        $currentgroup = groups_get_activity_group($cm, true);

        if (forum_user_can_post_discussion($forum, $currentgroup, $groupmode, $cm)) {
            $link = '<li><a href="'.$CFG->wwwroot.'/mod/forum/post.php?forum='.$forum->id.'">'.
            get_string('addsitenews', 'theme_leaf').'</a></li>';
            return $link;
        }

        if (! $discussions = leaf_forum_get_discussions($cm, 'p.modified DESC', true,
        $currentgroup, $COURSE->newsitems) ) {
            return '';
        }

        $strftimerecent = get_string('strftimerecent');

        $indicators = '';
        $carousel_items = '';
        $extra ='active ';
        $count = 0;
        $prev = html_writer::link('#myCarousel' . $device, "&lsaquo;" , array('class'=>'carousel-control left', 'data-slide'=>'prev'));
        $next = html_writer::link('#myCarousel' . $device, "&rsaquo;" , array('class'=>'carousel-control right', 'data-slide'=>'next'));

        foreach ($discussions as $discussion) {
            $strmore = get_string('read_more', 'theme_leaf');
            if (isset($discussion->message)) {
                if (strlen(strip_tags($discussion->message)) > 400) {
                    $message = substr(strip_tags($discussion->message),0,400) . '..';
                } else {
                    $message = $discussion->message;
                    $strmore = '';
                }
            } else {
                $message = '';
            }

            $discussion->subject = format_string($discussion->subject, true, $forum->course);

            $carousel_items .= '<div class="'.$extra.'item forumpost ">'.

                         '<div class="head clearfix">'.
                         '<div class="info"><h2>' . get_string('sitenews','theme_leaf').'</h2></div>' .
                         '<h3>'. $discussion->subject .'</h3>'.
            userdate($discussion->modified, $strftimerecent).'
                         </div><br>'.
                         '<div class="message">'. $message . ' </div>' .
                         '<div class="readmore"><a href="'.$CFG->wwwroot.'/theme/leaf/layout/index.php?d='.
            $discussion->discussion.'">'.
            $strmore.'</a></div>'.
                         "</div>\n";

            $extra = '';

        }

        // Carrousel markup
        $content .= html_writer::start_tag('div',array('class'=>'sitenews' . $device));
        $content .= html_writer::start_tag('div',array('id'=>'myCarousel' . $device,'class'=>'carousel slide'));

        $content .= html_writer::start_tag('div',array('class'=>'carousel-inner'));
        $content .= '<div class="newscontrols">' . $next . $prev . '</div>' ;

        $content .= $carousel_items;
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');

        return $content ;
    }

    public function leaf_header() {
        global $CFG, $COURSE;
        $content = html_writer::start_tag('div',array('class'=>'leafheader header clearfix'));

        $content .= html_writer::start_tag('div',array('class'=>'innerheader'));
        $content .= html_writer::start_tag('div',array('class'=>'mobile-menu-holder'));
        $content .= html_writer::end_tag('div');

        $content .= html_writer::start_tag('div',array('class'=>'menu-wrapper'));
        $content .= html_writer::start_tag('nav',array('id'=>'main_menu'));
        $content .= html_writer::start_tag('ul',array('class'=>'primary_menu'));
        $home = html_writer::link($CFG->wwwroot, get_string('home'));
        $content .= html_writer::tag('li', $home);
        $content .= $this->custom_menu();


        $content .= html_writer::end_tag('ul');
        if (isloggedin()) {
            $content .= html_writer::start_tag('div',array('class'=>'search-wrapper'));
            $content .= html_writer::start_tag('form',
            array('action'=>$CFG->wwwroot . '/course/search.php',
                       'method'=>'get',
                       'class'=>'search'));
            $content .= html_writer::start_tag('div',array('id'=>'search-trigger'));
            $content .= get_string('search');
            $content .= html_writer::end_tag('div');
            $content .= html_writer::empty_tag('input',
            array('type'=>'text',
                     'placehodler'=>'search + enter',
                     'name'=>'search',
                     'id'=>'coursesearchbox',
                     'style'=>'display: none;' ));
            $content .= html_writer::end_tag('form');
            $content .= html_writer::end_tag('div');
        }
        $content .= html_writer::end_tag('nav');
        $content .= html_writer::end_tag('div');
        $content .= html_writer::start_tag('div',array('id'=>'logo'));
        $attr = array(
            'src' => $this->image_url('trans', 'theme'),
            'alt' => 'Moodle',
            'class' => 'logo',
        );

        $logotext = html_writer::tag('div', get_string('sitename', 'theme_leaf'), array('class' => 'sitename'));

        $logo = html_writer::empty_tag('img', $attr);
        $content .= html_writer::link($CFG->wwwroot, $logotext, array('class' => 'sitelink'));
        $content .= html_writer::tag('div', get_string('sitedescription', 'theme_leaf'), array('class' => 'sitedescription'));
        $content .= $this->leaf_get_social();
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
        return $content;
    }

    public function leaf_social() {
        global $USER;
        $mobilenav = html_writer::link('javascript:void(0)','<span></span>');

        $content = html_writer::start_tag('div',array('class'=>'preheader'));
        $content .= html_writer::tag('h5',$mobilenav,array('class'=>'mobile_nav'));
        $content .= html_writer::start_tag('nav',array('class'=>'socioicons clearfix'));
        $content .= $this->leaf_get_social();
        $content .= html_writer::end_tag('nav');
        $content .= html_writer::start_tag('div',array('class'=>'social'));
        $content .= html_writer::start_tag('div',array('class'=>'logininfowrap'));
        $content .= $this->user_picture($USER, array('size'=>35,'link'=>false));

        $content .= $this->login_info();
        $content .= html_writer::end_tag('div');

        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
        return $content;
    }

    public function leaf_get_social() {
        $content = '';
        $content .= html_writer::start_tag('ul',array('class'=>'social'));

        $settings = $this->page->theme->settings;

        if (!empty($settings->facebook) && $settings->facebook != '') {
            $facebook = html_writer::link($settings->facebook,'&nbsp;',array('data-placement'=>'bottom','class'=>'socicon small facebook','data-original-title'=>'Follow us on Facebook'));
            $content .= html_writer::tag('li',$facebook);
        }
        if (!empty($settings->twitter) && $settings->twitter != '') {
            $twitter = html_writer::link($settings->twitter,'&nbsp;',array('data-placement'=>'bottom','class'=>'socicon small twitterbird','data-original-title'=>'Follow us on Twitter'));
            $content .= html_writer::tag('li',$twitter);
        }
        if (!empty($settings->wordpress) && $settings->wordpress != '') {
            $wordpress = html_writer::link($settings->wordpress,'&nbsp;',array('data-placement'=>'bottom','class'=>'socicon small wordpress','data-original-title'=>'Visit our blog'));
            $content .= html_writer::tag('li',$wordpress);
        }
        $content .= html_writer::end_tag('ul');
        return $content;
    }

    public function leaf_twitter_feed() {
        $content = html_writer::start_tag('p',array('class'=>'helplink'));
        $content .= html_writer::link($this->page->theme->settings->twitter,'&nbsp;',
        array('class'=>'twitter-timeline',
                      'data-theme'=>'dark',
                      'data-tweet-limit'=>'2',
                      'data-chrome'=>'nofooter transparent',
                      'width'=>'260',
                      'height'=>'300',
                      'data-widget-id'=>'398152078985404416'));

        $content .= "<script>
               !function(d,s,id){
                 var js,
                 fjs=d.getElementsByTagName(s)[0],
                 p=/^http:/.test(d.location)?'http':'https';
                 if(!d.getElementById(id)){
                   js=d.createElement(s);
                   js.id=id;js.src=p+\"://platform.twitter.com/widgets.js\";
                   fjs.parentNode.insertBefore(js,fjs);
              }
            }
            (document,\"script\",\"twitter-wjs\");
            </script>";
        $content .= html_writer::end_tag('p');
        return $content;
    }

    public function leaf_footer() {
        global $DB;
        $content = html_writer::start_tag('div',array('class'=>'container'));
        $content .= html_writer::start_tag('div',array('class'=>'row-fluid'));
        //Row 1
        $content .= html_writer::start_tag('div',array('class'=>'span4'));
        $content .= html_writer::start_tag('section');
        $content .= html_writer::tag('h4',get_string('contact','theme_leaf'));
        if (!empty($this->page->theme->settings->helpusername)) {
            $content .= html_writer::start_tag('div',array('class' => 'helpuser'));
            if ($helpuser = $DB->get_record('user',array('username' => $this->page->theme->settings->helpusername))) {
                $content .= $this->user_picture($helpuser, array('size'=>50, 'link'=>true));
                $helpuserlink = html_writer::link(new moodle_url('/user/view.php', array('id' => $helpuser->id)), fullname($helpuser));
                $content .= html_writer::tag('span', $helpuserlink, array('class' => 'helpusername'));
                if (!empty($helpuser->description)) {
                    $content .= html_writer::tag('span', $helpuser->description, array('class' => 'helpuserdescription'));
                }
            }
            $content .= html_writer::end_tag('div');
        }
        $content .= '<br><br>';
        $content .= $this->page->theme->settings->helpinfo;
        $content .= html_writer::end_tag('section');
        $content .= html_writer::start_tag('section');
        $attr = array(
            'src' => $this->image_url('densemlogo', 'theme'),
            'alt' => 'Denver Seminary Logo',
            'class' => 'densemlogo',
        );

        $content .= html_writer::empty_tag('img', $attr);
        $content .= html_writer::end_tag('section');
        $content .= html_writer::end_tag('div');
        //Row 2
        $content .= html_writer::start_tag('div',array('class'=>'span4'));
        $content .= html_writer::start_tag('section');
        $content .= html_writer::tag('h4',get_string('twitter','theme_leaf'));
        $content .= $this->leaf_twitter_feed();
        $content .= html_writer::end_tag('section');
        $content .= html_writer::end_tag('div');
        //Row 3
        $content .= html_writer::start_tag('div',array('class'=>'span4'));
        if (isset($this->page->theme->settings->extra1) && isset($this->page->theme->settings->extra1text)) {
            $content .= html_writer::start_tag('section');

            if (strlen($this->page->theme->settings->extra1text) > 450) {
                $message = substr($this->page->theme->settings->extra1text ,0,450) . '..<br>';
                $message .= html_writer::link(new moodle_url('/theme/leaf/layout/index.php',
                array('page'=>'extra1text')),'Details →',array('class'=>'btn btn-primary'));
            } else {
                $message = $this->page->theme->settings->extra1text;
            }

            $content .= html_writer::tag('h4',$this->page->theme->settings->extra1);
            $content .= $message;
            $content .= html_writer::end_tag('section');
        }
        if (isset($this->page->theme->settings->extra1) && isset($this->page->theme->settings->extra1text)) {
            $content .= html_writer::start_tag('section');

            if (strlen($this->page->theme->settings->extra2text) > 450) {
                $message = substr($this->page->theme->settings->extra2text ,0,450) . '..<br>';
                $message .= html_writer::link(new moodle_url('/theme/leaf/layout/index.php',
                array('page'=>'extra2text')),'Details →',array('class'=>'btn btn-primary'));
            } else {
                $message = $this->page->theme->settings->extra2text;
            }


            $content .= html_writer::tag('h4',$this->page->theme->settings->extra2);
            $content .= $message;
            $content .= html_writer::end_tag('section');
        }
        $content .= html_writer::end_tag('div');

        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
        return $content;
    }
    
    /**
     * Renders tabtree
     *
     * @param tabtree $tabtree
     * @return string
     */
    protected function render_tabtree(tabtree $tabtree) {
        if (empty($tabtree->subtree)) {
            return '';
        }
        $firstrow = $secondrow = '';
        foreach ($tabtree->subtree as $tab) {
            $firstrow .= $this->render($tab);
            if (($tab->selected || $tab->activated) && !empty($tab->subtree) && $tab->subtree !== array()) {
                $secondrow = $this->tabtree($tab->subtree);
            }
        }
        return html_writer::tag('ul', $firstrow, array('class' => 'nav nav-tabs')) . $secondrow;
    }

    /**
     * Renders tabobject (part of tabtree)
     *
     * This function is called from {@link core_renderer::render_tabtree()}
     * and also it calls itself when printing the $tabobject subtree recursively.
     *
     * @param tabobject $tabobject
     * @return string HTML fragment
     */
    protected function render_tabobject(tabobject $tab) {
        if ($tab->selected or $tab->activated) {
            return html_writer::tag('li', html_writer::tag('a', $tab->text), array('class' => 'active'));
        } else if ($tab->inactive) {
            return html_writer::tag('li', html_writer::tag('a', $tab->text), array('class' => 'disabled'));
        } else {
            if (!($tab->link instanceof moodle_url)) {
                // backward compartibility when link was passed as quoted string
                $link = "<a href=\"$tab->link\" title=\"$tab->title\">$tab->text</a>";
            } else {
                $link = html_writer::link($tab->link, $tab->text, array('title' => $tab->title));
            }
            return html_writer::tag('li', $link);
        }
    }

    /*
     * This renders a notification message.
     * Uses bootstrap compatible html.
     */
    public function notification($message, $classes = 'notifyproblem') {
        $message = clean_text($message);
        $type = '';

        if ($classes == 'notifyproblem') {
            $type = 'alert alert-error';
        }
        if ($classes == 'notifysuccess') {
            $type = 'alert alert-success';
        }
        if ($classes == 'notifymessage') {
            $type = 'alert alert-info';
        }
        if ($classes == 'redirectmessage') {
            $type = 'alert alert-block alert-info';
        }
        return "<div class=\"$type\">$message</div>";
    }
    /**
     * Return the standard string that says whether you are logged in (and switched
     * roles/logged in as another user).
     * @param bool $withlinks if false, then don't include any links in the HTML produced.
     * If not set, the default is the nologinlinks option from the theme config.php file,
     * and if that is not set, then links are included.
     * @return string HTML fragment.
     */
    public function login_info($withlinks = null) {
        global $USER, $CFG, $DB, $SESSION;

        if (during_initial_install()) {
            return '';
        }

        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        $loginpage = ((string)$this->page->url === get_login_url());
        $course = $this->page->course;
        if (\core\session\manager::is_loggedinas()) {
            $realuser = \core\session\manager::get_realuser();
            $fullname = fullname($realuser, true);
            if ($withlinks) {
                $loginastitle = get_string('loginas');
                $realuserinfo = " [<a href=\"$CFG->wwwroot/course/loginas.php?id=$course->id&amp;sesskey=".sesskey()."\"";
                $realuserinfo .= "title =\"".$loginastitle."\">$fullname</a>] ";
            } else {
                $realuserinfo = " [$fullname] ";
            }
        } else {
            $realuserinfo = '';
        }

        $loginurl = get_login_url();

        if (empty($course->id)) {
            // $course->id is not defined during installation
            return '';
        } else if (isloggedin()) {
            $context = context_course::instance($course->id);

            $fullname = fullname($USER, true);
            // Since Moodle 2.0 this link always goes to the public profile page (not the course profile page)
            if ($withlinks) {
                $linktitle = get_string('viewprofile');
                $username = "<a href=\"$CFG->wwwroot/user/profile.php?id=$USER->id\" title=\"$linktitle\">$fullname</a>";
            } else {
                $username = $fullname;
            }
            if (is_mnet_remote_user($USER) and $idprovider = $DB->get_record('mnet_host', array('id'=>$USER->mnethostid))) {
                if ($withlinks) {
                    $username .= " from <a href=\"{$idprovider->wwwroot}\">{$idprovider->name}</a>";
                } else {
                    $username .= " from {$idprovider->name}";
                }
            }
            if (isguestuser()) {
                $loggedinas = $realuserinfo.get_string('loggedinasguest');
                if (!$loginpage && $withlinks) {
                    $loggedinas .= " (<a href=\"$loginurl\">".get_string('login').'</a>)';
                }
            } else if (is_role_switched($course->id)) { // Has switched roles
                $rolename = '';
                if ($role = $DB->get_record('role', array('id'=>$USER->access['rsw'][$context->path]))) {
                    $rolename = ': '.role_get_name($role, $context);
                }
                $loggedinas = get_string('loggedinas', 'moodle', $username).$rolename;
                if ($withlinks) {
                    $url = new moodle_url('/course/switchrole.php', array('id'=>$course->id,'sesskey'=>sesskey(), 'switchrole'=>0, 'returnurl'=>$this->page->url->out_as_local_url(false)));
                    $loggedinas .= '('.html_writer::tag('a', get_string('switchrolereturn'), array('href'=>$url)).')';
                }
            } else {
                $loggedinas = $realuserinfo.get_string('loggedinas', 'moodle', $username);
                if ($withlinks) {
                    $loggedinas .= " (<a href=\"$CFG->wwwroot/login/logout.php?sesskey=".sesskey()."\">".get_string('logout').'</a>)';
                }
            }
        } else {
            $loggedinas = get_string('loggedinnot', 'moodle');
            if (!$loginpage && $withlinks) {
                $loggedinas .= " (<a href=\"$loginurl\">".get_string('login').'</a>)';
            }
        }

        $loggedinas = '<div class="logininfo">'.$loggedinas.'</div>'.'<div class="mobileonly">
          <a href="$CFG->wwwroot/login/logout.php?sesskey="'.sesskey().'">'.get_string('logout').'</a></div>';

        if (isset($SESSION->justloggedin)) {
            unset($SESSION->justloggedin);
            if (!empty($CFG->displayloginfailures)) {
                if (!isguestuser()) {
                    if ($count = count_login_failures($CFG->displayloginfailures, $USER->username, $USER->lastlogin)) {
                        $loggedinas .= '&nbsp;<div class="loginfailures">';
                        if (empty($count->accounts)) {
                            $loggedinas .= get_string('failedloginattempts', '', $count);
                        } else {
                            $loggedinas .= get_string('failedloginattemptsall', '', $count);
                        }
                        if (file_exists("$CFG->dirroot/report/log/index.php") and has_capability('report/log:view', context_system::instance())) {
                            $loggedinas .= ' (<a href="'.$CFG->wwwroot.'/report/log/index.php'.
                                                 '?chooselog=1&amp;id=1&amp;modid=site_errors">'.get_string('logs').'</a>)';
                        }
                        $loggedinas .= '</div>';
                    }
                }
            }
        }
        return $loggedinas;
    }

    public function content_zoom() {
        if (!isloggedin() || isguestuser()) {
            return '';
        }
        $zoomin = html_writer::span(get_string('fullscreen', 'theme_leaf'), 'zoomin');
        $zoomout = html_writer::span(get_string('closefullscreen', 'theme_leaf'), 'zoomout');
        $content = html_writer::link('#',  $zoomin . $zoomout,
            array('class' => 'pull-right moodlezoom'));
        return $content;
    }
}