<?php // $Id$

/// Bulk user registration script from a comma separated file
/// Returns list of users with their user ids

require_once('../config.php');
require_once($CFG->libdir.'/uploadlib.php');

$createpassword = optional_param('createpassword', 0, PARAM_BOOL);
$updateaccounts = optional_param('updateaccounts', 0, PARAM_BOOL);
$allowrenames   = optional_param('allowrenames', 0, PARAM_BOOL);

require_login();

if (!isadmin()) {
    error("You must be an administrator to edit users this way.");
}

if (! $site = get_site()) {
    error("Could not find site-level course");
}

if (!$adminuser = get_admin()) {
    error("Could not find site admin");
}

$streditmyprofile = get_string("editmyprofile");
$stradministration = get_string("administration");
$strfile = get_string("file");
$struser = get_string("user");
$strusers = get_string("users");
$strusersnew = get_string("usersnew");
$strusersupdated = get_string("usersupdated");
$struploadusers = get_string("uploadusers");
$straddnewuser = get_string("importuser");

$csv_encode = '/\&\#44/';
if (isset($CFG->CSV_DELIMITER)) {
    $csv_delimiter = '\\' . $CFG->CSV_DELIMITER;
    $csv_delimiter2 = $CFG->CSV_DELIMITER;

    if (isset($CFG->CSV_ENCODE)) {
        $csv_encode = '/\&\#' . $CFG->CSV_ENCODE . '/';
    }
} else {
    $csv_delimiter = "\,";
    $csv_delimiter2 = ",";
}

/// Print the header

print_header("$site->shortname: $struploadusers", $site->fullname,
        "<a href=\"index.php\">$stradministration</a> ->
        <a href=\"users.php\">$strusers</a> -> $struploadusers");


/// If a file has been uploaded, then process it


$um = new upload_manager('userfile',false,false,null,false,0);

