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
    require_once($CFG->libdir.'/blocklib.php');

    define('PAGE_DATA_FIELDS',   'mod-data-fields');
    define('PAGE_DATA', PAGE_DATA_FIELDS);

    require_once('pagelib.php');
    require_login();

    page_map_class(PAGE_DATA_FIELDS, 'page_data');
    $DEFINEDPAGES = array(PAGE_DATA_FIELDS);

    $id    = optional_param('id', 0, PARAM_INT);    // course module id
    $d     = optional_param('d', 0, PARAM_INT);    // database id
    $fid   = optional_param('fid', 0 , PARAM_INT);    //update field id
    $newtype = optional_param('fieldmenu','',PARAM_ALPHA);    //type of the new field

    //action specifies what action is performed when data is submitted
    $mode = optional_param('mode','',PARAM_ALPHA);
    $displayflag = '';    //str to print after an operation,
    
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

    if (!isteacheredit($course->id)){
        error(get_string('noaccess','data'));
    }

    add_to_log($course->id, 'data', 'view', "view.php?id=$cm->id", $data->id, $cm->id);
    
// Initialize $PAGE, compute blocks

    $PAGE       = page_create_instance($data->id);
    $pageblocks = blocks_setup($PAGE);
    $blocks_preferred_width = bounded_number(180, blocks_preferred_width($pageblocks[BLOCK_POS_LEFT]), 210);

/// Print the page header

    if (!empty($edit) && $PAGE->user_allowed_editing()) {
        if ($edit == 'on') {
            $USER->editing = true;
        } else if ($edit == 'off') {
            $USER->editing = false;
        }
    }

    $PAGE->print_header($course->shortname.': %fullname%');

    echo '<table id="layout-table"><tr>';

    if(!empty($CFG->showblocksonmodpages) && (blocks_have_content($pageblocks, BLOCK_POS_LEFT) || $PAGE->user_is_editing())) {
        echo '<td style="width: '.$blocks_preferred_width.'px;" id="left-column">';
        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_LEFT);
        echo '</td>';
    }

    echo '<td id="middle-column">';

    print_heading(format_string($data->name));
    
    
    /************************************
     *        Data Processing           *
     ***********************************/
    switch($mode){

        case 'add':    ///add a new field
            if (confirm_sesskey() and $field = data_submitted()){

                $sql = 'SELECT * from '.$CFG->prefix.'data_fields WHERE name LIKE "'.$field->name.
                       '" AND dataid = '.$data->id;
                if ($field->name and !get_record_sql($sql)){    
                    $field->dataid = $data->id;
                    insert_record('data_fields', $field);
                    $displayflag = get_string('fieldadded','data');
                }
                else {    //no duplicate names allowed in one database!
                    $displayflag = get_string('invalidfieldname','data');
                }
            }
            break;

        case 'delete':    ///delete a field
            if (confirm_sesskey()){
                if ($confirm = optional_param('confirm', 0, PARAM_INT)){
                    delete_records('data_fields','id',$fid);
                    $displayflag = get_string('fielddeleted','data');
                }
                else {    //prints annoying confirmation dialogue
                    $field = get_record('data_fields','id',$fid);
                    print_simple_box_start('center', '60%');
                    echo '<div align="center">';
                    echo '<form action = "fields.php?d='.$data->id.'&amp;mode=delete&amp;fid='.$fid.'" method="post">';
                    echo '<input name="sesskey" value="'.sesskey().'" type="hidden" />';
                    echo '<input name="confirm" value="1" type="hidden" />';
                    echo '<strong>'.$field->name.'</strong> - '.get_string('confirmdeletefield','data');
                    echo '<p />';
                    echo '<input type="submit" value="'.get_string('ok').'"> ';
                    echo '<input type="button" value="'.get_string('cancel').'" onclick="javascript:history.go(-1);" />';
                    echo '</form>';
                    echo '</div>';
                    print_simple_box_end();
                    echo '</td></tr></table>';
                    print_footer($course);
                    exit;
                }
            }
            break;

        case 'update':    ///update a field
            if (confirm_sesskey() and $field = data_submitted()){

                $field->id = $fid;

                $field->name = optional_param('name','',PARAM_ALPHA);
                $sql = 'SELECT * from '.$CFG->prefix.'data_fields WHERE name LIKE "'.$field->name.
                       '" AND dataid = '.$data->id.' AND ((id < '.$field->id.') OR (id > '.$field->id.'))';

                if ($field->name and !get_records_sql($sql)){
                    //depends on the type of field, perform native update methods
                    $currentfield = get_record('data_fields','id',$fid);
                    $g = data_get_field($currentfield);
                    $g->update($field);
                    unset($g);
                    $displayflag = get_string('fieldupdated','data');
                }
                else {
                    $displayflag = get_string('invalidfieldname','data');
                }
            }
            break;

        default:
            break;
    }

