<?php // $Id: uploadgroups.php, 2005/10/31 19:09:31 

/// Bulk group creation registration script from a comma separated file

	require_once('../../../config.php');
    require_once('../../lib.php');
	
	$mycourseid = required_param('id', PARAM_INT);    // Course id
    
	if (! $course = get_record('course', 'id', $mycourseid) ) {
        error("That's an invalid course id");
    }
	
	require_login($course->id);
	
    if (!isadmin() && !isteacheredit($course->id)) {
        error("You must be an administrator to edit users this way.");
    }

    //if (!confirm_sesskey()) {
    //    error(get_string('confirmsesskeybad', 'error'));
    //}

  	$strimportgroups = get_string("importgroups");

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

    print_header("$course->shortname: $strimportgroups", "$course->fullname", 
                 "<a href=\"$CFG->wwwroot/course/view.php?id=$course->id\">$course->shortname</a> ".
                 "-> <a href=\"$CFG->wwwroot/course/import.php?id=$course->id\">".get_string('import')."</a> ".
				 "-> $strimportgroups");

/// If a file has been uploaded, then process it

    require_once($CFG->dirroot.'/lib/uploadlib.php');
    $um = new upload_manager('userfile',false,false,null,false,0);
    if ($um->preprocess_files()) {
        $filename = $um->files['userfile']['tmp_name'];

        //Fix mac/dos newlines
        $text = my_file_get_contents($filename);
        $text = preg_replace('!\r\n?!',"\n",$text);
        $fp = fopen($filename, "w");
        fwrite($fp,$text);
        fclose($fp);

        $fp = fopen($filename, "r");

        // make arrays of valid fields for error checking
        $required = array("groupname" => 1, );
        $optionalDefaults = array("lang" => 1, );
        $optional = array("coursename" => 1, 
						  "idnumber" =>1,
						  "description" => 1,
						  "password" => 1,
						  "theme" => 1,
			     		  "picture" => 1, 
                          "hidepicture" => 1, );

        // --- get header (field names) ---
        $header = split($csv_delimiter, fgets($fp,1024));
        // check for valid field names
        foreach ($header as $i => $h) {
            $h = trim($h); $header[$i] = $h; // remove whitespace
            if (!($required[$h] or $optionalDefaults[$h] or $optional[$h])) {
                error(get_string('invalidfieldname', 'error', $h), 'index.php?id='.$id.'&amp;sesskey='.$USER->sesskey);
            }
            if ($required[$h]) {
                $required[$h] = 2;
            }
        }
        // check for required fields
        foreach ($required as $key => $value) {
            if ($value < 2) {
                error(get_string('fieldrequired', 'error', $key), 'uploaduser.php?id='.$id.'&amp;sesskey='.$USER->sesskey);
            }
        }
        $linenum = 2; // since header is line 1

        while (!feof ($fp)) {
			
			$newgroup = new object();//to make Martin happy
            foreach ($optionalDefaults as $key => $value) {
                $newgroup->$key = current_language(); //defaults to current language
            }
           //Note: commas within a field should be encoded as &#44 (for comma separated csv files)
           //Note: semicolon within a field should be encoded as &#59 (for semicolon separated csv files)
            $line = split($csv_delimiter, fgets($fp,1024));
            foreach ($line as $key => $value) {
                //decode encoded commas
                $record[$header[$key]] = preg_replace($csv_encode,$csv_delimiter2,trim($value));
            }
            if ($record[$header[0]]) {
                // add a new group to the database

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
                    else if ($name == "password") {
                        $newgroup->password = md5($value);
                    }
                    else if ($name == "groupname") {
                        $newgroup->name = addslashes($value);
                    }
                    // normal entry
                    else {
                        $newgroup->{$name} = addslashes($value);
                    }
                }
				///Find the courseid of the course with the given shortname
				
				//if idnumber is set, we use that.
				//unset invalid courseid
				if ($newgroup->idnumber){
					if (!$mycourse = get_record('course', 'idnumber',$newgroup->idnumber)){
						notify(get_string('unknowncourseidnumber', 'error', $newgroup->idnumber));
						unset($newgroup->courseid);//unset so 0 doesnt' get written to database
					}
					$newgroup->courseid = $mycourse->id;		
				}
				//else use course short name to look up
				//unset invalid coursename (if no id)
							
				else if ($newgroup->coursename){
					if (!$mycourse = get_record('course', 'shortname',$newgroup->coursename)){
						notify(get_string('unknowncourse', 'error', $newgroup->coursename));
						unset($newgroup->courseid);//unset so 0 doesnt' get written to database
					}
					$newgroup->courseid = $mycourse->id;	
				}
				//else juse use current id
				else{
					$newgroup->courseid = $mycourseid;
				}
				
				//if courseid is set
				if (isset($newgroup->courseid)){
															
               		$newgroup->timecreated = time();
                	$linenum++;
                	$groupname = $newgroup->name;
				
					///Teachers can not upload groups in courses they are not teaching...
					if (!isadmin() and !isteacheredit($newgroup->courseid)){
						notify("$newgroup->name ".get_string('notaddedto').$newgroup->coursename.get_string('notinyourcapacity'));
					}
					
					else {
						if (get_record("groups","name",$groupname,"courseid",$newgroup->courseid) || !($newgroup->id = insert_record("groups", $newgroup))) {
	
							//Record not added - probably because group is already registered
							//In this case, output groupname from previous registration
							if ($group = get_record("groups","name",$groupname)) {
								notify("$newgroup->name ".get_string('groupexistforcourse', 'error', $groupname));
							} else {
								notify(get_string('groupnotaddederror', 'error', $groupname));
							} 
						}  
						else {
							notify(get_string('group')." $newgroup->name ".get_string('addedsuccessfully'));
						}
					}
				} //close courseid validity check
                unset ($newgroup);
            }//close if ($record[$header[0]])
        }//close while($fp)
        fclose($fp);

        echo '<hr />';
    }

/// Print the form
	require('mod.php');

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