if ($um->preprocess_files() && confirm_sesskey()) {
    $filename = $um->files['userfile']['tmp_name'];

    // Large files are likely to take their time and memory. Let PHP know
    // that we'll take longer, and that the process should be recycled soon
    // to free up memory.
    @set_time_limit(0);
    @raise_memory_limit("128M");
    if (function_exists('apache_child_terminate')) {
        @apache_child_terminate();
    }

    //Fix mac/dos newlines
    $text = my_file_get_contents($filename);
    $text = preg_replace('!\r\n?!',"\n",$text);
    $fp = fopen($filename, "w");
    fwrite($fp,$text);
    fclose($fp);

    $fp = fopen($filename, "r");

    // make arrays of valid fields for error checking
    $required = array("username" => 1,
            "password" => !$createpassword,
            "firstname" => 1,
            "lastname" => 1,
            "email" => 1);
    $optionalDefaults = array("institution" => 1,
            "department" => 1,
            "city" => 1,
            "country" => 1,
            "lang" => 1,
            "auth" => 1,
            "timezone" => 1);
    $optional = array("idnumber" => 1,
            "icq" => 1,
            "phone1" => 1,
            "phone2" => 1,
            "address" => 1,
            "url" => 1,
            "description" => 1,
            "mailformat" => 1,
            "maildisplay" => 1,
            "htmleditor" => 1,
            "autosubscribe" => 1,
            "idnumber" => 1,
            "icq" => 1,
            "course1" => 1,
            "course2" => 1,
            "course3" => 1,
            "course4" => 1,
            "course5" => 1,
            "group1" => 1,
            "group2" => 1,
            "group3" => 1,
            "group4" => 1,
            "group5" => 1,
            "type1" => 1,
            "type2" => 1,
            "type3" => 1,
            "type4" => 1,
            "type5" => 1,
            "password" => $createpassword,
            "oldusername" => $allowrenames);

            // --- get header (field names) ---
            $header = split($csv_delimiter, fgets($fp,1024));
            // check for valid field names
            foreach ($header as $i => $h) {
                $h = trim($h); $header[$i] = $h; // remove whitespace
                if (!($required[$h] or $optionalDefaults[$h] or $optional[$h])) {
                    error(get_string('invalidfieldname', 'error', $h), 'uploaduser.php?sesskey='.$USER->sesskey);
                }
                if ($required[$h]) {
                    $required[$h] = 0;
                }
            }
            // check for required fields
            foreach ($required as $key => $value) {
                if ($value) { //required field missing
                    error(get_string('fieldrequired', 'error', $key), 'uploaduser.php?sesskey='.$USER->sesskey);
                }
            }
            $linenum = 2; // since header is line 1

            $usersnew     = 0;
            $usersupdated = 0;
            $userserrors  = 0;
            $renames      = 0;
            $renameerrors = 0;

            // Will use this course array a lot
            // so fetch it early and keep it in memory
            $courses = get_courses('all','c.sortorder','c.id,c.shortname,c.fullname,c.sortorder,c.teacher');

            while (!feof ($fp)) {
                foreach ($optionalDefaults as $key => $value) {
                    $user->$key = addslashes($adminuser->$key);
                }
                //Note: commas within a field should be encoded as &#44 (for comma separated csv files)
                //Note: semicolon within a field should be encoded as &#59 (for semicolon separated csv files)
                $line = split($csv_delimiter, fgets($fp,1024));
                foreach ($line as $key => $value) {
                    //decode encoded commas
                    $record[$header[$key]] = preg_replace($csv_encode,$csv_delimiter2,trim($value));
                }
                if ($record[$header[0]]) {
                    // add a new user to the database

                    // add fields to object $user
                    foreach ($record as $name => $value) {
                        // check for required values
                        if ($required[$name] and !$value) {
                            error(get_string('missingfield', 'error', $name). " ".
                                    get_string('erroronline', 'error', $linenum) .". ".
                                    get_string('processingstops', 'error'),
                                    'uploaduser.php?sesskey='.$USER->sesskey);
                        }
                        // password needs to be encrypted
                        else if ($name == "password" && !empty($value)) {
                            $user->password = hash_internal_user_password($value);
                        }
                        else if ($name == "username") {
                            $user->username = addslashes(moodle_strtolower($value));
                        }
                        // normal entry
                        else {
                            $user->{$name} = addslashes($value);
                        }
                    }
                    $user->confirmed = 1;
                    $user->timemodified = time();
                    $linenum++;
                    $username = $user->username;
                    $addcourse[0] = $user->course1;
                    $addcourse[1] = $user->course2;
                    $addcourse[2] = $user->course3;
                    $addcourse[3] = $user->course4;
                    $addcourse[4] = $user->course5;
                    $addgroup[0] = $user->group1;
                    $addgroup[1] = $user->group2;
                    $addgroup[2] = $user->group3;
                    $addgroup[3] = $user->group4;
                    $addgroup[4] = $user->group5;
                    $addtype[0] = $user->type1;
                    $addtype[1] = $user->type2;
                    $addtype[2] = $user->type3;
                    $addtype[3] = $user->type4;
                    $addtype[4] = $user->type5;

                    for ($i=0; $i<5; $i++) {
                        $course[$i]=NULL;
                    }
                    foreach ($courses as $eachcourse) {
                        for ($i=0; $i<5; $i++) {
                            if ($eachcourse->shortname == $addcourse[$i]) {
                                $course[$i] = $eachcourse;
                            }
                        }
                    }

                    if ($user->username === 'changeme') {
                        notify(get_string('invaliduserchangeme', 'admin'));
                        $userserrors++;
                        continue;
                    }

                    // before insert/update, check whether we should be updating
                    // an old record instead
                    if ($allowrenames && !empty($user->oldusername) ) {
                        $user->oldusername = moodle_strtolower($user->oldusername);
                        if ($olduser = get_record('user','username',$user->oldusername)) {
                            if (set_field('user', 'username', $user->username, 'username', $user->oldusername)) {
                                notify(get_string('userrenamed', 'admin') . " : $user->oldusername $user->username");
                                $renames++;
                            } else {
                                notify(get_string('usernotrenamedexists', 'error') . " : $user->oldusername $user->username");
                                $renameerrors++;
                                continue;
                            }
                        } else {
                            notify(get_string('usernotrenamedmissing', 'error') . " : $user->oldusername $user->username");
                            $renameerrors++;
                            continue;
                        }
                    }

                    if ($olduser = get_record("user","username",$username)) {
                        if ($updateaccounts) {
                            // Record is being updated
                            $user->id = $olduser->id;
                            if (update_record('user', $user)) {
                                notify("$user->id , $user->username ".get_string('useraccountupdated', 'admin'));
                                $usersupdated++;
                            } else {
                                notify(get_string('usernotupdatederror', 'error', $username));
                                $userserrors++;
                                continue;
                            }
                        } else {
                            //Record not added - user is already registered
                            //In this case, output userid from previous registration
                            //This can be used to obtain a list of userids for existing users
                            notify("$user->id ".get_string('usernotaddedregistered', 'error', $username));
                            $userserrors++;
                        }

                    } else { // new user
                        if ($user->id = insert_record("user", $user)) {
                            notify("$struser: $user->id = $user->username");
                            $usersnew++;
                            if (empty($user->password) && $createpassword) {
                                // passwords will be created and sent out on cron
                                insert_record('user_preferences', array( userid => $user->id,
                                            name   => 'create_password',
                                            value  => 1));
                                insert_record('user_preferences', array( userid => $user->id,
                                            name   => 'auth_forcepasswordchange',
                                            value  => 1));
                            }
                        } else {
                            // Record not added -- possibly some other error
                            notify(get_string('usernotaddederror', 'error', $username));
                            $userserrors++;
                            continue;
                        }
                    }
                    for ($i=0; $i<5; $i++) {
                        if ($addcourse[$i] && !$course[$i]) {
                            notify(get_string('unknowncourse', 'error', $addcourse[$i]));
                        }
                    }
                    for ($i=0; $i<5; $i++) {
                        $groupid[$i] = 0;
                        if ($addgroup[$i]) {
                            if (!$course[$i]) {
                                notify(get_string('coursegroupunknown','error',$addgroup[$i]));
                            } else {
                                if ($group = get_record("groups","courseid",$course[$i]->id,"name",$addgroup[$i])) {
                                    $groupid[$i] = $group->id;
                                } else {
                                    notify(get_string('groupunknown','error',$addgroup[$i]));
                                }
                            }
                        }
                    }
                    for ($i=0; $i<5; $i++) {   /// Enrol into courses if necessary
                        if ($course[$i]) {
                            if (isset($addtype[$i])) {
                                switch ($addtype[$i]) {
                                    case 2:   // teacher
                                        $ret = add_teacher($user->id, $course[$i]->id, 1);
                                        break;

                                    case 3:   // non-editing teacher
                                        $ret = add_teacher($user->id, $course[$i]->id, 0);
                                        break;

                                    default:  // student
                                        $ret = enrol_student($user->id, $course[$i]->id);
                                        break;
                                }
                            } else {
                                $ret = enrol_student($user->id, $course[$i]->id);
                            }
                            if ($ret) {   // OK
                                notify('-->'. get_string('enrolledincourse', '', $addcourse[$i]));
                            } else {
                                notify('-->'.get_string('enrolledincoursenot', '', $addcourse[$i]));
                            }
                        }
                    }
                    for ($i=0; $i<5; $i++) {   // Add user to groups if necessary
                        if ($course[$i] && $groupid[$i]) {
                            if (record_exists('user_students','userid',$user->id,'course',$course[$i]->id) ||
                                    record_exists('user_teachers','userid',$user->id,'course',$course[$i]->id)) {
                                if (add_user_to_group($groupid[$i], $user->id)) {
                                    notify('-->' . get_string('addedtogroup','',$addgroup[$i]));
                                } else {
                                    notify('-->' . get_string('addedtogroupnot','',$addgroup[$i]));
                                }
                            } else {
                                notify('-->' . get_string('addedtogroupnotenrolled','',$addgroup[$i]));
                            }
                        }
                    }

                    unset ($user);
                }
            }
            fclose($fp);
            notify("$strusersnew: $usersnew");
            notify(get_string('usersupdated', 'admin') . ": $usersupdated");
            notify(get_string('errors', 'admin') . ": $userserrors");
            if ($allowrenames) {
                notify(get_string('usersrenamed', 'admin') . ": $renames");
                notify(get_string('renameerrors', 'admin') . ": $renameerrors");
            }
            echo '<hr />';
}