/// Check to see if groups are being used here
    if ($groupmode = groupmode($course, $cm)) {   // Groups are being used
        $currentgroup = setup_and_print_groups($course, $groupmode, "view.php?id=$cm->id");
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

    $currenttab = 'fields';
    include('tabs.php'); 

/// Print the browsing interface
    
    ///get the list of possible fields (plugins)
    $directories = get_list_of_plugins('mod/data/field/');
    $menufield = array();

    foreach ($directories as $directory){
        $menufield[$directory] = get_string($directory,'data');    //get from language files
    }
    asort($menufield);    //sort in alphabetical order
    
    notify ($displayflag);    //print message, if any

    if (($mode == 'new') && confirm_sesskey() ){    //adding new field

        require_once('field/'.$newtype.'/field.class.php');
        $f = 'data_field_'.$newtype;
        $g = new $f;
        $g->display_edit_field(0, $data->id);
        unset($f);
        unset($g);
    }
    
    else if ($mode != 'display'){    //display main form - add new, update, delete

        echo '<form name="fieldform" action="fields.php?d='.$data->id.'&amp;" method="POST">';
        echo '<input type="hidden" name="mode" value="">';
        echo '<input name="sesskey" value="'.sesskey().'" type="hidden">';
        print_simple_box_start('center','50%');

        ///New fields
        echo '<table width="100%"><tr>';
        echo '<td>'.get_string('newfield','data').' ';
        choose_from_menu($menufield,'fieldmenu','0','choose','fieldform.mode.value=\'new\';fieldform.submit();','0');
        helpbutton('fields', get_string('addafield','data'), 'data');
        echo '</td></tr>';
        
        /*******************************************
         * Print List of Fields in Quiz Style      *
         *******************************************/
        
        if (!count_records('data_fields','dataid',$data->id)) {
            echo '<tr><td colspan="2">'.get_string('nofieldindatabase','data').'</td></tr>';  // nothing in database
        }
        else {    //else print quiz style list of fields
            
            echo '<tr><td>';
            print_simple_box_start('center','90%');
            echo '<table width="100%"><tr><td align="center"><b>'.get_string('action','data').
                 '</b></td><td><b>'.get_string('fieldname','data').
                 '</b></td><td align="center"><b>'.get_string('type','data').'</b></td></tr>';

            if ($fields = get_records('data_fields','dataid',$data->id)){
                foreach ($fields as $field) {

                    ///Print Action Column

                    echo '<tr><td align="center">';
                    echo '<a href="fields.php?d='.$data->id.'&amp;mode=display&amp;fid='.$field->id.'&amp;sesskey='.sesskey().'">';
                    echo '<img src="'.$CFG->pixpath.'/t/edit.gif"
                    height="11" width="11" border="0" alt="'.get_string('edit').'" /></a>';
                    echo '&nbsp;';
                    echo '<a href="fields.php?d='.$data->id.'&amp;mode=delete&amp;fid='.$field->id.'&amp;sesskey='.sesskey().'">';
                    echo '<img src="'.$CFG->pixpath.'/t/delete.gif"
                    height="11" width="11" border="0" alt="'.get_string('delete').'" /></a>';
                    echo '</td>';

                    ///Print Fieldname Column

                    echo '<td>';
                    echo $field->name;
                    echo '</td>';

                    ///Print Type Column

                    echo '<td align="center">';
                    $g = data_get_field($field);
                    echo $g->image();    //print type icon
                    echo '</td></tr>';
                }
            }
            echo '</table>';
            print_simple_box_end();
        }    //close else
        
        echo '</td></tr></table>';

        print_simple_box_end();
        echo '</form>';

    }else {    //display native update form

        if (confirm_sesskey()){
            $currentfield = get_record('data_fields','id',$fid);
            $g = data_get_field($currentfield);
            $g->display_edit_field($currentfield->id);
            unset($g);
        }
    }

/// Finish the page
    echo '</td></tr></table>';

    print_footer($course);

?>