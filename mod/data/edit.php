<?php  // $Id$
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.org                                            //
//                                                                       //
// Copyright (C) 2005 Martin Dougiamas  http://dougiamas.com             //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

    require_once('../../config.php');
    require_once('lib.php');

    $id    = optional_param('id', 0, PARAM_INT);    // course module id
    $d     = optional_param('d', 0, PARAM_INT);    // database id
    $rid   = optional_param('rid', 0, PARAM_INT);    //record id
    $import   = optional_param('import', 0, PARAM_INT);    // show import form
    $cancel   = optional_param('cancel', '');    // cancel an add
    $mode ='addtemplate';    //define the mode for this page, only 1 mode available

    if ($id) {
        if (! $cm = get_coursemodule_from_id('data', $id)) {
            error('Course Module ID was incorrect');
        }
        if (! $course = get_record('course', 'id', $cm->course)) {
            error('Course is misconfigured');
        }
        if (! $data = get_record('data', 'id', $cm->instance)) {
            error('Course module is incorrect');
        }

    } else {
        if (! $data = get_record('data', 'id', $d)) {
            error('Data ID is incorrect');
        }
        if (! $course = get_record('course', 'id', $data->course)) {
            error('Course is misconfigured');
        }
        if (! $cm = get_coursemodule_from_instance('data', $data->id, $course->id)) {
            error('Course Module ID was incorrect');
        }
    }
    
    require_course_login($course, false, $cm);
    
    if (!isloggedin() or isguest()) {
        redirect('view.php?d='.$data->id);
    }

/// If it's hidden then it's don't show anything.  :)
    if (empty($cm->visible) and !isteacher($course->id)) {
        $strdatabases = get_string("modulenameplural", "data");
        $navigation = "<a href=\"index.php?id=$course->id\">$strdatabases</a> ->";
        print_header_simple(format_string($data->name), "",
                 "$navigation ".format_string($data->name), "", "", true, '', navmenu($course, $cm));
        notice(get_string("activityiscurrentlyhidden"));
    }
    
/// Can't use this if there are no fields
    if (isteacher($course->id)) {
        if (!record_exists('data_fields','dataid',$data->id)) {      // Brand new database!
            redirect($CFG->wwwroot.'/mod/data/field.php?d='.$data->id);  // Redirect to field entry
        }
    }

/// Check access for participants
    if ((!isteacher($course->id)) && $data->participants == DATA_TEACHERS_ONLY) {
        error (get_string('noaccess','data'));
    }

    if ($rid) {    // So do you have access?
        if (!(isteacher($course->id) or data_isowner($rid)) or !confirm_sesskey() ) {
            error(get_string('noaccess','data'));
        }
    }

    if ($cancel) {
        redirect('view.php?d='.$data->id);
    }
  

/// Print the page header

    $strdata = get_string('modulenameplural','data');

    print_header_simple($data->name, '', "<a href='index.php?id=$course->id'>$strdata</a> -> $data->name",
                        '', '', true, '', navmenu($course, $cm), '', '');

    print_heading(format_string($data->name));

/// Check to see if groups are being used here

    if ($groupmode = groupmode($course, $cm)) {   // Groups are being used
        $currentgroup = setup_and_print_groups($course, $groupmode, 'edit.php?d='.$data->id.'&amp;sesskey='.sesskey().'&amp;');
    } else {
        $currentgroup = 0;
    }

    if ($currentgroup) {
        $groupselect = " AND groupid = '$currentgroup'";
        $groupparam = "&amp;groupid=$currentgroup";
    } else {
        $groupselect = "";
        $groupparam = "";
    }

/// Print the tabs

    $currenttab = 'add';
    if ($rid) {
        $editentry = true;  //used in tabs
    }
    include('tabs.php');