/// Print the form
print_heading_with_help($struploadusers, 'uploadusers');

$noyesoptions = array( get_string('no'), get_string('yes') );

$maxuploadsize = get_max_upload_file_size();
echo '<center>';
echo '<form method="post" enctype="multipart/form-data" action="uploaduser.php">'.
$strfile.'&nbsp;<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
'<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">'.
'<input type="file" name="userfile" size="30">';
print_heading(get_string('settings'));
echo '<table>';
echo '<tr><td>' . get_string('passwordhandling', 'auth') . '</td><td>';
$passwordopts = array( 0 => get_string('infilefield', 'auth'),
        1 => get_string('createpasswordifneeded', 'auth'),
        );
choose_from_menu($passwordopts, 'createpassword', $createpassword);
echo '</td></tr>';

echo '<tr><td>' . get_string('updateaccounts', 'admin') . '</td><td>';
choose_from_menu($noyesoptions, 'updateaccounts', $updateaccounts);
echo '</td></tr>';

echo '<tr><td>' . get_string('allowrenames', 'admin') . '</td><td>';
choose_from_menu($noyesoptions, 'allowrenames', $allowrenames);
echo '</td></tr>';
echo '</table><br />';
echo '<input type="submit" value="'.$struploadusers.'">';
echo '</form><br />';
echo '</center>';

print_footer($course);



function my_file_get_contents($filename, $use_include_path = 0) {
    /// Returns the file as one big long string

    $data = "";
    $file = @fopen($filename, "rb", $use_include_path);
    if ($file) {
        while (!feof($file)) {
            $data .= fread($file, 1024);
        }
        fclose($file);
    }
    return $data;
}

?>

