<?php //$Id$

global $CFG, $USER;

require_once($CFG->dirroot .'/blog/lib.php');
require_once($CFG->libdir .'/pagelib.php');
require_once($CFG->dirroot .'/blog/blogpage.php');
require_once($CFG->libdir .'/blocklib.php');
require_once($CFG->dirroot .'/course/lib.php');

$blockaction = optional_param('blockaction','', PARAM_ALPHA);
$instanceid = optional_param('instanceid', 0, PARAM_INT);
$blockid = optional_param('blockid',    0, PARAM_INT);
$groupid = optional_param('groupid',    0, PARAM_INT);
$userid = optional_param('userid',     0, PARAM_INT);

if (!isset($courseid)) {
    $courseid = optional_param('courseid', SITEID, PARAM_INT);
}

if (!$site = get_site()) {
    redirect($CFG->wwwroot.'/index.php');
}

if ($courseid == 0 ) {
    $courseid = SITEID;
}

// now check that they are logged in and allowed into the course (if specified)
if ($courseid != SITEID) {
    if (! $course = get_record('course', 'id', $courseid)) {
        error('The course number was incorrect ('. $courseid .')');
    }
    require_login($course->id);
}

// Bounds for block widths within this page
define('BLOCK_L_MIN_WIDTH', 160);
define('BLOCK_L_MAX_WIDTH', 210);
define('BLOCK_R_MIN_WIDTH', 160);
define('BLOCK_R_MAX_WIDTH', 210);

//_____________ new page class code ________
$pagetype = PAGE_BLOG_VIEW;
$pageclass = 'page_blog';

// map our page identifier to the actual name
// of the class which will be handling its operations.
page_map_class($pagetype, $pageclass);    

// Now, create our page object.
if (!isset($USER->id)) {
    $PAGE = page_create_object($pagetype);
}
else {
    $PAGE = page_create_object($pagetype, $USER->id);
}
$PAGE->courseid = $courseid;
$PAGE->init_full(); //init the BlogInfo object and the courserecord object

if (isset($tagid)) {
    $taginstance = get_record('tags', 'id', $tagid);
} else {
    $tagid = '';
}
if (!isset($filtertype)) {
    $filtertype = 'user';
    $filterselect = $USER->id;
}

/// navigations
/// site blogs - sitefullname -> blogs -> (?tag)
/// course blogs - sitefullname -> course fullname ->blogs ->(?tag)
/// group blogs - sitefullname -> course fullname ->group ->(?tag)
/// user blogs - sitefullname -> (?coursefullname) -> participants -> blogs -> (?tag)
$blogstring = get_string('blogs','blog');
$tagstring = get_string('tag');

