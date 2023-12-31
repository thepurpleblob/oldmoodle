<?php // $Id$

    if (!empty($question->id)) {
        $options = get_record("question_truefalse", "question", "$question->id");
    }
    if (!empty($options->trueanswer)) {
        $true    = get_record("question_answers", "id", $options->trueanswer);
    } else {
        $true->fraction = 1;
        $true->feedback = "";
    }
    if (!empty($options->falseanswer)) {
        $false   = get_record("question_answers", "id", "$options->falseanswer");
    } else {
        $false->fraction = 0;
        $false->feedback = "";
    }

    if ($true->fraction > $false->fraction) {
        $question->answer = 1;
    } else {
        $question->answer = 0;
    }

    print_heading_with_help(get_string("editingtruefalse", "quiz"), "truefalse", "quiz");
    require("$CFG->dirroot/question/type/truefalse/editquestion.html");

?>
