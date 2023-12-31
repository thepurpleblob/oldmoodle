<?php // $Id$
/**
* This page displays a preview of a question
*
* The preview uses the option settings from the activity within which the question
* is previewed or the default settings if no activity is specified. The question session
* information is stored in the session as an array of subsequent states rather
* than in the database.
*
* TODO: make this work with activities other than quiz
*
* @version $Id$
* @author Alex Smith as part of the Serving Mathematics project
*         {@link http://maths.york.ac.uk/serving_maths}
* @license http://www.gnu.org/copyleft/gpl.html GNU Public License
* @package question
*/

    require_once("../config.php");
    require_once($CFG->libdir.'/questionlib.php');
    require_once($CFG->dirroot.'/mod/quiz/locallib.php'); // We really want to get rid of this

    $id = required_param('id', PARAM_INT);        // question id
    // if no quiz id is specified then a dummy quiz with default options is used
    $quizid = optional_param('quizid', 0, PARAM_INT);
    // Test if we are continuing an attempt at a question
    $continue = optional_param('continue', 0, PARAM_BOOL);
    // Check for any of the submit buttons
    $fillcorrect = optional_param('fillcorrect', 0, PARAM_BOOL);
    $markall = optional_param('markall', 0, PARAM_BOOL);
    $finishattempt = optional_param('finishattempt', 0, PARAM_BOOL);
    $back = optional_param('back', 0, PARAM_BOOL);
    $startagain = optional_param('startagain', 0, PARAM_BOOL);
    // We are always continuing an attempt if a submit button was pressed with the
    // exception of the start again button
    if ($fillcorrect || $markall || $finishattempt || $back) {
        $continue = true;
    } else if ($startagain) {
        $continue = false;
    }

    require_login();

    if (!isteacherinanycourse()) {
        error('This page is for teachers only');
    }

    if (!$continue) {
        // Start a new attempt; delete the old session
        unset($SESSION->quizpreview);
        // Redirect to ourselves but with continue=1; prevents refreshing the page
        // from restarting an attempt (needed so that random questions don't change)
        $quizid = $quizid ? '&amp;quizid=' . $quizid : '';
        redirect($CFG->wwwroot . '/question/preview.php?id=' . $id . $quizid .
         '&amp;continue=1');
    }

    if (empty($quizid)) {
        $quiz = new cmoptions;
        $quiz->id = 0;
        $quiz->review = $CFG->quiz_review;

    } else if (!$quiz = get_record('quiz', 'id', $quizid)) {
        error("Quiz id $quizid does not exist");
    }

    // Load the question information
    if (!$questions = get_records('question', 'id', $id)) {
        error('Could not load question');
    }
    if ($maxgrade = get_field('quiz_question_instances', 'grade', 'quiz', $quiz->id, 'question', $id)) {
        $questions[$id]->maxgrade = $maxgrade;
    } else {
        $questions[$id]->maxgrade = $questions[$id]->defaultgrade;
    }

    $quiz->id = 0; // just for safety
    $quiz->questions = $id;

    if (!$category = get_record("question_categories", "id", $questions[$id]->category)) {
        error("This question doesn't belong to a valid category!");
    }

    if (!isteacher($category->course) and !$category->publish) {
        error("You can't preview these questions!");
    }
    $quiz->course = $category->course;

    // Load the question type specific information
    if (!get_question_options($questions)) {
        error(get_string('newattemptfail', 'quiz'));
    }

    // Create a dummy quiz attempt
    // TODO: find out what of the following we really need. What is $attempt
    //       really used for?
    $timenow = time();
    $attempt->quiz = $quiz->id;
    $attempt->userid = $USER->id;
    $attempt->attempt = 0;
    $attempt->sumgrades = 0;
    $attempt->timestart = $timenow;
    $attempt->timefinish = 0;
    $attempt->timemodified = $timenow;
    $attempt->uniqueid = 0;
    $attempt->id = 0;

    // Restore the history of question sessions from the moodle session or create
    // new sessions. Make $states a reference to the states array in the moodle
    // session.
    if (isset($SESSION->quizpreview->states) and $SESSION->quizpreview->questionid
     == $id) {
        // Reload the question session history from the moodle session
        $states =& $SESSION->quizpreview->states;
        $historylength = count($states) - 1;
        if ($back && $historylength > 0) {
            // Go back one step in the history
            unset($states[$historylength]);
            $historylength--;
        }
    } else {
        // Record the question id in the moodle session
        $SESSION->quizpreview->questionid = $id;
        // Create an empty session for the question
        if (!$newstates =
         get_question_states($questions, $quiz, $attempt)) {
            error(get_string('newattemptfail', 'quiz'));
        }
        $SESSION->quizpreview->states = array($newstates);
        $states =& $SESSION->quizpreview->states;
        $historylength = 0;
    }

    if (!$fillcorrect && !$back && ($form = data_submitted())) {
        $form = (array)$form;
        $submitted = true;

        // Create a new item in the history of question states (don't simplify!)
        $states[$historylength + 1] = array();
        $states[$historylength + 1][$id] = clone($states[$historylength][$id]);
        $historylength++;
        $curstate =& $states[$historylength][$id];
        $curstate->changed = false;

        // Process the responses
        unset($form['id']);
        unset($form['quizid']);
        unset($form['continue']);
        unset($form['markall']);
        unset($form['finishattempt']);
        unset($form['back']);
        unset($form['startagain']);

        $event = $finishattempt ? QUESTION_EVENTCLOSE : QUESTION_EVENTSUBMIT;
        if ($actions = question_extract_responses($questions, $form, $event)) {
            $actions[$id]->timestamp = 0; // We do not care about timelimits here
            question_process_responses($questions[$id], $curstate, $actions[$id], $quiz, $attempt);
            if (!$curstate->changed) {
                // Update the current state rather than creating a new one
                $historylength--;
                unset($states[$historylength]);
                $states = array_values($states);
                $curstate =& $states[$historylength][$id];
            }
        }
    } else {
        $submitted = false;
        $curstate =& $states[$historylength][$id];
    }

    // TODO: should not use quiz-specific function here
    $options = quiz_get_renderoptions($quiz->review, $curstate);

    // Fill in the correct responses (unless the question is in readonly mode)
    if ($fillcorrect && !$options->readonly) {
        $curstate->responses = $QTYPES[$questions[$id]->qtype]
         ->get_correct_responses($questions[$id], $curstate);
    }

    $strpreview = get_string('preview', 'quiz').' '.s($questions[$id]->name);
    print_header($strpreview);
    print_heading($strpreview);

    if (!empty($quizid)) {
        echo '<p class="quemodname">'.get_string('modulename', 'quiz') . ': ';
        p(format_string($quiz->name));
        echo "</p>\n";
    }
    $number = 1;
    echo "<form method=\"post\" action=\"preview.php\" autocomplete=\"off\">\n";
    echo "<input type=\"hidden\" name=\"id\" value=\"$id\" />\n";
    echo "<input type=\"hidden\" name=\"quizid\" value=\"$quizid\" />\n";
    echo "<input type=\"hidden\" name=\"continue\" value=\"1\" />\n";

    print_question($questions[$id], $curstate, $number, $quiz, $options);

    echo '<br />';
    echo '<center>';

    // Print the mark and finish attempt buttons
    echo '<input name="markall" type="submit" value="' . get_string('markall',
     'quiz') . "\" />\n";
    echo '<input name="finishattempt" type="submit" value="' .
     get_string('finishattempt', 'quiz') . "\" />\n";
    echo '<br />';
    echo '<br />';
    // Print the fill correct button (unless the question is in readonly mode)
    if (!$options->readonly) {
        echo '<input name="fillcorrect" type="submit" value="' .
         get_string('fillcorrect', 'quiz') . "\" />\n";
    }
    // Print the navigation buttons
    if ($historylength > 0) {
        echo '<input name="back" type="submit" value="' . get_string('previous',
         'quiz') . "\" />\n";
    }
    // Print the start again button
    echo '<input name="startagain" type="submit" value="' .
     get_string('startagain', 'quiz') . "\" />\n";
    // Print the close window button
    echo '<input type="button" onclick="window.close()" value="' .
     get_string('closepreview', 'quiz') . "\" />";
    echo '</center>';
    echo '</form>';
    print_footer();
?>
