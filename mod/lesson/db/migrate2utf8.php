<?
function migrate2utf8_lesson_answers_answer($recordid){
    global $CFG;

/// Some trivial checks
    if (empty($recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    $SQL = "SELECT l.course
           FROM {$CFG->prefix}lesson l,
                {$CFG->prefix}lesson_answers la
           WHERE l.id = la.lessonid
                 AND la.id = $recordid";

    if (!$lessonanswers = get_record_sql($SQL)) {
        log_the_problem_somewhere();
        return false;
    }

    if (!$lesson = get_record('lesson','id',$lessonanswers->course)) {
        log_the_problem_somewhere();
        return false;
    }

    $sitelang   = $CFG->lang;
    $courselang = get_course_lang($lessonanswers->course);  //Non existing!
    $userlang   = get_main_teacher_lang($lessonanswers->course); //N.E.!!

    $fromenc = get_original_encoding($sitelang, $courselang, $userlang);

/// We are going to use textlib facilities
    $textlib = textlib_get_instance();
/// Convert the text
    $result = $textlib->convert($lessonanswers->answer, $fromenc);

    $newlessonanswers = new object;
    $newlessonanswers->id = $recordid;
    $newlessonanswers->answer = $result;
    update_record('lesson_answers',$newlessonanswers);
/// And finally, just return the converted field
    return $result;
}

function migrate2utf8_lesson_answers_response($recordid){
    global $CFG;

/// Some trivial checks
    if (empty($recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    $SQL = "SELECT l.course
           FROM {$CFG->prefix}lesson l,
                {$CFG->prefix}lesson_answers la
           WHERE l.id = la.lessonid
                 AND la.id = $recordid";

    if (!$lessonanswers = get_record_sql($SQL)) {
        log_the_problem_somewhere();
        return false;
    }

    if (!$lesson = get_record('lesson','id',$lessonanswers->course)) {
        log_the_problem_somewhere();
        return false;
    }

    $sitelang   = $CFG->lang;
    $courselang = get_course_lang($lessonanswers->course);  //Non existing!
    $userlang   = get_main_teacher_lang($lessonanswers->course); //N.E.!!

    $fromenc = get_original_encoding($sitelang, $courselang, $userlang);

/// We are going to use textlib facilities
    $textlib = textlib_get_instance();
/// Convert the text
    $result = $textlib->convert($lessonanswers->response, $fromenc);

    $newlessonanswers = new object;
    $newlessonanswers->id = $recordid;
    $newlessonanswers->response = $result;
    update_record('lesson_answers',$newlessonanswers);
/// And finally, just return the converted field
    return $result;
}

function migrate2utf8_lesson_default_password($recordid){

    ///um

}


function migrate2utf8_lesson_pages_contents($recordid){
    global $CFG;

/// Some trivial checks
    if (empty($recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    $SQL = "SELECT l.course
           FROM {$CFG->prefix}lesson l,
                {$CFG->prefix}lesson_pages lp
           WHERE l.id = lp.lessonid
                 AND lp.id = $recordid";

    if (!$lessonpages = get_record_sql($SQL)) {
        log_the_problem_somewhere();
        return false;
    }

    if (!$lesson = get_record('lesson','id',$lessonpages->course)) {
        log_the_problem_somewhere();
        return false;
    }

    $sitelang   = $CFG->lang;
    $courselang = get_course_lang($lessonpages->course);  //Non existing!
    $userlang   = get_main_teacher_lang($lessonpages->course); //N.E.!!

    $fromenc = get_original_encoding($sitelang, $courselang, $userlang);

/// We are going to use textlib facilities
    $textlib = textlib_get_instance();
/// Convert the text
    $result = $textlib->convert($lessonpages->contents, $fromenc);

    $newlessonpages = new object;
    $newlessonpages->id = $recordid;
    $newlessonpages->contents = $result;
    update_record('lesson_answers',$newlessonpages);
/// And finally, just return the converted field
    return $result;
}

function migrate2utf8_lesson_pages_title($recordid){
    global $CFG;

/// Some trivial checks
    if (empty($recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    $SQL = "SELECT l.course
           FROM {$CFG->prefix}lesson l,
                {$CFG->prefix}lesson_pages lp
           WHERE l.id = lp.lessonid
                 AND lp.id = $recordid";

    if (!$lessonpages = get_record_sql($SQL)) {
        log_the_problem_somewhere();
        return false;
    }

    if (!$lesson = get_record('lesson','id',$lessonpages->course)) {
        log_the_problem_somewhere();
        return false;
    }

    $sitelang   = $CFG->lang;
    $courselang = get_course_lang($lessonpages->course);  //Non existing!
    $userlang   = get_main_teacher_lang($lessonpages->course); //N.E.!!

    $fromenc = get_original_encoding($sitelang, $courselang, $userlang);

/// We are going to use textlib facilities
    $textlib = textlib_get_instance();
/// Convert the text
    $result = $textlib->convert($lessonpages->title, $fromenc);

    $newlessonpages = new object;
    $newlessonpages->id = $recordid;
    $newlessonpages->title = $result;
    update_record('lesson_answers',$newlessonpages);
/// And finally, just return the converted field
    return $result;
}

function migrate2utf8_lesson_name($recordid){
    global $CFG;

/// Some trivial checks
    if (empty($recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    if (!$lesson = get_record('lesson','id',$recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    $sitelang   = $CFG->lang;
    $courselang = get_course_lang($lesson->course);  //Non existing!
    $userlang   = get_main_teacher_lang($lesson->course); //N.E.!!

    $fromenc = get_original_encoding($sitelang, $courselang, $userlang);

/// We are going to use textlib facilities
    $textlib = textlib_get_instance();
/// Convert the text
    $result = $textlib->convert($lesson->name, $fromenc);

    $newlesson = new object;
    $newlesson->id = $recordid;
    $newlesson->name = $result;
    update_record('lesson',$newlesson);
/// And finally, just return the converted field
    return $result;
}

function migrate2utf8_lesson_password($recordid){
///um
}
?>