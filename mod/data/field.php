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

    
    $id             = optional_param('id', 0, PARAM_INT);            // course module id
    $d              = optional_param('d', 0, PARAM_INT);             // database id
    $fid            = optional_param('fid', 0 , PARAM_INT);          // update field id
    $newtype        = optional_param('newtype','',PARAM_ALPHA);      // type of the new field
    $mode           = optional_param('mode','',PARAM_ALPHA);
    $defaultsort    = optional_param('defaultsort', 0, PARAM_INT);
    $defaultsortdir = optional_param('defaultsortdir', 0, PARAM_INT);
    $cancel         = optional_param('cancel', '');

    if ($cancel) {
        $mode = 'list';
    }
    
    
    if ($id) {
        if (! $cm = get_record('course_modules', 'id', $id)) {
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

    require_course_login($course, true, $cm);

    if (!isteacheredit($course->id)){
        error(get_string('noaccess','data'));
    }

    
    
    /************************************
     *        Data Processing           *
     ***********************************/
    switch ($mode) {

        case 'add':    ///add a new field
            if (confirm_sesskey() and $fieldinput = data_submitted($CFG->wwwroot.'/mod/data/field.php')){

                $fieldinput->name = data_clean_field_name($fieldinput->name);
                
            /// Only store this new field if it doesn't already exist.
                if (($fieldinput->name == '') or data_fieldname_exists($fieldinput->name, $data->id)) {

                    $displaynoticebad = get_string('invalidfieldname','data');

                } else {   
                    
                /// Check for arrays and convert to a comma-delimited string
                    data_convert_arrays_to_strings($fieldinput);

                /// Create a field object to collect and store the data safely
                    $type = required_param('type', PARAM_FILE);
                    $field = data_get_field_new($type, $data);

                    $field->define_field($fieldinput);
                    $field->insert_field();

                /// Update some templates
                    data_append_new_field_to_templates($data, $field->field->name);

                    add_to_log($course->id, 'data', 'fields add', 
                               "field.php?d=$data->id&amp;mode=display&amp;fid=$fid", $fid, $cm->id);
                    
                    $displaynoticegood = get_string('fieldadded','data');
                }
            }
            break;


        case 'update':    ///update a field
            if (confirm_sesskey() and $fieldinput = data_submitted($CFG->wwwroot.'/mod/data/field.php')){

                $fieldinput->name = data_clean_field_name($fieldinput->name);

                if (($fieldinput->name == '') or data_fieldname_exists($fieldinput->name, $data->id, $fieldinput->fid)) {
                    
                    $displaynoticebad = get_string('invalidfieldname','data');

                } else {
                /// Check for arrays and convert to a comma-delimited string
                    data_convert_arrays_to_strings($fieldinput);

                /// Create a field object to collect and store the data safely
                    $field = data_get_field_from_id($fid, $data);
                    $oldfieldname = $field->field->name;
                    
                    $field->field->name = $fieldinput->name;
                    $field->field->description = $fieldinput->description;
                    
                    for ($i=1; $i<=10; $i++) {
                        if (isset($fieldinput->{'param'.$i})) {
                            $field->field->{'param'.$i} = $fieldinput->{'param'.$i};
                        } else {
                            $field->field->{'param'.$i} = '';
                        }
                    }
                    
                    $field->update_field();
                    
                /// Update the templates.
                    data_replace_field_in_templates($data, $oldfieldname, $field->field->name);
                    
                    add_to_log($course->id, 'data', 'fields update', 
                               "field.php?d=$data->id&amp;mode=display&amp;fid=$fid", $fid, $cm->id);
                    
                    $displaynoticegood = get_string('fieldupdated','data');
                }
            }
            break;


        case 'delete':    // Delete a field
            if (confirm_sesskey()){

                if ($confirm = optional_param('confirm', 0, PARAM_INT)) {


                    // Delete the field completely
                    if ($field = data_get_field_from_id($fid, $data)) {
                        $field->delete_field();

                        // Update the templates.
                        data_replace_field_in_templates($data, $field->field->name, '');

                        // Update the default sort field
                        if ($fid == $data->defaultsort) {
                            unset($rec);
                            $rec->id = $data->id;
                            $rec->defaultsort = 0;
                            $rec->defaultsortdir = 0;
                            if (!update_record('data', $rec)) {
                                error('There was an error updating the database');
                            }
                        }
                        
                        add_to_log($course->id, 'data', 'fields delete', 
                                   "field.php?d=$data->id", $field->field->name, $cm->id);
    
                        $displaynoticegood = get_string('fielddeleted', 'data');
                    }

                } else {

                    data_fields_print_header($course,$cm,$data, false);

                    // Print confirmation message.
                    $field = data_get_field_from_id($fid, $data);

                    notice_yesno('<strong>'.$field->name().': '.$field->field->name.'</strong><br /><br />'. get_string('confirmdeletefield','data'), 
                                 'field.php?d='.$data->id.'&amp;mode=delete&amp;fid='.$fid.'&amp;sesskey='.sesskey().'&amp;confirm=1',
                                 'field.php?d='.$data->id);

                    print_footer($course);
                    exit;
                }
            }
            break;


        case 'sort':    // Set the default sort parameters
            if (confirm_sesskey()) {
                $rec->id = $data->id;
                $rec->defaultsort = $defaultsort;
                $rec->defaultsortdir = $defaultsortdir;

                if (update_record('data', $rec)) {
                    redirect($CFG->wwwroot.'/mod/data/field.php?d='.$data->id, get_string('changessaved'), 2);
                } else {
                    error('There was an error updating the database');
                }
                exit;
            }
            break;

        default:
            break;
    }



/// Print the browsing interface
    
    ///get the list of possible fields (plugins)
    $directories = get_list_of_plugins('mod/data/field/');
    $menufield = array();

    foreach ($directories as $directory){
        $menufield[$directory] = get_string($directory,'data');    //get from language files
    }
    asort($menufield);    //sort in alphabetical order
    

    if (($mode == 'new') && (!empty($newtype)) && confirm_sesskey()) {          ///  Adding a new field
        $CFG->pagepath='mod/data/field/'.$newtype;
        data_fields_print_header($course,$cm,$data);

        $field = data_get_field_new($newtype, $data);
        $field->display_edit_field();

    } else if ($mode == 'display' && confirm_sesskey()) { /// Display/edit existing field
        $CFG->pagepath='mod/data/field/'.$newtype;
        data_fields_print_header($course,$cm,$data);

        $field = data_get_field_from_id($fid, $data);
        $field->display_edit_field();

    } else {                                              /// Display the main listing of all fields

        $CFG->pagepath='mod/data/field/'.$newtype;
        data_fields_print_header($course,$cm,$data);
      
        
        if (!record_exists('data_fields','dataid',$data->id)) {
            notify(get_string('nofieldindatabase','data'));  // nothing in database

        } else {    //else print quiz style list of fields

            $table->head = array(get_string('fieldname','data'), get_string('type','data'), get_string('fielddescription', 'data'), get_string('action','data'));
            $table->align = array('left','left','left', 'center');
            $table->wrap = array(false,false,false,false);

            if ($fff = get_records('data_fields','dataid',$data->id,'id')){
                foreach ($fff as $ff) {
                    
                    $field = data_get_field($ff, $data);

                    $table->data[] = array(



                    '<a href="field.php?mode=display&amp;d='.$data->id.
                    '&amp;fid='.$field->field->id.'&amp;sesskey='.sesskey().'">'.$field->field->name.'</a>',


                    $field->image().'&nbsp;'.get_string($field->type, 'data'),

                    shorten_text($field->field->description, 30),

                    '<a href="field.php?d='.$data->id.'&amp;mode=display&amp;fid='.$field->field->id.'&amp;sesskey='.sesskey().'">'.
                    '<img src="'.$CFG->pixpath.'/t/edit.gif" height="11" width="11" border="0" alt="'.get_string('edit').'" /></a>'.
                    '&nbsp;'.
                    '<a href="field.php?d='.$data->id.'&amp;mode=delete&amp;fid='.$field->field->id.'&amp;sesskey='.sesskey().'">'.
                    '<img src="'.$CFG->pixpath.'/t/delete.gif" height="11" width="11" border="0" alt="'.get_string('delete').'" /></a>'
                    
                    );
                }
            }
            print_table($table);
        } 
        

        echo '<div class="fieldadd" align="center">';
        echo get_string('newfield','data').': ';
        popup_form($CFG->wwwroot.'/mod/data/field.php?d='.$data->id.'&amp;mode=new&amp;sesskey='.
                sesskey().'&amp;newtype=', $menufield, 'fieldform', '', 'choose');
        helpbutton('fields', get_string('addafield','data'), 'data');
        echo '</div>';

        echo '<div class="sortdefault" align="center">';
        echo '<form name="sortdefault" action="'.$CFG->wwwroot.'/mod/data/field.php" method="get">';
        echo '<input type="hidden" name="d" value="'.$data->id.'" />';
        echo '<input type="hidden" name="mode" value="sort" />';
        echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
        echo '&nbsp;'.get_string('defaultsortfield','data').':';
        $fields = get_records('data_fields','dataid',$data->id);
        echo '<select name="defaultsort"><option value="0">'.get_string('dateentered','data').'</option>';
        foreach ($fields as $field) {
            if ($field->id == $data->defaultsort) {
                echo '<option value="'.$field->id.'" selected="selected">'.$field->name.'</option>';
            } else {
                echo '<option value="'.$field->id.'">'.$field->name.'</option>';
            }
        }
        echo '</select>';

        echo '&nbsp;';
        
        $options = array(0 => get_string('ascending', 'data'),
                         1 => get_string('descending', 'data'));
        choose_from_menu($options, 'defaultsortdir', $data->defaultsortdir, '');
        echo '<input type="submit" value="'.get_string('go').'" />';

        echo '</form>';
        echo '</div>';


    }

/// Finish the page
    print_footer($course);


    function data_fields_print_header($course,$cm,$data,$showtabs=true) {

        global $CFG, $displaynoticegood, $displaynoticebad;

        $strdata = get_string('modulenameplural','data');

        print_header_simple($data->name, '', "<a href='index.php?id=$course->id'>$strdata</a> -> $data->name", 
                '', '', true, '', navmenu($course, $cm));

        print_heading(format_string($data->name));

        /// Print the tabs

        if ($showtabs) {
            $currenttab = 'fields';
            include_once('tabs.php');
        }

        /// Print any notices

        if (!empty($displaynoticegood)) {
            notify($displaynoticegood, 'notifysuccess');    // good (usually green)
        } else if (!empty($displaynoticebad)) {
            notify($displaynoticebad);                     // bad (usuually red)
        }
    }

?>
