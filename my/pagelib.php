<?php

global $CFG;

require_once($CFG->libdir.'/pagelib.php');

class page_my_moodle extends page_base {

    function get_type() {
        return PAGE_MY_MOODLE;
    }

    function user_allowed_editing() {
        page_id_and_class($id,$class);
        if (isadmin() && $id == PAGE_ADMIN_MY_MOODLE) {
            return true;
        } elseif ($id == PAGE_MY_MOODLE) {
            return true;
        }
        return false;
    }

    function edit_always() {
        page_id_and_class($id,$class);
        return ($id == PAGE_ADMIN_MY_MOODLE && isadmin());
    }
    
    function user_is_editing() {
        global $USER;
        page_id_and_class($id,$class);
        if (isadmin() && $id == PAGE_ADMIN_MY_MOODLE) {
            return true;
        }
        return (!empty($USER->editing));
    }

    function print_header($title) {

        global $USER;

        $site = get_site();

        $button = update_mymoodle_icon($USER->id);
        $header = get_string('mymoodle','my');
        $nav = $header;
        
        $loggedinas = '<p class="logininfo">'. user_login_string($site) .'</p>';
        print_header($title, $header,$nav,'','',true, $button, $loggedinas);

    }
    
    function url_get_path() {
        page_id_and_class($id,$class);
        if ($id == PAGE_ADMIN_MY_MOODLE) {
            return $GLOBALS['CFG']->wwwroot.'/admin/mymoodle.php';
        }
        return $GLOBALS['CFG']->wwwroot.'/my/index.php';
    }
       
    function blocks_default_position() {
        return BLOCK_POS_LEFT;
    }

    function blocks_get_positions() {
        return array(BLOCK_POS_LEFT, BLOCK_POS_RIGHT);
    }

    function blocks_move_position(&$instance, $move) {
        if($instance->position == BLOCK_POS_LEFT && $move == BLOCK_MOVE_RIGHT) {
            return BLOCK_POS_RIGHT;
        } else if ($instance->position == BLOCK_POS_RIGHT && $move == BLOCK_MOVE_LEFT) {
            return BLOCK_POS_LEFT;
        }
        return $instance->position;
    }

    function get_format_name() {
        return MY_MOODLE_FORMAT;
    }
}


define('PAGE_MY_MOODLE',   'my-index');
define('PAGE_ADMIN_MY_MOODLE', 'admin-mymoodle');
define('MY_MOODLE_FORMAT', 'my'); //doing this so we don't run into problems with applicable formats.

page_map_class(PAGE_MY_MOODLE, 'page_my_moodle');
page_map_class(PAGE_ADMIN_MY_MOODLE,'page_my_moodle');




?>