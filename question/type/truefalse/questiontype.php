<?php  // $Id$

/////////////////
/// TRUEFALSE ///
/////////////////

/// QUESTION TYPE CLASS //////////////////
class question_truefalse_qtype extends default_questiontype {

    function name() {
        return 'truefalse';
    }

    function save_question_options($question) {
        
        // fetch old answer ids so that we can reuse them
        if (!$oldanswers = get_records("question_answers", "question", $question->id, "id ASC")) {
            $oldanswers = array();
        }

        // Save answer 'True'
        if ($true = array_shift($oldanswers)) {  // Existing answer, so reuse it
            $true->answer   = get_string("true", "quiz");
            $true->fraction = $question->answer;
            $true->feedback = $question->feedbacktrue;
            if (!update_record("question_answers", $true)) {
                $result->error = "Could not update quiz answer \"true\")!";
                return $result;
            }
        } else {
            unset($true);
            $true->answer   = get_string("true", "quiz");
            $true->question = $question->id;
            $true->fraction = $question->answer;
            $true->feedback = $question->feedbacktrue;
            if (!$true->id = insert_record("question_answers", $true)) {
                $result->error = "Could not insert quiz answer \"true\")!";
                return $result;
            }
        }

        // Save answer 'False'
        if ($false = array_shift($oldanswers)) {  // Existing answer, so reuse it
            $false->answer   = get_string("false", "quiz");
            $false->fraction = 1 - (int)$question->answer;
            $false->feedback = $question->feedbackfalse;
            if (!update_record("question_answers", $false)) {
                $result->error = "Could not insert quiz answer \"false\")!";
                return $result;
            }
        } else {
            unset($false);
            $false->answer   = get_string("false", "quiz");
            $false->question = $question->id;
            $false->fraction = 1 - (int)$question->answer;
            $false->feedback = $question->feedbackfalse;
            if (!$false->id = insert_record("question_answers", $false)) {
                $result->error = "Could not insert quiz answer \"false\")!";
                return $result;
            }
        }

        // delete any leftover old answer records (there couldn't really be any, but who knows)
        if (!empty($oldanswers)) {
            foreach($oldanswers as $oa) {
                delete_records('question_answers', 'id', $oa->id);
            }
        }

        // Save question options in question_truefalse table
        if ($options = get_record("question_truefalse", "question", $question->id)) {
            // No need to do anything, since the answer IDs won't have changed
            // But we'll do it anyway, just for robustness
            $options->trueanswer  = $true->id;
            $options->falseanswer = $false->id;
            if (!update_record("question_truefalse", $options)) {
                $result->error = "Could not update quiz truefalse options! (id=$options->id)";
                return $result;
            }
        } else {
            unset($options);
            $options->question    = $question->id;
            $options->trueanswer  = $true->id;
            $options->falseanswer = $false->id;
            if (!insert_record("question_truefalse", $options)) {
                $result->error = "Could not insert quiz truefalse options!";
                return $result;
            }
        }
        return true;
    }

    /**
    * Loads the question type specific options for the question.
    */
    function get_question_options(&$question) {
        // Get additional information from database
        // and attach it to the question object
        if (!$question->options = get_record('question_truefalse', 'question', $question->id)) {
            notify('Error: Missing question options!');
            return false;
        }
        // Load the answers
        if (!$question->options->answers = get_records('question_answers', 'question', $question->id)) {
           notify('Error: Missing question answers!');
           return false;
        }

        return true;
    }

    /**
    * Deletes question from the question-type specific tables
    *
    * @return boolean Success/Failure
    * @param object $question  The question being deleted
    */
    function delete_question($questionid) {
        delete_records("question_truefalse", "question", $questionid);
        return true;
    }

    function get_correct_responses(&$question, &$state) {
        // The correct answer is the one which gives full marks
        foreach ($question->options->answers as $answer) {
            if (((int) $answer->fraction) === 1) {
                return array('' => $answer->id);
            }
        }
        return null;
    }

    /**
    * Prints the main content of the question including any interactions
    */
    function print_question_formulation_and_controls(&$question, &$state,
            $cmoptions, $options) {
        global $CFG;

        $readonly = $options->readonly ? ' readonly="readonly"' : '';
        $formatoptions->noclean = true;
        $formatoptions->para = false;

        // Print question formulation
        $questiontext = format_text($question->questiontext,
                         $question->questiontextformat,
                         $formatoptions, $cmoptions->course);
        $image = get_question_image($question, $cmoptions->course);

        $answers = &$question->options->answers;
        $trueanswer = &$answers[$question->options->trueanswer];
        $falseanswer = &$answers[$question->options->falseanswer];
        $correctanswer = ($trueanswer->fraction == 1) ? $trueanswer : $falseanswer;

        // Work out which radio button to select (if any)
        $truechecked = ($state->responses[''] == $trueanswer->id) ? ' checked="checked"' : '';
        $falsechecked = ($state->responses[''] == $falseanswer->id) ? ' checked="checked"' : '';

        // Work out which answer is correct if we need to highlight it
        if ($options->correct_responses) {
            $trueclass = ($trueanswer->fraction) ? ' class="highlight"' : '';
            $falseclass = ($falseanswer->fraction) ? ' class="highlight"' : '';
        } else {
            $trueclass = '';
            $falseclass = '';
        }

        $inputname = ' name="'.$question->name_prefix.'" ';
        $trueid    = $question->name_prefix.'true';
        $falseid   = $question->name_prefix.'false';

        $radiotrue = '<input type="radio"' . $truechecked . $readonly . $inputname
            . 'id="'.$trueid . '" value="' . $trueanswer->id . '" alt="'
            . s($trueanswer->answer) . '" /><label for="'.$trueid . '">'
            . s($trueanswer->answer) . '</label>';
        $radiofalse = '<input type="radio"' . $falsechecked . $readonly . $inputname
            . 'id="'.$falseid . '" value="' . $falseanswer->id . '" alt="'
            . s($falseanswer->answer) . '" /><label for="'.$falseid . '">'
            . s($falseanswer->answer) . '</label>';

        $feedback = '';
        if ($options->feedback and isset($answers[$state->responses['']])) {
            $chosenanswer = $answers[$state->responses['']];
            $feedback = format_text($chosenanswer->feedback, true, $formatoptions, $cmoptions->course);
        }
        
        include("$CFG->dirroot/question/type/truefalse/display.html");
    }

