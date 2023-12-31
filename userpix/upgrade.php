<?php // $Id$
      // This script updates all users picturesi to remove black border.


    include('../config.php');
    include('../lib/gdlib.php');

    require_login();

    if (!isadmin()) {
        error("Currently only the administrator can access this page!");
    }

    
    if (!$users = get_records("user", "picture", "1", "lastaccess DESC", "id,firstname,lastname")) {
        error("no users!");
    }
    
    $title = get_string("users");
    
    print_header($title, $title, $title);
    
    foreach ($users as $user) {
        upgrade_profile_image($user->id);
        $fullname = fullname($user);
        echo "<a href=\"$CFG->wwwroot/user/view.php?id=$user->id&amp;course=1\"".
             "title=\"$fullname\">";
        if ($CFG->slasharguments) {        // Use this method if possible for better caching
            echo '<img src="'. $CFG->wwwroot .'/user/pix.php/'.$user->id.'/f1.jpg"'.
                 ' border="0" width="100" height="100" alt="'.$fullname.'" />';
        } else {
            echo '<img src="'. $CFG->wwwroot .'/user/pix.php?file=/'. $user->id .'/f1.jpg"'.
                 ' border="0" width="100" height="100" alt="'.$fullname.'" />';
        }
        echo "</a> \n";
    }
    
    print_footer();

?>
