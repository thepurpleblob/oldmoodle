<?php // $Id$

    if (!empty($question->id)) {
        $options = get_record("question_match", "question", $question->id);
        if (!empty($options->subquestions)) {
            $oldsubquestions = get_records_list("question_match_sub", "id", $options->subquestions);
        }
    } else {
        $options->shuffleanswers = 1;
    }

    $subquestions = array();
    $subanswers   = array();

    if (!empty($oldsubquestions)) {
        foreach ($oldsubquestions as $oldsubquestion) {
            $subquestions[] = $oldsubquestion->questiontext;   // insert questions into slots
            $subanswers[] = $oldsubquestion->answertext;       // insert answers into slots
        }
    }

    $i = count($subquestions);
    $limit = QUESTION_NUMANS;
    $limit = $limit <= $i ? $i+1 : $limit;
    for (; $i < $limit; $i++) {
        $subquestions[] = "";   // Make question slots, default as blank
        $subanswers[] = "";     // Make answer slots, default as blank
    }

    $yesnooptions = array();
    $yesnooptions[0] = get_string("no");
    $yesnooptions[1] = get_string("yes");

    print_heading_with_help(get_string("editingmatch", "quiz"), "match", "quiz");
    require("$CFG->dirroot/question/type/match/editquestion.html");

?>