    function grade_responses(&$question, &$state, $cmoptions) {
        if (isset($question->options->answers[$state->responses['']])) {
            $state->raw_grade = $question->options->answers[$state->responses['']]->fraction * $question->maxgrade;
        } else {
            $state->raw_grade = 0;
        }
        // Only allow one attempt at the question
        $state->penalty = 1 * $question->maxgrade;

        // mark the state as graded
        $state->event = ($state->event ==  QUESTION_EVENTCLOSE) ? QUESTION_EVENTCLOSEANDGRADE : QUESTION_EVENTGRADE;

        return true;
    }

    function response_summary($question, $state, $length=80) {
        if (isset($question->options->answers[$state->answer])) {
            $responses = $question->options->answers[$state->answer]->answer;
        } else {
            $responses = '';
        }
        return $responses;
    }

    function get_actual_response($question, $state) {
        if (isset($question->options->answers[$state->responses['']])) {
            $responses[] = $question->options->answers[$state->responses['']]->answer;
        } else {
            $responses[] = '';
        }
        return $responses;
    }
    
/// BACKUP FUNCTIONS ////////////////////////////

    /*
     * Backup the data in a truefalse question
     *
     * This is used in question/backuplib.php
     */
    function backup($bf,$preferences,$question,$level=6) {

        $status = true;

        $truefalses = get_records("question_truefalse","question",$question,"id");
        //If there are truefalses
        if ($truefalses) {
            //Iterate over each truefalse
            foreach ($truefalses as $truefalse) {
                $status = fwrite ($bf,start_tag("TRUEFALSE",$level,true));
                //Print truefalse contents
                fwrite ($bf,full_tag("TRUEANSWER",$level+1,false,$truefalse->trueanswer));
                fwrite ($bf,full_tag("FALSEANSWER",$level+1,false,$truefalse->falseanswer));
                $status = fwrite ($bf,end_tag("TRUEFALSE",$level,true));
            }
            //Now print question_answers
            $status = question_backup_answers($bf,$preferences,$question);
        }
        return $status;
    }

/// RESTORE FUNCTIONS /////////////////

    /*
     * Restores the data in the question
     *
     * This is used in question/restorelib.php
     */
    function restore($old_question_id,$new_question_id,$info,$restore) {

        $status = true;

        //Get the truefalse array
        $truefalses = $info['#']['TRUEFALSE'];

        //Iterate over truefalse
        for($i = 0; $i < sizeof($truefalses); $i++) {
            $tru_info = $truefalses[$i];

            //Now, build the question_truefalse record structure
            $truefalse->question = $new_question_id;
            $truefalse->trueanswer = backup_todb($tru_info['#']['TRUEANSWER']['0']['#']);
            $truefalse->falseanswer = backup_todb($tru_info['#']['FALSEANSWER']['0']['#']);

            ////We have to recode the trueanswer field
            $answer = backup_getid($restore->backup_unique_code,"question_answers",$truefalse->trueanswer);
            if ($answer) {
                $truefalse->trueanswer = $answer->new_id;
            }

            ////We have to recode the falseanswer field
            $answer = backup_getid($restore->backup_unique_code,"question_answers",$truefalse->falseanswer);
            if ($answer) {
                $truefalse->falseanswer = $answer->new_id;
            }

            //The structure is equal to the db, so insert the question_truefalse
            $newid = insert_record ("question_truefalse",$truefalse);

            //Do some output
            if (($i+1) % 50 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 1000 == 0) {
                        echo "<br />";
                    }
                }
                backup_flush(300);
            }

            if (!$newid) {
                $status = false;
            }
        }

        return $status;
    }

    function restore_recode_answer($state, $restore) {
        //answer may be empty
        if ($state->answer) {
            $answer = backup_getid($restore->backup_unique_code,"question_answers",$state->answer);
            if ($answer) {
                return $answer->new_id;
            } else {
                echo 'Could not recode truefalse answer id '.$state->answer.' for state '.$state->oldid.'<br />';
            }
        }
    }

}
//// END OF CLASS ////

//////////////////////////////////////////////////////////////////////////
//// INITIATION - Without this line the question type is not in use... ///
//////////////////////////////////////////////////////////////////////////
$QTYPES['truefalse']= new question_truefalse_qtype();
// The following adds the questiontype to the menu of types shown to teachers
$QTYPE_MENU['truefalse'] = get_string("truefalse", "quiz");

?>