switch ($filtertype) {
    case 'site':
        if ($tagid) {
            print_header("$site->shortname: $blogstring", "$site->fullname",
                        '<a href="index.php?filtertype=site">'. "$blogstring</a> -> $tagstring: $taginstance->text",'','',true,$PAGE->get_extra_header_string());
        } else {
            print_header("$site->shortname: $blogstring", "$site->fullname",
                        "$blogstring",'','',true,$PAGE->get_extra_header_string());
        }
    break;

    case 'course':
        if ($tagid) {
            print_header("$course->shortname: $blogstring", "$course->fullname",
                        '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$filterselect.'">'.$course->shortname.'</a> ->
                        <a href="index.php?filtertype=course&amp;filterselect='.$filterselect.'">'. "$blogstring</a> -> $tagstring: $taginstance->text",'','',true,$PAGE->get_extra_header_string());
        } else {
            print_header("$site->shortname: $blogstring", "$site->fullname",
                        '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$filterselect.'">'.$course->shortname."</a> ->
                        $blogstring",'','',true,$PAGE->get_extra_header_string());
        }
    break;

    case 'group':

        $thisgroup = get_record('groups', 'id', $filterselect);

        if ($tagid) {
            print_header("$course->shortname: $blogstring", "$course->fullname",
                        '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">'.$course->shortname.'</a> ->
                        <a href="'.$CFG->wwwroot.'/user/index.php?id='.$course->id.'&amp;group='.$filterselect.'">'.$thisgroup->name.'</a> ->
                        <a href="index.php?filtertype=group&amp;filterselect='.$filterselect.'">'. "$blogstring</a> -> $tagstring: $taginstance->text",'','',true,$PAGE->get_extra_header_string());
        } else {
            print_header("$course->shortname: $blogstring", "$course->fullname",
                        '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">'.$course->shortname.'</a> ->
                        <a href="'.$CFG->wwwroot.'/user/index.php?id='.$course->id.'&amp;group='.$filterselect.'">'.$thisgroup->name."</a> ->
                        $blogstring",'','',true,$PAGE->get_extra_header_string());

        }
    
    break;

    case 'user':
        $user = get_record('user', 'id', $filterselect);
        $participants = get_string('participants');

        if (isset($course->id) && $course->id && $course->id != SITEID) {
            if ($tagid) {
                print_header("$course->shortname: $blogstring", "$course->fullname",
                        '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">'.$course->shortname.'</a> ->
                        <a href="'.$CFG->wwwroot.'/user/index.php?id='.$course->id.'">'.$participants.'</a> ->
                        <a href="'.$CFG->wwwroot.'/user/view.php?id='.$filterselect.'&amp;course='.$course->id.'">'.fullname($user).'</a> ->
                        <a href="index.php?courseid='.optional_param('courseid', 0, PARAM_INT).'&amp;filtertype=user&amp;filterselect='.$filterselect.'">'. "$blogstring</a> -> $tagstring: $taginstance->text",'','',true,$PAGE->get_extra_header_string());

            } else {
                print_header("$course->shortname: $blogstring", "$course->fullname",
                        '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">'.$course->shortname.'</a> ->
                        <a href="'.$CFG->wwwroot.'/user/index.php?id='.$course->id.'">'.$participants.'</a> ->
                        <a href="'.$CFG->wwwroot.'/user/view.php?id='.$filterselect.'&amp;course='.$course->id.'">'.fullname($user).'</a> ->
                        '.$blogstring,'','',true,$PAGE->get_extra_header_string());

            }

        }
        //in top view
        else {

            if ($tagid) {
                print_header("$site->shortname: $blogstring", "$site->fullname",
                        '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$filterselect.'">'.fullname($user).'</a> ->
                        <a href="index.php?filtertype=user&amp;filterselect='.$filterselect.'">'. "$blogstring</a> -> $tagstring: $taginstance->text",'','',true,$PAGE->get_extra_header_string());

            } else {
                print_header("$site->shortname: $blogstring", "$site->fullname",
                        '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$filterselect.'">'.fullname($user).'</a> ->
                        '.$blogstring,'','',true,$PAGE->get_extra_header_string());

            }
           
        }
    break;

    default:    //user click on add from block
        print_header("$site->shortname: $blogstring", "$site->fullname",
                        '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$filterselect.'&amp;course='.$course->id.'">'.fullname($user).'</a> ->
                        '.$blogstring,'','',true,$PAGE->get_extra_header_string());
    break;
}

$editing = false;
if ($PAGE->user_allowed_editing()) {
    $editing = $PAGE->user_is_editing();
}

// Calculate the preferred width for left, right and center (both center positions will use the same)
$preferred_width_left = optional_param('preferred_width_left',  blocks_preferred_width($pageblocks[BLOCK_POS_LEFT]), PARAM_INT);
$preferred_width_right = optional_param('preferred_width_right', blocks_preferred_width($pageblocks[BLOCK_POS_RIGHT]), PARAM_INT);
$preferred_width_left = min($preferred_width_left, BLOCK_L_MAX_WIDTH);
$preferred_width_left = max($preferred_width_left, BLOCK_L_MIN_WIDTH);
$preferred_width_right = min($preferred_width_right, BLOCK_R_MAX_WIDTH);
$preferred_width_right = max($preferred_width_right, BLOCK_R_MIN_WIDTH);

// Display the blocks and allow blocklib to handle any block action requested
$pageblocks = blocks_get_by_page($PAGE);

if ($editing) {
    if (!empty($blockaction) && confirm_sesskey()) {
        if (!empty($blockid)) {
            blocks_execute_action($PAGE, $pageblocks, strtolower($blockaction), intval($blockid));
        } else if (!empty($instanceid)) {
            $instance = blocks_find_instance($instanceid, $pageblocks);
            blocks_execute_action($PAGE, $pageblocks, strtolower($blockaction), $instance);
        }
        // This re-query could be eliminated by judicious programming in blocks_execute_action(),
        // but I'm not sure if it's worth the complexity increase...
        $pageblocks = blocks_get_by_page($PAGE);
    }
    $missingblocks = blocks_get_missing($PAGE, $pageblocks);
}

/// Layout the whole page as three big columns.
print '<table border="0" cellpadding="3" cellspacing="0" width="100%">' . "\n";
print '<tr valign="top">' . "\n";

/// The left column ...
if (blocks_have_content($pageblocks, BLOCK_POS_LEFT) || $editing) {
    print '<td style="vertical-align: top; width: '. $preferred_width_left .'px;">' . "\n";
    print '<!-- Begin left side blocks -->' . "\n";
    blocks_print_group($PAGE, $pageblocks, BLOCK_POS_LEFT);
    print '<!-- End left side blocks -->' . "\n";
    print '</td>' . "\n";
}

/// Start main column
print '<!-- Begin page content -->' . "\n";
print '<td width="*">';
?>
<table width="100%">
<tr>
<td height="100%" valign="top">