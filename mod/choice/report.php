<?php  // $Id$

    require_once("../../config.php");
    require_once("lib.php");

    $id       = required_param('id', PARAM_INT);   //moduleid
    $format   = optional_param('format', CHOICE_PUBLISH_NAMES, PARAM_INT);
    $download = optional_param('download', '', PARAM_ALPHA);
    $action   = optional_param('action', '', PARAM_ALPHA);

    if (! $cm = get_coursemodule_from_id('choice', $id)) {
        error("Course Module ID was incorrect");
    }

    if (! $course = get_record("course", "id", $cm->course)) {
        error("Course module is misconfigured");
    }

    require_login($course->id, false, $cm);

    if (!isteacher($course->id)) {
        error("Only teachers can look at this page");
    }

    if (!$choice = choice_get_choice($cm->instance)) {
        error("Course module is incorrect");
    }

    $strchoice = get_string("modulename", "choice");
    $strchoices = get_string("modulenameplural", "choice");
    $strresponses = get_string("responses", "choice");

    add_to_log($course->id, "choice", "report", "report.php?id=$cm->id", "$choice->id",$cm->id);
      
    if ($action == 'delete') { //some responses need to be deleted
        $attemptids = isset($_POST['attemptid']) ? $_POST['attemptid'] : array(); //get array of repsonses to delete.
        choice_delete_responses($attemptids); //delete responses.
        redirect("report.php?id=$cm->id");			            
    }
        
    if ($download <> "xls" and $download <> "txt" ) {
        print_header_simple(format_string($choice->name).": $strresponses", "",
                 "<a href=\"index.php?id=$course->id\">$strchoices</a> ->
                  <a href=\"view.php?id=$cm->id\">".format_string($choice->name,true)."</a> -> $strresponses", "", '', true,
                  update_module_button($cm->id, $course->id, $strchoice), navmenu($course, $cm));
    }

    $users = get_course_users($course->id, "u.firstname ASC", '', 'u.id, u.picture, u.firstname, u.lastname, u.idnumber') + get_admins();


    if (!$users) {
        print_heading(get_string("nousersyet"));        
    }

    if ($allresponses = get_records("choice_answers", "choiceid", $choice->id)) {
        foreach ($allresponses as $aa) {
            $answers[$aa->userid] = $aa;
        }
    } else {
        $answers = array () ;
    }

    $timenow = time();

    foreach ($choice->option as $optionid => $text) {
        $useranswer[$optionid] = array();
    }
    foreach ($users as $user) {
        if (!empty($user->id) and !empty($answers[$user->id])) {
            $answer = $answers[$user->id];
            $useranswer[(int)$answer->optionid][] = $user;
        } else {
            $useranswer[0][] = $user;
        }
    }
    foreach ($choice->option as $optionid => $text) {
        if (!$choice->option[$optionid]) {
            unset($useranswer[$optionid]);     // Throw away any data that doesn't apply
        }
    }
    ksort($useranswer);
    
    //print spreadsheet if one is asked for:
    if ($download == "xls") {
        require_once("$CFG->libdir/excellib.class.php");
  
    /// Calculate file name 
        $filename = clean_filename("$course->shortname ".strip_tags(format_string($choice->name,true))).'.xls';
    /// Creating a workbook
        $workbook = new MoodleExcelWorkbook("-");
    /// Send HTTP headers
        $workbook->send($filename);
    /// Creating the first worksheet
        $myxls =& $workbook->add_worksheet($strresponses);

    /// Print names of all the fields
        $myxls->write_string(0,0,get_string("lastname"));
        $myxls->write_string(0,1,get_string("firstname"));
        $myxls->write_string(0,2,get_string("idnumber"));
        $myxls->write_string(0,3,get_string("group"));
        $myxls->write_string(0,4,get_string("choice","choice"));
        
              
    /// generate the data for the body of the spreadsheet
        $i=0;  
        $row=1;
        if ($users) {
            foreach ($users as $user) {
                if (!empty($answers[$user->id]) && !($answers[$user->id]->optionid==0 && isadmin($user->id)) && 
                    (!($answers[$user->id]->optionid==0 && isteacher($course->id, $user->id) && !(isteacheredit($course->id, $user->id)) ) ) &&  
                    !($choice->showunanswered==0 && $answers[$user->id]->optionid==0)  ) { //make sure admins and hidden teachers are not shown in not answered yet column, and not answered only shown if set in config page.

                    $myxls->write_string($row,0,$user->lastname);
                    $myxls->write_string($row,1,$user->firstname);
                    $studentid=(!empty($user->idnumber) ? $user->idnumber : " ");
                    $myxls->write_string($row,2,$studentid);
                    $ug2 = '';
                    if ($usergrps = user_group($course->id, $user->id)) {
                        foreach ($usergrps as $ug) {
						    $ug2 = $ug2. $ug->name;
					    }
			        }
                    $myxls->write_string($row,3,$ug2);
                    
                    $useroption = choice_get_option_text($choice, $answers[$user->id]->optionid);
                    if (isset($useroption)) {
                        $myxls->write_string($row,4,format_string($useroption,true));
                    }                 
                    $row++;
                }
                $pos=4;
            }
        }

    /// Close the workbook
        $workbook->close();

        exit;
    } 
    // print text file     
    if ($download == "txt") {
        $filename = clean_filename("$course->shortname ".strip_tags(format_string($choice->name,true))).'.txt';
            
            header("Content-Type: application/download\n");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Expires: 0");
            header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
            header("Pragma: public");
            
    /// Print names of all the fields

        echo get_string("firstname")."\t".get_string("lastname") . "\t". get_string("idnumber") . "\t";
        echo get_string("group"). "\t";
        echo get_string("choice","choice"). "\n";        
        
    /// generate the data for the body of the spreadsheet
      $i=0;  
      $row=1;
      if ($users) foreach ($users as $user) {
          if (!empty($answers[$user->id]) && !($answers[$user->id]->optionid==0 && isadmin($user->id)) && 
              (!($answers[$user->id]->optionid==0 && isteacher($course->id, $user->id) && !(isteacheredit($course->id, $user->id)) ) ) &&  
              !($choice->showunanswered==0 && $answers[$user->id]->optionid==0)  ) { //make sure admins and hidden teachers are not shown in not answered yet column, and not answered only shown if set in config page.

              echo $user->lastname;
              echo "\t".$user->firstname;
              $studentid = " ";
              if (!empty($user->idnumber)) {
			      $studentid = $user->idnumber;
		 	  }              
              echo "\t". $studentid."\t";
              $ug2 = '';
              if ($usergrps = user_group($course->id, $user->id)) {
                  foreach ($usergrps as $ug) {
			          $ug2 = $ug2. $ug->name;
			      }
			  }
              echo $ug2. "\t";
              echo format_string(choice_get_option_text($choice, $answers[$user->id]->optionid),true). "\n";
          }
      $row++;
      }      
  exit;
}
    choice_show_results($choice, $course, $cm, $format); //show table with students responses.
   
   //now give links for downloading spreadsheets. 
    echo "<br />\n";
    echo "<table border=\"0\" align=\"center\"><tr>\n";
    echo "<td>";
    unset($options);
    $options["id"] = "$cm->id";   
    $options["download"] = "xls";
    print_single_button("report.php", $options, get_string("downloadexcel"));
    echo "</td><td>";
    $options["download"] = "txt";    
    print_single_button("report.php", $options, get_string("downloadtext"));

    echo "</td></tr></table>";
print_footer($course);


?>
