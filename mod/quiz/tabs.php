<?php  // $Id$
/**
* Sets up the tabs used by the quiz pages for teachers.
*
* @version $Id$
* @license http://www.gnu.org/copyleft/gpl.html GNU Public License
* @package quiz
*/

/// This file to be included so we can assume config.php has already been included.

    if (empty($quiz)) {
        error('You cannot call this script in that way');
    }
    if (!isset($currenttab)) {
        $currenttab = '';
    }
    if (!isset($cm)) {
        $cm = get_coursemodule_from_instance('quiz', $quiz->id);
    }
    if (!isset($course)) {
        $course = get_record('course', 'id', $quiz->course);
    }

    //print_heading(format_string($quiz->name));

    $tabs = array();
    $row  = array();
    $inactive = array();

    $row[] = new tabobject('info', "$CFG->wwwroot/mod/quiz/view.php?q=$quiz->id", get_string('info', 'quiz'));
    $row[] = new tabobject('reports', "$CFG->wwwroot/mod/quiz/report.php?q=$quiz->id", get_string('results', 'quiz'));
    $row[] = new tabobject('preview', "$CFG->wwwroot/mod/quiz/attempt.php?q=$quiz->id", get_string('preview', 'quiz'));
    if (isteacheredit($course->id)) {
        $row[] = new tabobject('edit', "$CFG->wwwroot/mod/quiz/edit.php?quizid=$quiz->id", get_string('edit'));
    }

    $tabs[] = $row;

    if ($currenttab == 'reports' and isset($mode)) {
        $inactive[] = 'reports';
        $allreports = get_list_of_plugins("mod/quiz/report");
        $reportlist = array ('overview', 'regrade', 'grading', 'analysis');   // Standard reports we want to show first

        foreach ($allreports as $report) {
            if (!in_array($report, $reportlist)) {
                $reportlist[] = $report;
            }
        }

        $row  = array();
        $currenttab = '';
        foreach ($reportlist as $report) {
            $row[] = new tabobject($report, "$CFG->wwwroot/mod/quiz/report.php?q=$quiz->id&amp;mode=$report",
                                    get_string($report, 'quiz_'.$report));
            if ($report == $mode) {
                $currenttab = $report;
            }
        }
        $tabs[] = $row;
    }

    if ($currenttab == 'edit' and isset($mode)) {
        $inactive[] = 'edit';

        $row  = array();
        $currenttab = $mode;

        $strquizzes = get_string('modulenameplural', 'quiz');
        $strquiz = get_string('modulename', 'quiz');
        $streditingquestions = get_string('editquestions', "quiz");
        $streditingquiz = get_string("editinga", "moodle", $strquiz);
        $strupdate = get_string('updatethis', 'moodle', $strquiz);
        $row[] = new tabobject('editq', "$CFG->wwwroot/mod/quiz/edit.php?quizid=$quiz->id", $strquiz, $streditingquiz);
        $row[] = new tabobject('questions', "$CFG->wwwroot/question/edit.php?courseid=$course->id", get_string('questions', 'quiz'), $streditingquestions);
        $row[] = new tabobject('categories', "$CFG->wwwroot/question/category.php?id=$course->id", get_string('categories', 'quiz'), get_string('editqcats', 'quiz'));
        $row[] = new tabobject('import', "$CFG->wwwroot/question/import.php?course=$course->id", get_string('import', 'quiz'), get_string('importquestions', 'quiz'));
        $row[] = new tabobject('export', "$CFG->wwwroot/question/export.php?courseid=$course->id", get_string('export', 'quiz'), get_string('exportquestions', 'quiz'));

        $tabs[] = $row;
    }

    print_tabs($tabs, $currenttab, $inactive);

?>
