<?php // $Id$
function migrate2utf8_workshop_stockcomments_comments($recordid){
    global $CFG, $globallang;

/// Some trivial checks
    if (empty($recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    $SQL = "SELECT w.course
           FROM {$CFG->prefix}workshop w,
                {$CFG->prefix}workshop_stockcomments ws
           WHERE w.id = ws.workshopid
                 AND ws.id = $recordid";
                 
    if (!$workshop = get_record_sql($SQL)) {
        log_the_problem_somewhere();
        return false;
    }

    if (!$workshopstockcomments = get_record('workshop_stockcomments','id',$recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    if ($globallang) {
        $fromenc = $globallang;
    } else {
        $sitelang   = $CFG->lang;
        $courselang = get_course_lang($workshop->course);  //Non existing!
        $userlang   = get_main_teacher_lang($workshop->course); //N.E.!!

        $fromenc = get_original_encoding($sitelang, $courselang, $userlang);
    }

/// We are going to use textlib facilities
    
/// Convert the text
    if (($fromenc != 'utf-8') && ($fromenc != 'UTF-8')) {
        $result = utfconvert($workshopstockcomments->comments, $fromenc);

        $newworkshopstockcomments = new object;
        $newworkshopstockcomments->id = $recordid;
        $newworkshopstockcomments->comments = $result;
        migrate2utf8_update_record('workshop_stockcomments',$newworkshopstockcomments);
    }
/// And finally, just return the converted field
    return $result;
}

function migrate2utf8_workshop_rubrics_description($recordid){
    global $CFG, $globallang;

/// Some trivial checks
    if (empty($recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    $SQL = "SELECT w.course
           FROM {$CFG->prefix}workshop w,
                {$CFG->prefix}workshop_rubrics wr
           WHERE w.id = wr.workshopid
                 AND wr.id = $recordid";

    if (!$workshop = get_record_sql($SQL)) {
        log_the_problem_somewhere();
        return false;
    }

    if (!$workshoprubrics = get_record('workshop_rubrics','id',$recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    if ($globallang) {
        $fromenc = $globallang;
    } else {
        $sitelang   = $CFG->lang;
        $courselang = get_course_lang($workshop->course);  //Non existing!
        $userlang   = get_main_teacher_lang($workshop->course); //N.E.!!

        $fromenc = get_original_encoding($sitelang, $courselang, $userlang);
    }

/// We are going to use textlib facilities
    
/// Convert the text
    if (($fromenc != 'utf-8') && ($fromenc != 'UTF-8')) {
        $result = utfconvert($workshoprubrics->description, $fromenc);

        $newworkshoprubrics = new object;
        $newworkshoprubrics->id = $recordid;
        $newworkshoprubrics->description = $result;
        migrate2utf8_update_record('workshop_rubrics',$newworkshoprubrics);
    }
/// And finally, just return the converted field
    return $result;
}

function migrate2utf8_workshop_grades_feedback($recordid){
    global $CFG, $globallang;

/// Some trivial checks
    if (empty($recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    $SQL = "SELECT w.course
           FROM {$CFG->prefix}workshop w,
                {$CFG->prefix}workshop_grades wg
           WHERE w.id = wg.workshopid
                 AND wg.id = $recordid";

    if (!$workshop = get_record_sql($SQL)) {
        log_the_problem_somewhere();
        return false;
    }

    if (!$workshopgrades = get_record('workshop_grades','id',$recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    if ($globallang) {
        $fromenc = $globallang;
    } else {
        $sitelang   = $CFG->lang;
        $courselang = get_course_lang($workshop->course);  //Non existing!
        $userlang   = get_main_teacher_lang($workshop->course); //N.E.!!

        $fromenc = get_original_encoding($sitelang, $courselang, $userlang);
    }

/// We are going to use textlib facilities
    
/// Convert the text
    if (($fromenc != 'utf-8') && ($fromenc != 'UTF-8')) {
        $result = utfconvert($workshopgrades->feedback, $fromenc);

        $newworkshopgrades = new object;
        $newworkshopgrades->id = $recordid;
        $newworkshopgrades->feedback = $result;
        migrate2utf8_update_record('workshop_grades',$newworkshopgrades);
    }
/// And finally, just return the converted field
    return $result;
}

function migrate2utf8_workshop_elements_description($recordid){
    global $CFG, $globallang;

/// Some trivial checks
    if (empty($recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    $SQL = "SELECT w.course
           FROM {$CFG->prefix}workshop w,
                {$CFG->prefix}workshop_elements we
           WHERE w.id = we.workshopid
                 AND we.id = $recordid";

    if (!$workshop = get_record_sql($SQL)) {
        log_the_problem_somewhere();
        return false;
    }

    if (!$workshopelements = get_record('workshop_elements','id',$recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    if ($globallang) {
        $fromenc = $globallang;
    } else {
        $sitelang   = $CFG->lang;
        $courselang = get_course_lang($workshop->course);  //Non existing!
        $userlang   = get_main_teacher_lang($workshop->course); //N.E.!!

        $fromenc = get_original_encoding($sitelang, $courselang, $userlang);
    }

/// We are going to use textlib facilities
    
/// Convert the text
    if (($fromenc != 'utf-8') && ($fromenc != 'UTF-8')) {
        $result = utfconvert($workshopelements->description, $fromenc);

        $newworkshopelements = new object;
        $newworkshopelements->id = $recordid;
        $newworkshopelements->description = $result;
        migrate2utf8_update_record('workshop_elements',$newworkshopelements);
    }
/// And finally, just return the converted field
    return $result;
}

function migrate2utf8_workshop_name($recordid){
    global $CFG, $globallang;

/// Some trivial checks
    if (empty($recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    if (!$workshop = get_record('workshop','id',$recordid)) {
        log_the_problem_somewhere();
        return false;
    }
    if ($globallang) {
        $fromenc = $globallang;
    } else {
        $sitelang   = $CFG->lang;
        $courselang = get_course_lang($workshop->course);  //Non existing!
        $userlang   = get_main_teacher_lang($workshop->course); //N.E.!!

        $fromenc = get_original_encoding($sitelang, $courselang, $userlang);
    }

/// We are going to use textlib facilities
    
/// Convert the text
    if (($fromenc != 'utf-8') && ($fromenc != 'UTF-8')) {
        $result = utfconvert($workshop->name, $fromenc);

        $newworkshop = new object;
        $newworkshop->id = $recordid;
        $newworkshop->name = $result;
        migrate2utf8_update_record('workshop',$newworkshop);
    }
/// And finally, just return the converted field
    return $result;
}

function migrate2utf8_workshop_description($recordid){
    global $CFG, $globallang;

/// Some trivial checks
    if (empty($recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    if (!$workshop = get_record('workshop','id',$recordid)) {
        log_the_problem_somewhere();
        return false;
    }
    if ($globallang) {
        $fromenc = $globallang;
    } else {
        $sitelang   = $CFG->lang;
        $courselang = get_course_lang($workshop->course);  //Non existing!
        $userlang   = get_main_teacher_lang($workshop->course); //N.E.!!

        $fromenc = get_original_encoding($sitelang, $courselang, $userlang);
    }

/// We are going to use textlib facilities
    
/// Convert the text
    if (($fromenc != 'utf-8') && ($fromenc != 'UTF-8')) {
        $result = utfconvert($workshop->description, $fromenc);

        $newworkshop = new object;
        $newworkshop->id = $recordid;
        $newworkshop->description = $result;
        migrate2utf8_update_record('workshop',$newworkshop);
    }
/// And finally, just return the converted field
    return $result;
}

function migrate2utf8_workshop_password($recordid){
    global $CFG, $globallang;

/// Some trivial checks
    if (empty($recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    if (!$workshop = get_record('workshop','id',$recordid)) {
        log_the_problem_somewhere();
        return false;
    }
    if ($globallang) {
        $fromenc = $globallang;
    } else {
        $sitelang   = $CFG->lang;
        $courselang = get_course_lang($workshop->course);  //Non existing!
        $userlang   = get_main_teacher_lang($workshop->course); //N.E.!!

        $fromenc = get_original_encoding($sitelang, $courselang, $userlang);
    }

/// We are going to use textlib facilities
    
/// Convert the text
    if (($fromenc != 'utf-8') && ($fromenc != 'UTF-8')) {
        $result = utfconvert($workshop->password, $fromenc);

        $newworkshop = new object;
        $newworkshop->id = $recordid;
        $newworkshop->password = $result;
        migrate2utf8_update_record('workshop',$newworkshop);
    }
/// And finally, just return the converted field
    return $result;
}
?>
