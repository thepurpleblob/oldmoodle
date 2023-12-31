<?php  // $Id$
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set

    if (!isset($filtertype)) {
        $filtertype = '';
    }
    if (!isset($filterselect)) {
        $filterselect = '';
    }

    //make sure everything is cleaned properly
    $filtertype   = clean_param($filtertype, PARAM_ALPHA);
    $filterselect = clean_param($filterselect, PARAM_INT);

    if (empty($currenttab) or empty($user) or empty($course)) {
        //error('You cannot call this script in that way');
    }

    if (($filtertype == 'site' && $filterselect) || ($filtertype=='user' && $filterselect)) {
        $user = get_record('user','id',$filterselect);
    }

    $inactive = NULL;
    $activetwo = NULL;
    $toprow = array();

    /**************************************
     * Site Level participation or Blogs  *
     **************************************/
    if ($filtertype == 'site') {

        $site = get_site();
        print_heading($site->fullname);
        
        if ($CFG->bloglevel >= 4) {
            if (isteacher(SITEID) || 
                ($CFG->showsiteparticipants == 1 && isteacherinanycourse()) || 
                ($CFG->showsiteparticipantslist == 2)) {
                $toprow[] = new tabobject('participants', $CFG->wwwroot.'/user/index.php?id='.SITEID,
                    get_string('participants'));
            }

            $toprow[] = new tabobject('blogs', $CFG->wwwroot.'/blog/index.php?filtertype=site&amp;',
                get_string('blogs','blog'));
        }

    /**************************************
     * Course Level participation or Blogs  *
     **************************************/
    } else if ($filtertype == 'course' && $filterselect) {

        $course = get_record('course','id',$filterselect);
        print_heading($course->fullname);

        if ($CFG->bloglevel >= 3) {

            $toprow[] = new tabobject('participants', $CFG->wwwroot.'/user/index.php?id='.$filterselect.'&amp;group=0',
                get_string('participants'));    //the groupid hack is necessary, otherwise the group in the session willbe used
        
            $toprow[] = new tabobject('blogs', $CFG->wwwroot.'/blog/index.php?filtertype=course&amp;filterselect='.$filterselect, get_string('blogs','blog'));
        }

    /**************************************
     * Group Level participation or Blogs  *
     **************************************/
    } else if ($filtertype == 'group' && $filterselect) {

        $group = get_record('groups','id',$filterselect);
        print_heading($group->name);

        if ($CFG->bloglevel >= 2) {

            $toprow[] = new tabobject('participants', $CFG->wwwroot.'/user/index.php?id='.$course->id.'&amp;group='.$filterselect,
                get_string('participants'));

        
            $toprow[] = new tabobject('blogs', $CFG->wwwroot.'/blog/index.php?filtertype=group&amp;filterselect='.$filterselect, get_string('blogs','blog'));
        }

    /**************************************
     * User Level participation or Blogs  *
     **************************************/
    } else {
        if (isset($userid)) {
            $user = get_record('user','id', $userid);
        }
        print_heading(fullname($user, isteacher($course->id)));

        $toprow[] = new tabobject('profile', $CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$course->id, get_string('profile'));


    /// Can only edit profile if it belongs to user or current user is admin and not editing primary admin

        if (($mainadmin = get_admin()) === false) {
            $mainadmin->id = 0; /// Weird - no primary admin!
        }
        if ((!empty($USER->id) and ($USER->id == $user->id) and !isguest()) or
            (isadmin() and ($user->id != $mainadmin->id)) ) {

            if(empty($CFG->loginhttps)) {
                $wwwroot = $CFG->wwwroot;
            } else {
                $wwwroot = str_replace('http:','https:',$CFG->wwwroot);
            }

            $toprow[] = new tabobject('editprofile', $wwwroot.'/user/edit.php?id='.$user->id.'&amp;course='.$course->id, get_string('editmyprofile'));
        }


    /// Everyone can see posts for this user

        $toprow[] = new tabobject('forumposts', $CFG->wwwroot.'/mod/forum/user.php?id='.$user->id.'&amp;course='.$course->id,
                    get_string('forumposts', 'forum'));

        if (in_array($currenttab, array('posts', 'discussions'))) {
            $inactive = array('forumposts');
            $activetwo = array('forumposts');

            $secondrow = array();
            $secondrow[] = new tabobject('posts', $CFG->wwwroot.'/mod/forum/user.php?course='.$course->id.
                                      '&amp;id='.$user->id.'&amp;mode=posts', get_string('posts', 'forum'));
            $secondrow[] = new tabobject('discussions', $CFG->wwwroot.'/mod/forum/user.php?course='.$course->id.
                                      '&amp;id='.$user->id.'&amp;mode=discussions', get_string('discussions', 'forum'));
        }


    /// Blog entry, everyone can view
        if ($CFG->bloglevel > 0) { // only if blog is enabled. Permission check kicks in when display list
            $toprow[] = new tabobject('blogs', $CFG->wwwroot.'/blog/index.php?userid='.$user->id.'&amp;courseid='.$course->id, get_string('blogs', 'blog'));
        }
        

    /// Current user must be teacher of the course or the course allows user to view their reports
        if (isteacher($course->id) or ($course->showreports and $USER->id == $user->id)) {

            $toprow[] = new tabobject('reports', $CFG->wwwroot.'/course/user.php?id='.$course->id.
                                      '&amp;user='.$user->id.'&amp;mode=outline', get_string('activityreports'));

            if (in_array($currenttab, array('outline', 'complete', 'todaylogs', 'alllogs', 'stats'))) {
                $inactive = array('reports');
                $activetwo = array('reports');

                $secondrow = array();
                $secondrow[] = new tabobject('outline', $CFG->wwwroot.'/course/user.php?id='.$course->id.
                                          '&amp;user='.$user->id.'&amp;mode=outline', get_string('outlinereport'));
                $secondrow[] = new tabobject('complete', $CFG->wwwroot.'/course/user.php?id='.$course->id.
                                          '&amp;user='.$user->id.'&amp;mode=complete', get_string('completereport'));
                $secondrow[] = new tabobject('todaylogs', $CFG->wwwroot.'/course/user.php?id='.$course->id.
                                          '&amp;user='.$user->id.'&amp;mode=todaylogs', get_string('todaylogs'));
                $secondrow[] = new tabobject('alllogs', $CFG->wwwroot.'/course/user.php?id='.$course->id.
                                          '&amp;user='.$user->id.'&amp;mode=alllogs', get_string('alllogs'));
                if (!empty($CFG->enablestats)) {
                    $secondrow[] = new tabobject('stats',$CFG->wwwroot.'/course/user.php?id='.$course->id.
                                                 '&amp;user='.$user->id.'&amp;mode=stats',get_string('stats'));
                }
            }

        }

    }    //close last bracket (individual tags)

/// Add second row to display if there is one

    if (!empty($secondrow)) {
        $tabs = array($toprow, $secondrow);
    } else {
        $tabs = array($toprow);
    }

/// Print out the tabs and continue!

    print_tabs($tabs, $currenttab, $inactive, $activetwo);

?>
