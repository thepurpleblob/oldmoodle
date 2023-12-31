<?php // $Id$
function migrate2utf8_choice_options_text($recordid){
    global $CFG, $globallang;

/// Some trivial checks
    if (empty($recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    $SQL = "SELECT ch.*
           FROM {$CFG->prefix}choice ch,
                {$CFG->prefix}choice_options cho
           WHERE ch.id = cho.choiceid
                 AND cho.id = $recordid";

    if (!$choice = get_record_sql($SQL)) {
        log_the_problem_somewhere();
        return false;
    }
    
    if (!$choiceoption = get_record('choice_options','id',$recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    if ($globallang) {
        $fromenc = $globallang;
    } else {
        $sitelang   = $CFG->lang;
        $courselang = get_course_lang($choice->course);  //Non existing!
        $userlang   = get_main_teacher_lang($choice->course); //N.E.!!

        $fromenc = get_original_encoding($sitelang, $courselang, $userlang);
    }

/// We are going to use textlib facilities
    
/// Convert the text
    if (($fromenc != 'utf-8') && ($fromenc != 'UTF-8')) {
        $result = utfconvert($choiceoption->text, $fromenc);

        $newchoiceoption = new object;
        $newchoiceoption->id = $recordid;
        $newchoiceoption->text = $result;
        migrate2utf8_update_record('choice_options',$newchoiceoption);
    }
/// And finally, just return the converted field
    return $result;
}

function migrate2utf8_choice_name($recordid){
    global $CFG, $globallang;

/// Some trivial checks
    if (empty($recordid)) {
        log_the_problem_somewhere();
        return false;
    }
    if (!$choice = get_record('choice', 'id', $recordid)) {
        log_the_problem_somewhere();
        return false;
    }
    if ($globallang) {
        $fromenc = $globallang;
    } else {
        $sitelang   = $CFG->lang;
        $courselang = get_course_lang($choice->course);  //Non existing!
        $userlang   = get_main_teacher_lang($choice->course); //N.E.!!

        $fromenc = get_original_encoding($sitelang, $courselang, $userlang);
    }

/// We are going to use textlib facilities
    
/// Convert the text
    if (($fromenc != 'utf-8') && ($fromenc != 'UTF-8')) {
        $result = utfconvert($choice->name, $fromenc);

        $newchoice = new object;
        $newchoice->id = $recordid;
        $newchoice->name = $result;
        migrate2utf8_update_record('choice',$newchoice);
    }
/// And finally, just return the converted field
    return $result;
}

function migrate2utf8_choice_text($recordid){
    global $CFG, $globallang;

/// Some trivial checks
    if (empty($recordid)) {
        log_the_problem_somewhere();
        return false;
    }
    if (!$choice = get_record('choice', 'id', $recordid)) {
        log_the_problem_somewhere();
        return false;
    }
    if ($globallang) {
        $fromenc = $globallang;
    } else {
        $sitelang   = $CFG->lang;
        $courselang = get_course_lang($choice->course);  //Non existing!
        $userlang   = get_main_teacher_lang($choice->course); //N.E.!!

        $fromenc = get_original_encoding($sitelang, $courselang, $userlang);
    }

/// We are going to use textlib facilities
    
/// Convert the text
    if (($fromenc != 'utf-8') && ($fromenc != 'UTF-8')) {
        $result = utfconvert($choice->text, $fromenc);

        $newchoice = new object;
        $newchoice->id = $recordid;
        $newchoice->text = $result;
        migrate2utf8_update_record('choice',$newchoice);
    }
/// And finally, just return the converted field
    return $result;
}

?>