/// Process incoming data for adding/updating records

    if ($datarecord = data_submitted($CFG->wwwroot.'/mod/data/edit.php') and confirm_sesskey()) {

        $ignorenames = array('MAX_FILE_SIZE','sesskey','d','rid','saveandview','cancel');  // strings to be ignored in input data

        if ($rid) {                                          /// Update some records

            /// All student edits are marked unapproved by default
            $record = get_record('data_records','id',$rid);

            /// reset approved flag after student edit
            if (!isteacher($course->id)) {
                $record->approved = 0;
            }
            
            $record->groupid = $currentgroup;
            $record->timemodified = time();
            update_record('data_records',$record);

            /// Update all content
            $field = NULL;
            foreach ($datarecord as $name => $value) {
                if (!in_array($name, $ignorenames)) {
                    $namearr = explode('_',$name);  // Second one is the field id
                    if (empty($field->field) || ($namearr[1] != $field->field->id)) {  // Try to reuse classes
                        $field = data_get_field_from_id($namearr[1], $data);
                    }
                    if ($field) {
                        $field->update_content($rid, $value, $name);
                    }
                }
            }

            add_to_log($course->id, 'data', 'update', "view.php?d=$data->id&amp;rid=$rid", $data->id, $cm->id);

            redirect($CFG->wwwroot.'/mod/data/view.php?d='.$data->id.'&amp;rid='.$rid);

        } else { /// Add some new records
        
        /// Check if maximum number of entry as specified by this database is reached
        /// Of course, you can't be stopped if you are an editting teacher! =)

            if (data_atmaxentries($data) and !isteacheredit($course->id)){
                notify (get_string('atmaxentry','data'));
                print_footer($course);
                exit;
            }

            ///Empty form checking - you can't submit an empty form!

            $emptyform = true;      // assume the worst
            
            foreach ($datarecord as $name => $value) {  
                if (!in_array($name, $ignorenames)) {
                    $namearr = explode('_', $name);  // Second one is the field id
                    if (empty($field->field) || ($namearr[1] != $field->field->id)) {  // Try to reuse classes
                        $field = data_get_field_from_id($namearr[1], $data);
                    }
                    if ($field->notemptyfield($value, $name)) {
                        $emptyform = false;    
                        break;             // if anything has content, this form is not empty, so stop now!
                    }
                }
            }  

            if ($emptyform){    //nothing gets written to database
                notify(get_string('emptyaddform','data'));
            }

            if (!$emptyform && $recordid = data_add_record($data, $currentgroup)) {    //add instance to data_record
                
                /// Insert a whole lot of empty records to make sure we have them
                $fields = get_records('data_fields','dataid',$data->id);
                foreach ($fields as $field) {
                    $content->recordid = $recordid;
                    $content->fieldid = $field->id;
                    insert_record('data_content',$content);
                }

                //for each field in the add form, add it to the data_content.
                foreach ($datarecord as $name => $value){
                    if (!in_array($name, $ignorenames)) {
                        $namearr = explode('_', $name);  // Second one is the field id
                        if (empty($field->field) || ($namearr[1] != $field->field->id)) {  // Try to reuse classes
                            $field = data_get_field_from_id($namearr[1], $data);
                        }
                        if ($field) {
                            $field->update_content($recordid, $value, $name);
                        }
                    }
                }

                add_to_log($course->id, 'data', 'add', "view.php?d=$data->id&amp;rid=$recordid", $data->id, $cm->id);

                notify(get_string('entrysaved','data'));

                if (!empty($datarecord->saveandview)) {
                    redirect($CFG->wwwroot.'/mod/data/view.php?d='.$data->id.'&amp;rid='.$recordid);
                }
            }
        }
    }  // End of form processing

    /// Print the browsing interface

    $patterns = array();    //tags to replace
    $replacement = array();    //html to replace those yucky tags

    //form goes here first in case add template is empty
    echo '<form enctype="multipart/form-data" action="edit.php" method="post">';
    echo '<input name="d" value="'.$data->id.'" type="hidden" />';
    echo '<input name="rid" value="'.$rid.'" type="hidden" />';
    echo '<input name="sesskey" value="'.sesskey().'" type="hidden" />';
    print_simple_box_start('center','80%');
    
    if (!$rid){
        print_heading(get_string('newentry','data'), '', 2);
    }
        
    /******************************************
     * Regular expression replacement section *
     ******************************************/
    if ($data->addtemplate){
        $possiblefields = get_records('data_fields','dataid',$data->id,'id');
        
        ///then we generate strings to replace
        foreach ($possiblefields as $eachfield){
            $field = data_get_field($eachfield, $data);
            $patterns[]="/\[\[".$field->field->name."\]\]/i";
            $replacements[] = $field->display_add_field($rid);
        }
        $newtext = preg_replace($patterns, $replacements, $data->{$mode});

    } else {    //if the add template is not yet defined, print the default form!
        echo data_generate_default_template($data, 'addtemplate', $rid, true, false);
        $newtext = '';
    }
   
    echo $newtext;
    echo '<div align="center"><input type="submit" name="saveandview" value="'.get_string('saveandview','data').'" />';
    if ($rid) {
        echo '&nbsp;<input type="submit" name="cancel" value="'.get_string('cancel').'" onclick="javascript:history.go(-1)">';
    } else {
        echo '<input type="submit" value="'.get_string('saveandadd','data').'" />';
    }
    echo '</div>';
    print_simple_box_end();
    echo '</form>';

    
/// Upload records section. Only for teachers and the admin.
    
    if (isteacher($course->id)) {
        if ($import) {
            print_simple_box_start('center','80%');
            print_heading(get_string('uploadrecords', 'data'), '', 3);

            $maxuploadsize = get_max_upload_file_size();
            echo '<div align="center">';
            echo '<form enctype="multipart/form-data" action="import.php" method="post">';
            echo '<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">';
            echo '<input name="d" value="'.$data->id.'" type="hidden" />';
            echo '<input name="sesskey" value="'.sesskey().'" type="hidden" />';
            echo '<table align="center" cellspacing="0" cellpadding="2" border="0">';
            echo '<tr>';
            echo '<td align="right">'.get_string('csvfile', 'data').':</td>';
            echo '<td><input type="file" name="recordsfile" size="30">';
            helpbutton('importcsv', get_string('csvimport', 'data'), 'data', true, false);
            echo '</td><tr>';
            echo '<td align="right">'.get_string('fielddelimiter', 'data').':</td>';
            echo '<td><input type="text" name="fielddelimiter" size="6">';
            echo get_string('defaultfielddelimiter', 'data').'</td>';
            echo '</tr>';
            echo '<td align="right">'.get_string('fieldenclosure', 'data').':</td>';
            echo '<td><input type="text" name="fieldenclosure" size="6">';
            echo get_string('defaultfieldenclosure', 'data').'</td>';
            echo '</tr>';
            echo '</table>';
            echo '<input type="submit" value="'.get_string('uploadfile', 'data').'">';
            echo '</form>';
            echo '</div>';
            print_simple_box_end();
        } else {
            echo '<div align="center">';
            echo '<a href="edit.php?d='.$data->id.'&amp;import=1">'.get_string('uploadrecords', 'data').'</a>';
            echo '</div>';
        }
    }


/// Finish the page

    // Print the stuff that need to come after the form fields.
    if (!$fields = get_records('data_fields', 'dataid', $data->id)) {
        error(get_string('nofieldindatabase', 'data'));
    }
    foreach ($fields as $eachfield) {
        $field = data_get_field($eachfield, $data);
        $field->print_after_form();
    }
    
    print_footer($course);
?>
