<?php  // $Id$
/**
* This page prints a review of a particular question attempt
*
* @version $Id$
* @license http://www.gnu.org/copyleft/gpl.html GNU Public License
* @package quiz
*/

    require_once('../../config.php');
    require_once('locallib.php');

    $attemptid =required_param('attempt', PARAM_INT); // attempt id
    $questionid =required_param('question', PARAM_INT); // question id

    if (! $attempt = get_record('quiz_attempts', 'uniqueid', $attemptid)) {
        error('No such attempt ID exists');
    }
    if (! $quiz = get_record('quiz', 'id', $attempt->quiz)) {
        error('Course module is incorrect');
    }
    if (! $course = get_record('course', 'id', $quiz->course)) {
        error('Course is misconfigured');
    }

    // Teachers are only allowed to comment and grade on closed attempts
    if (!($attempt->timefinish > 0)) {
        error('Attempt has not closed yet');
    }

    require_login($course->id);

    if (!isteacher($course->id)) {
        error('This page is for teachers only');
    }

    // Load question
    if (! $question = get_record('question', 'id', $questionid)) {
        error('Question for this session is missing');
    }
    $question->maxgrade = get_field('quiz_question_instances', 'grade', 'quiz', $quiz->id, 'question', $question->id);
    // Some of the questions code is optimised to work with several questions
    // at once so it wants the question to be in an array. 
    $key = $question->id;
    $questions[$key] = &$question;
    // Add additional questiontype specific information to the question objects.
    if (!get_question_options($questions)) {
        error("Unable to load questiontype specific question information");
    }

    // Load state
    $states = get_question_states($questions, $quiz, $attempt);
    // The $states array is indexed by question id but because we are dealing
    // with only one question there is only one entry in this array
    $state = &$states[$question->id];

    print_header();
    print_heading(format_string($question->name));
    
    //add_to_log($course->id, 'quiz', 'review', "review.php?id=$cm->id&amp;attempt=$attempt->id", "$quiz->id", "$cm->id");

    if ($data = data_submitted() and confirm_sesskey()) {
        // the following will update the state and attempt
        question_process_comment($question, $state, $attempt, $data->response['comment'], $data->response['grade']);
        // If the state has changed save it and update the quiz grade
        if ($state->changed) {
            save_question_session($question, $state);
            quiz_save_best_grade($quiz, $attempt->userid);
        }

        notify(get_string('changessaved'));
        echo '<center><input type="button" onclick="window.opener.location.reload(1); self.close();return false;" value="' .
         get_string('closewindow') . "\" /></center>";
         
        print_footer();
        exit;
    }

    question_print_comment_box($question, $state, $attempt, $CFG->wwwroot.'/mod/quiz/comment.php');

    print_footer();

?>
