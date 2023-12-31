<?php  // $Id$

/////////////////
/// CALCULATED ///
/////////////////

/// QUESTION TYPE CLASS //////////////////

require_once("$CFG->dirroot/question/type/datasetdependent/abstractqtype.php");

class question_calculated_qtype extends question_dataset_dependent_questiontype {

    // Used by the function custom_generator_tools:
    var $calcgenerateidhasbeenadded = false;

    function name() {
        return 'calculated';
    }

    function get_question_options(&$question) {
        // First get the datasets and default options
         global $CFG;
        if (!$question->options->answers = get_records_sql(
                                "SELECT a.*, c.tolerance, c.tolerancetype, c.correctanswerlength, c.correctanswerformat " .
                                "FROM {$CFG->prefix}question_answers a, " .
                                "     {$CFG->prefix}question_calculated c " .
                                "WHERE a.question = $question->id " .
                                "AND   a.id = c.answer ".
                                "ORDER BY a.id ASC")) {
            notify('Error: Missing question answer!');
            return false;
        }

/*
        if(false === parent::get_question_options($question)) {
            return false;
        }

        if (!$options = get_record('question_calculated', 'question', $question->id)) {
            notify("No options were found for calculated question
             #{$question->id}! Proceeding with defaults.");
            $options = new stdClass;
            $options->tolerance           = 0.01;
            $options->tolerancetype       = 1; // relative
            $options->correctanswerlength = 2;
            $options->correctanswerformat = 1; // decimals
        }

        // For historic reasons we also need these fields in the answer objects.
        // This should eventually be removed and related code changed to use
        // the values in $question->options instead.
        foreach ($question->options->answers as $key => $answer) {
            $answer = &$question->options->answers[$key]; // for PHP 4.x
            $answer->calcid              = $options->id;
            $answer->tolerance           = $options->tolerance;
            $answer->tolerancetype       = $options->tolerancetype;
            $answer->correctanswerlength = $options->correctanswerlength;
            $answer->correctanswerformat = $options->correctanswerformat;
        }*/

        $virtualqtype = $this->get_virtual_qtype();
        $virtualqtype->get_numerical_units($question);

        if( isset($question->export_process)&&$question->export_process){
            $question->options->datasets = $this->get_datasets_for_export($question);
        } 
  
        return true;
    }

    function get_datasets_for_export(&$question){
        $datasetdefs = array();
        if (!empty($question->id)) {
            global $CFG;
            $sql = "SELECT i.*
                    FROM {$CFG->prefix}question_datasets d,
                         {$CFG->prefix}question_dataset_definitions i
                    WHERE d.question = '$question->id'
                    AND   d.datasetdefinition = i.id
                   ";
            if ($records = get_records_sql($sql)) {                
                foreach ($records as $r) {
                    $def = $r ;
                    if ($def->category=='0'){
                        $def->status='private';
                    } else {   
                        $def->status='shared';
                    }   
                    $def->type ='calculated' ;
                    list($distribution, $min, $max,$dec) = explode(':', $def->options, 4);
                    $def->distribution=$distribution;
                    $def->minimum=$min;
                    $def->maximum=$max;
                    $def->decimals=$dec ;                                         
                     if ($def->itemcount > 0 ) {
                        // get the datasetitems
                        $def->items = array();
                        $sql1= (" SELECT number, definition, id, value
                        FROM {$CFG->prefix}question_dataset_items 
                        WHERE definition = '$def->id' order by number ASC ");
                        if ($items = get_records_sql($sql1)){
                            $n = 0;
                            foreach( $items as $ii){
                                $n++;
                                $def->items[$n] = new stdClass;
                                $def->items[$n]->itemnumber=$ii->number;
                                $def->items[$n]->value=$ii->value;
                           }
                           $def->number_of_items=$n ;
                        }
                    }
                    $datasetdefs["1-$r->category-$r->name"] = $def;                                                                                               
                }
            }
        }
        return $datasetdefs ;
    } 
      
    function save_question_options($question) {
        //$options = $question->subtypeoptions;
        // Get old answers:
        global $CFG;
        // Get old versions of the objects
        if (!$oldanswers = get_records('question_answers', 'question', $question->id, 'id ASC')) {
            $oldanswers = array();
        }

        if (!$oldoptions = get_records('question_calculated', 'question', $question->id, 'answer ASC')) {
            $oldoptions = array();
                }
                // Save the units.
        // Save units
        $virtualqtype = $this->get_virtual_qtype();
        $result = $virtualqtype->save_numerical_units($question);
        if (isset($result->error)) {
            return $result;
        } else {
            $units = &$result->units;
        }
        // Insert all the new answers
        foreach ($question->answers as $key => $dataanswer) {
            if (  trim($dataanswer) != '' ) { 
                $answer = new stdClass;
                $answer->question = $question->id;
                $answer->answer = trim($dataanswer);
                $answer->fraction = $question->fraction[$key];
                $answer->feedback = trim($question->feedback[$key]);
                if ($oldanswer = array_shift($oldanswers)) {  // Existing answer, so reuse it
                    $answer->id = $oldanswer->id;
                    if (! update_record("question_answers", $answer)) {
                        $result->error = "Could not update question answer! (id=$answer->id)";
                        return $result;
                    }
                } else { // This is a completely new answer
                    if (! $answer->id = insert_record("question_answers", $answer)) {
                        $result->error = "Could not insert question answer!";
                        return $result;
            }
        }

                // Set up the options object
                if (!$options = array_shift($oldoptions)) {
                    $options = new stdClass;
                }
                $options->question  = $question->id;
                $options->answer    = $answer->id;
                $options->tolerance = trim($question->tolerance[$key]);
                $options->tolerancetype  = trim($question->tolerancetype[$key]);
                $options->correctanswerlength  = trim($question->correctanswerlength[$key]);
                $options->correctanswerformat  = trim($question->correctanswerformat[$key]);
                
                // Save options
                if (isset($options->id)) { // reusing existing record
                    if (! update_record('question_calculated', $options)) {
                        $result->error = "Could not update question calculated options! (id=$options->id)";
                        return $result;
                    }
                } else { // new options
                    if (! insert_record('question_calculated', $options)) {
                        $result->error = "Could not insert question  calculated options!";
                        return $result;
                    }
                }
            }
        }
        // delete old answer records
        if (!empty($oldanswers)) {
            foreach($oldanswers as $oa) {
                delete_records('question_answers', 'id', $oa->id);
            }
            }
        // delete old answer records
        if (!empty($oldoptions)) {
            foreach($oldoptions as $oo) {
                delete_records('question_calculated', 'id', $oo->id);
            }
        }
        // validating data but not on import
       if( !isset($question->import_process)){
            $mandatorydatasets = array();
                foreach ($question->answers as $answer) {
                    $mandatorydatasets += $this->find_dataset_names($answer);
                }
            // do not proceed further if no mandatorydataset    
            if (count($mandatorydatasets) < 1) { // check there are at lest 2 answers for multiple choice
                $result->notice = get_string('atleastonewildcard', 'qtype_datasetdependent');//get_string("notenoughanswers", "qtype_multichoice", "2");
                return $result;
            }  
            $calculatedmessages = array();
                        //evaluate the equations i.e {=5+4) eval(  
          $qtext = "";
         $qtextremaining = $question->questiontext ;
         $possibledatasets = $this->find_dataset_names($question->questiontext);
            foreach ($possibledatasets as $name => $value) {
            $qtextremaining = str_replace('{'.$name.'}', '1', $qtextremaining);
        }
    //     echo "numericalquestion qtextremaining <pre>";print_r($possibledatasets); 

//         echo "test<pre>";print_r($numericalquestion);
           while  (ereg('\{=([^[:space:]}]*)}', $qtextremaining, $regs1)) {
             $qtextsplits = explode($regs1[0], $qtextremaining, 2);
            $qtext =$qtext.$qtextsplits[0];
            $qtextremaining = $qtextsplits[1];
             // echo "testsplit0".$qtextsplits[0]."<br/>";
             // echo "test".$regs1[0]."<br/>";            
             // echo "qtext".$qtext."<br/>";       
            //    echo "test<pre>";print_r($regs1);
            if (empty($regs1[1])) {
                    $str = '';
                } else {
                    // could put here an equation verification
                     if ($formulaerrors =
                     qtype_calculated_find_formula_errors($regs1[1])) {
                        $calculatedmessages[] = $formulaerrors;
                        $str = '';
                    }                  
                }
            } 
            // import from 1.8 to be tested
            $answercount=0 ;
        $maxgrade = false;
        $mandatorydatasets = array();
        foreach ($question->answers as $key => $answer){
            $mandatorydatasets += $this->find_dataset_names($answer);
        }      
        if ( count($mandatorydatasets )==0){
          //  $errors['questiontext']=get_string('atleastonewildcard', 'qtype_datasetdependent');
 //           foreach ($answers as $key => $answer){
               $calculatedmessages[] = get_string('atleastonewildcard', 'qtype_datasetdependent');
//            }      
        }  
        foreach ($question->answers as $key => $answer){
            //check no of choices
            // the * for everykind of answer not actually implemented
            $trimmedanswer = trim($answer);
            if (($trimmedanswer!='')||$answercount==0){
                $eqerror = qtype_calculated_find_formula_errors($trimmedanswer);
                if (FALSE !== $eqerror){
                    $calculatedmessages[] = $eqerror.":".$answer;
                }
            }
            
            if ($trimmedanswer!=''){
            /*    if ('2' == $data['correctanswerformat'][$key]
                        && '0' == $data['correctanswerlength'][$key]) {
                    $errors['correctanswerlength['.$key.']'] = get_string('zerosignificantfiguresnotallowed','quiz');
                }
                if (!is_numeric($data['tolerance'][$key])){
                    $errors['tolerance['.$key.']'] = get_string('mustbenumeric', 'qtype_calculated');
                }
                if ($data['fraction'][$key] == 1) {
                   $maxgrade = true;
                }
*/
                $answercount++;
            }
        } 
        if ($answercount == 0) {
            $calculatedmessages[]=get_string('atleastoneanswer', 'qtype_calculated');
        }
          /*              $calculatedmessages = array();
                if (empty($form->name)) {
                    $calculatedmessages[] = get_string('missingname', 'quiz');
                }
                if (empty($form->questiontext)) {
                    $calculatedmessages[] = get_string('missingquestiontext', 'quiz');
                }
                // Verify formulas
                foreach ($form->answers as $key => $answer) {
                    if ('' === trim($answer)) {
                        $calculatedmessages[] =
                            get_string('missingformula', 'quiz');
                    }
                    if ($formulaerrors =
                     qtype_calculated_find_formula_errors($answer)) {
                        $calculatedmessages[] = $formulaerrors;
                    }
                    if (! isset($form->tolerance[$key])) {
                        $form->tolerance[$key] = 0.0;
                    }
                    if (! is_numeric($form->tolerance[$key])) {
                        $calculatedmessages[] =
                            get_string('tolerancemustbenumeric', 'quiz');
                    }
                }

                if (!empty($calculatedmessages)) {
                    $errorstring = "The following errors were found:<br />";
                    foreach ($calculatedmessages as $msg) {
                        $errorstring .= $msg . '<br />';
                    }
                    error($errorstring);
                }
      */

        }
        if( isset($question->import_process)&&$question->import_process){
            $this->import_datasets($question);
         }   
            if (!empty($calculatedmessages)) {
                    $errorstring = "The following errors were found:<br />";
                    foreach ($calculatedmessages as $msg) {
                        $errorstring .= $msg . '<br />';
                    }
                    $result->notice=$errorstring;
                }
  
        // Report any problems.
        if (!empty($result->notice)) {
            return $result;
        }
        return true;
    }

    function import_datasets($question){
        $n = count($question->dataset);
        foreach ($question->dataset as $dataset) {
            // name, type, option, 
            $datasetdef = new stdClass();
            $datasetdef->name = $dataset->name;
            $datasetdef->type = 1 ;
            $datasetdef->options =  $dataset->distribution.':'.$dataset->min.':'.$dataset->max.':'.$dataset->length;
            $datasetdef->itemcount=$dataset->itemcount;                       
            if ( $dataset->status =='private'){
                $datasetdef->category = 0;
                $todo='create' ;
            }else if ($dataset->status =='shared' ){
                if ($sharedatasetdefs = get_records_select(
                        'question_dataset_definitions',
                        "type = '1'
                        AND name = '$dataset->name'
                        AND category = '$question->category'
                        ORDER BY id DESC;"
                       )) { // so there is at least one
                    $sharedatasetdef = array_shift($sharedatasetdefs);
                    if ( $sharedatasetdef->options ==  $datasetdef->options ){// identical so use it
                        $todo='useit' ;
                        $datasetdef =$sharedatasetdef ;
                    } else { // different so create a private one
                        $datasetdef->category = 0;
                        $todo='create' ;
                    }    
                }else { // no so create one
                    $datasetdef->category =$question->category ;
                    $todo='create' ;
               }     
            }   
            if (  $todo=='create'){
                if (!$datasetdef->id = insert_record(
                    'question_dataset_definitions', $datasetdef)) {
                    error("Unable to create dataset $defid");
                } 
           }  
           // Create relation to the dataset:
           $questiondataset = new stdClass;
           $questiondataset->question = $question->id;
           $questiondataset->datasetdefinition = $datasetdef->id;
            if (!insert_record('question_datasets',
                               $questiondataset)) {
                error("Unable to create relation to dataset $dataset->name $todo");
            }
            if ($todo=='create'){ // add the items
                foreach ($dataset->datasetitem as $dataitem ){
                    $datasetitem = new stdClass;
                    $datasetitem->definition=$datasetdef->id ;
                    $datasetitem->number = $dataitem->itemnumber ;
                    $datasetitem->value = $dataitem->value ;
                    if (!insert_record('question_dataset_items', $datasetitem)) {
                        error("Unable to insert dataset item $datasetitem->number with $item->value for $datasetdef->name");
                    }
                }     
            }                                
        }
    }
         
    function create_runtime_question($question, $form) {
        $question = parent::create_runtime_question($question, $form);
        $question->options->answers = array();
        foreach ($form->answers as $key => $answer) {
            $a->answer              = trim($form->answer[$key]);
            $a->fraction              = $form->fraction[$key];//new
            $a->tolerance           = $form->tolerance[$key];
            $a->tolerancetype       = $form->tolerancetype[$key];
            $a->correctanswerlength = $form->correctanswerlength[$key];
            $a->correctanswerformat = $form->correctanswerformat[$key];
            $question->options->answers[] = clone($a);
        }

        return $question;
    }

    function validate_form($form) {
        switch($form->wizardpage) {
            case 'question':
                $calculatedmessages = array();
                if (empty($form->name)) {
                    $calculatedmessages[] = get_string('missingname', 'quiz');
                }
                if (empty($form->questiontext)) {
                    $calculatedmessages[] = get_string('missingquestiontext', 'quiz');
                }
                // Verify formulas
                foreach ($form->answers as $key => $answer) {
                    if ('' === trim($answer)) {
                        $calculatedmessages[] =
                            get_string('missingformula', 'quiz');
                    }
                    if ($formulaerrors =
                     qtype_calculated_find_formula_errors($answer)) {
                        $calculatedmessages[] = $formulaerrors;
                    }
                    if (! isset($form->tolerance[$key])) {
                        $form->tolerance[$key] = 0.0;
                    }
                    if (! is_numeric($form->tolerance[$key])) {
                        $calculatedmessages[] =
                            get_string('tolerancemustbenumeric', 'quiz');
                    }
                }

                if (!empty($calculatedmessages)) {
                    $errorstring = "The following errors were found:<br />";
                    foreach ($calculatedmessages as $msg) {
                        $errorstring .= $msg . '<br />';
                    }
                    error($errorstring);
                }

                break;
            default:
                return parent::validate_form($form);
                break;
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
        delete_records("question_calculated", "question", $questionid);
        delete_records("question_numerical_units", "question", $questionid);
        if ($datasets = get_records('question_datasets', 'question', $questionid)) {
            foreach ($datasets as $dataset) {
                if (! get_records_select(
                        'question_datasets',
                        "question != $questionid
                        AND datasetdefinition = $dataset->datasetdefinition;")){                                 
                    delete_records('question_dataset_definitions', 'id', $dataset->datasetdefinition);
                    delete_records('question_dataset_items', 'definition', $dataset->datasetdefinition);
                }
            }
        }
        delete_records("question_datasets", "question", $questionid);
        return true;
    }

    function print_question_formulation_and_controls(&$question, &$state, $cmoptions, $options) {
        // Substitute variables in questiontext before giving the data to the
        // virtual type for printing
        $virtualqtype = $this->get_virtual_qtype();
        $unit = $virtualqtype->get_default_numerical_unit($question);

        // We modify the question to look like a numerical question
        $numericalquestion = fullclone($question);
        foreach ($numericalquestion->options->answers as $key => $answer) {
          $answer = fullclone($numericalquestion->options->answers[$key]);
            $correctanswer = qtype_calculated_calculate_answer(
                 $answer->answer, $state->options->dataset, $answer->tolerance,
                 $answer->tolerancetype, $answer->correctanswerlength,
                 $answer->correctanswerformat, $unit->unit);
           $numericalquestion->options->answers[$key]->answer = $correctanswer->answer;
        }
        $numericalquestion->questiontext = parent::substitute_variables(
         $numericalquestion->questiontext, $state->options->dataset);
        //evaluate the equations i.e {=5+4)   
          $qtext = "";
         $qtextremaining = $numericalquestion->questiontext ;
           while  (ereg('\{=([^[:space:]}]*)}', $qtextremaining, $regs1)) {
             $qtextsplits = explode($regs1[0], $qtextremaining, 2);
            $qtext =$qtext.$qtextsplits[0];
            $qtextremaining = $qtextsplits[1];
            if (empty($regs1[1])) {
                    $str = '';
                } else {
                    if( $formulaerrors = qtype_calculated_find_formula_errors($regs1[1])){
                        $str=$formulaerrors ;
                    }else {                    
                        eval('$str = '.$regs1[1].';');
                    }
                }
                $qtext = $qtext.$str ; 
            } 
             $numericalquestion->questiontext = $qtext.$qtextremaining ; // end replace equations
        $virtualqtype->print_question_formulation_and_controls($numericalquestion, $state, $cmoptions, $options);
    }

    function grade_responses(&$question, &$state, $cmoptions) {
        // Forward the grading to the virtual qtype

        // We modify the question to look like a numerical question
        $numericalquestion = fullclone($question);
        foreach ($numericalquestion->options->answers as $key => $answer) {
            $answer = $numericalquestion->options->answers[$key]->answer; // for PHP 4.x
          $numericalquestion->options->answers[$key]->answer = $this->substitute_variables($answer,
             $state->options->dataset);
        }
         $virtualqtype = $this->get_virtual_qtype();
        return $virtualqtype->grade_responses($numericalquestion, $state, $cmoptions) ;
    }

    function response_summary($question, $state, $length=80) {
        // The actual response is the bit after the hyphen
        return substr($state->answer, strpos($state->answer, '-')+1, $length);
    }

    // ULPGC ecastro
    function check_response(&$question, &$state) {
        // Forward the checking to the virtual qtype
        // We modify the question to look like a numerical question
        $numericalquestion = clone($question);
        $numericalquestion->options = clone($question->options);
        foreach ($question->options->answers as $key => $answer) {
            $numericalquestion->options->answers[$key] = clone($answer);
        }
        foreach ($numericalquestion->options->answers as $key => $answer) {
            $answer = &$numericalquestion->options->answers[$key]; // for PHP 4.x
            $answer->answer = $this->substitute_variables($answer->answer,
             $state->options->dataset);
        }
        $virtualqtype = $this->get_virtual_qtype();
        return $virtualqtype->check_response($numericalquestion, $state) ;
    }

    // ULPGC ecastro
    function get_actual_response(&$question, &$state) {
        // Substitute variables in questiontext before giving the data to the
        // virtual type
        $virtualqtype = $this->get_virtual_qtype();
        $unit = $virtualqtype->get_default_numerical_unit($question);

        // We modify the question to look like a numerical question
        $numericalquestion = clone($question);
        $numericalquestion->options = clone($question->options);
        foreach ($question->options->answers as $key => $answer) {
            $numericalquestion->options->answers[$key] = clone($answer);
        }
        foreach ($numericalquestion->options->answers as $key => $answer) {
            $answer = &$numericalquestion->options->answers[$key]; // for PHP 4.x
            $answer->answer = $this->substitute_variables($answer->answer,
             $state->options->dataset);
            // apply_unit
        }
        $numericalquestion->questiontext = $this->substitute_variables(
                                  $numericalquestion->questiontext, $state->options->dataset);
        $responses = $virtualqtype->get_all_responses($numericalquestion, $state);
        $response = reset($responses->responses);
        $correct = $response->answer.' : ';

        $responses = $virtualqtype->get_actual_response($numericalquestion, $state);

        foreach ($responses as $key=>$response){
            $responses[$key] = $correct.$response;
        }

        return $responses;
    }

    function create_virtual_qtype() {
        global $CFG;
        require_once("$CFG->dirroot/question/type/numerical/questiontype.php");
        return new question_numerical_qtype();
    }

    function supports_dataset_item_generation() {
    // Calcualted support generation of randomly distributed number data
        return true;
    }

    function custom_generator_tools($datasetdef) {
        if (ereg('^(uniform|loguniform):([^:]*):([^:]*):([0-9]*)$',
                $datasetdef->options, $regs)) {
            $defid = "$datasetdef->type-$datasetdef->category-$datasetdef->name";
            for ($i = 0 ; $i<10 ; ++$i) {
                $lengthoptions[$i] = get_string(($regs[1] == 'uniform'
                                                ? 'decimals'
                                                : 'significantfigures'), 'quiz', $i);
            }
            return '<input type="submit" onClick="'
                    . "document.addform.regenerateddefid.value='$defid'; return true;"
                    .'" value="'. get_string('generatevalue', 'quiz') . '"/><br/>'
                    . '<input type="text" size="3" name="calcmin[]" '
                    . " value=\"$regs[2]\"/> &amp; <input name=\"calcmax[]\" "
                    . ' type="text" size="3" value="' . $regs[3] .'"/> '
                    . choose_from_menu($lengthoptions, 'calclength[]',
                                       $regs[4], // Selected
                                       '', '', '', true) . '<br/>'
                    . choose_from_menu(array('uniform' => get_string('uniform', 'quiz'),
                                             'loguniform' => get_string('loguniform', 'quiz')),
                                       'calcdistribution[]',
                                       $regs[1], // Selected
                                       '', '', '', true);
        } else {
            return '';
        }
    }

    function update_dataset_options($datasetdefs, $form) {
        // Do we have informatin about new options???
        if (empty($form->definition) || empty($form->calcmin)
                || empty($form->calcmax) || empty($form->calclength)
                || empty($form->calcdistribution)) {
            // I guess not

        } else {
            // Looks like we just could have some new information here
            foreach ($form->definition as $key => $defid) {
                if (isset($datasetdefs[$defid])
                        && is_numeric($form->calcmin[$key])
                        && is_numeric($form->calcmax[$key])
                        && is_numeric($form->calclength[$key])) {
                    switch     ($form->calcdistribution[$key]) {
                        case 'uniform': case 'loguniform':
                            $datasetdefs[$defid]->options =
                                    $form->calcdistribution[$key] . ':'
                                    . $form->calcmin[$key] . ':'
                                    . $form->calcmax[$key] . ':'
                                    . $form->calclength[$key];
                            break;
                        default:
                            notify("Unexpected distribution $form->calcdistribution[$key]");
                    }
                }
            }
        }

        // Look for empty options, on which we set default values
        foreach ($datasetdefs as $defid => $def) {
            if (empty($def->options)) {
                $datasetdefs[$defid]->options = 'uniform:1.0:10.0:1';
            }
        }
        return $datasetdefs;
    }

    function generate_dataset_item($options) {
        if (!ereg('^(uniform|loguniform):([^:]*):([^:]*):([0-9]*)$',
                $options, $regs)) {
            // Unknown options...
            return false;
        }
        if ($regs[1] == 'uniform') {
            $nbr = $regs[2] + ($regs[3]-$regs[2])*mt_rand()/mt_getrandmax();
            return sprintf("%.".$regs[4]."f",$nbr);

        } else if ($regs[1] == 'loguniform') {
            $log0 = log(abs($regs[2])); // It would have worked the other way to
            $nbr = exp($log0 + (log(abs($regs[3])) - $log0)*mt_rand()/mt_getrandmax());
            return sprintf("%.".$regs[4]."f",$nbr);

        } else {
            error("The distribution $regs[1] caused problems");
        }
        return '';
    }

    function comment_header($question) {
        //$this->get_question_options($question);
        global $SESSION;
        $strheader = Array();
        $delimiter = '';
        if (empty($question->id)) {
            $answers = $SESSION->datasetdependent->questionform->answers;
        } else {
            $answers = $question->options->answers;
        }
        foreach ($answers as $answer) {
            if (is_string($answer)) {
                $strheader[] = $answer;
            } else {
                $strheader[]= $answer->answer;
            }
            $delimiter = ',';
        }
        return $strheader;
    }

    function comment_on_datasetitems($question, $data, $number) {
        /// Find a default unit:
        if (!empty($question->id) && $unit = get_record('question_numerical_units',
                'question', $question->id, 'multiplier', 1.0)) {
            $unit = $unit->unit;
        } else {
            $unit = '';
        }

        $answers = $question->options->answers;
        $stranswers = get_string('answer', 'quiz');
        $strmin = get_string('min', 'quiz');
        $strmax = get_string('max', 'quiz');
        $errors = '';
        $delimiter = ': ';
        $virtualqtype = $this->get_virtual_qtype();
        $column =Array();
        foreach ($answers as $answer) {
            $calculated = qtype_calculated_calculate_answer(
                    $answer->answer, $data, $answer->tolerance,
                    $answer->tolerancetype, $answer->correctanswerlength,
                    $answer->correctanswerformat, $unit);
            $calculated->tolerance = $answer->tolerance;
            $calculated->tolerancetype = $answer->tolerancetype;
            $calculated->correctanswerlength = $answer->correctanswerlength;
            $calculated->correctanswerformat = $answer->correctanswerformat;
            $virtualqtype->get_tolerance_interval($calculated);
            if ($calculated->min === '') {
                // This should mean that something is wrong
                $errors .= " -$calculated->answer";
                $stranswers .= $delimiter;
            } else {
              //  $stranswers .= $delimiter.$calculated->answer;
              //  $strmin     .= $delimiter.$calculated->min;
              //  $strmax     .= $delimiter.$calculated->max;
                $column[]=$stranswers.$delimiter.$calculated->answer.'<br/>'.$strmin.$delimiter.$calculated->min.'<br/>'.$strmax.$delimiter.$calculated->max ;
            }
        }
        return $column;
    }

    function tolerance_types() {
        return array('1'  => get_string('relative', 'quiz'),
                     '2'  => get_string('nominal', 'quiz'),
                     '3'  => get_string('geometric', 'quiz'));
    }

    function dataset_options($form, $name, $mandatory=true,$renameabledatasets=false) {
    // Takes datasets from the parent implementation but
    // filters options that are currently not accepted by calculated
    // It also determines a default selection...
    //$renameabledatasets not implemented anmywhere 
        list($options, $selected) = parent::dataset_options($form, $name,'','qtype_calculated');
  //  list($options, $selected) = $this->dataset_optionsa($form, $name);

        foreach ($options as $key => $whatever) {
            if (!ereg('^'.LITERAL.'-', $key) && $key != '0') {
                unset($options[$key]);
            }
        }
        if (!$selected) {
            if ($mandatory){             
            $selected = LITERAL . "-0-$name"; // Default
            }else {
                $selected = "0"; // Default
            }    
        }
        return array($options, $selected);
    }

    function construct_dataset_menus($form, $mandatorydatasets,
                                     $optionaldatasets) {
        $datasetmenus = array();
        foreach ($mandatorydatasets as $datasetname) {
            if (!isset($datasetmenus[$datasetname])) {
                list($options, $selected) =
                        $this->dataset_options($form, $datasetname);
                unset($options['0']); // Mandatory...
                $datasetmenus[$datasetname] = choose_from_menu ($options,
                        'dataset[]', $selected, '', '', "0", true);
            }
        }
        foreach ($optionaldatasets as $datasetname) {
            if (!isset($datasetmenus[$datasetname])) {
                list($options, $selected) =
                        $this->dataset_options($form, $datasetname);
                $datasetmenus[$datasetname] = choose_from_menu ($options,
                        'dataset[]', $selected, '', '', "0", true);
            }
        }
        return $datasetmenus;
    }

    function get_correct_responses(&$question, &$state) {
        $virtualqtype = $this->get_virtual_qtype();
        $unit = $virtualqtype->get_default_numerical_unit($question);
        foreach ($question->options->answers as $answer) {
            if (((int) $answer->fraction) === 1) {
                $answernumerical = qtype_calculated_calculate_answer(
                 $answer->answer, $state->options->dataset, $answer->tolerance,
                 $answer->tolerancetype, $answer->correctanswerlength,
                 $answer->correctanswerformat, $unit->unit);
                return array('' => $answernumerical->answer);
            }
        }
        return null;
    }

    function substitute_variables($str, $dataset) {
        $formula = parent::substitute_variables($str, $dataset);
        if ($error = qtype_calculated_find_formula_errors($formula)) {
            return $error;
        }
        /// Calculate the correct answer
        if (empty($formula)) {
            $str = '';
        } else {
            eval('$str = '.$formula.';');
        }
        return $str;
    }
    
    /**
    * This function retrieve the item count of the available category shareable
    * wild cards that is added as a comment displayed when a wild card with 
    * the same name is displayed in datasetdefinitions_form.php
    */ 
    function get_dataset_definitions_category($form) {
        global $CFG;
        $datasetdefs = array();
        $lnamemax = 30;
        if (!empty($form->category)) {            
            $sql = "SELECT i.*,d.*
                    FROM {$CFG->prefix}question_datasets d,
                         {$CFG->prefix}question_dataset_definitions i    
                  WHERE i.id = d.datasetdefinition
                    AND i.category = '$form->category'
                    ;
                   ";
             if ($records = get_records_sql($sql)) {
                   foreach ($records as $r) {
                       if ( !isset ($datasetdefs["$r->name"])) $datasetdefs["$r->name"] = $r->itemcount;
                    }
                }
        }  
        return  $datasetdefs ;
    }  

    /**
    * This function build a table showing the available category shareable
    * wild cards, their name, their definition (Min, Max, Decimal) , the item count
    * and the name of the question where they are used.
    * This table is intended to be add before the question text to help the user use 
    * these wild cards
    */                          
        
    function print_dataset_definitions_category($form) {
        global $CFG;
        $datasetdefs = array();
        $lnamemax = 22;
        $namestr =get_string('name', 'quiz');
        $minstr=get_string('min', 'quiz');
        $maxstr=get_string('max', 'quiz');
        $rangeofvaluestr=get_string('minmax','qtype_datasetdependent');
        $questionusingstr = get_string('usedinquestion','qtype_calculated');
        $wildcardstr =  get_string('wildcard', 'qtype_calculated');
        $itemscountstr = get_string('itemscount','qtype_datasetdependent');
       $text ='';
        if (!empty($form->category)) {           
            $sql = "SELECT i.*,d.*
                    FROM {$CFG->prefix}question_datasets d,
                         {$CFG->prefix}question_dataset_definitions i    
                    WHERE i.id = d.datasetdefinition
                    AND i.category = '$form->category'; 
                    " ;
            if ($records = get_records_sql($sql)) {
                foreach ($records as $r) {
                    $sql1 = "SELECT q.*
                        FROM  {$CFG->prefix}question q                    
                             WHERE q.id = $r->question                    
                    ";                  
                    if ( !isset ($datasetdefs["$r->type-$r->category-$r->name"])){
                        $datasetdefs["$r->type-$r->category-$r->name"]= $r;
                    }
                    if ($questionb = get_records_sql($sql1)) {
                        $datasetdefs["$r->type-$r->category-$r->name"]->questions[$r->question]->name =$questionb[$r->question]->name ;
                    }
                }
            }
        }
        if (!empty ($datasetdefs)){
            
            $text ="<table width=\"100%\" border=\"1\"><tr><th  style=\"white-space:nowrap;\" class=\"header\" scope=\"col\" >$namestr</th><th   style=\"white-space:nowrap;\" class=\"header\" scope=\"col\">$rangeofvaluestr</th><th  style=\"white-space:nowrap;\" class=\"header\" scope=\"col\">$itemscountstr</th><th style=\"white-space:nowrap;\" class=\"header\" scope=\"col\">$questionusingstr</th></tr>";  
            foreach ($datasetdefs as $datasetdef){
                list($distribution, $min, $max,$dec) = explode(':', $datasetdef->options, 4);
                $text .="<tr><td valign=\"top\" align=\"center\"> $datasetdef->name </td><td align=\"center\" valign=\"top\"> $min <strong>-</strong> $max </td><td align=\"right\" valign=\"top\">$datasetdef->itemcount&nbsp;&nbsp;</td><td align=\"left\">";
                foreach ($datasetdef->questions as $qu) {
                    //limit the name length displayed
                    if (!empty($qu->name)) {
                        $qu->name = (strlen($qu->name) > $lnamemax) ?
                        substr($qu->name, 0, $lnamemax).'...' : $qu->name;
                    } else {
                        $qu->name = '';
                    }
                    $text .=" &nbsp;&nbsp; $qu->name <br/>";  
                }  
                $text .="</td></tr>";
            }
            $text .="</table>";
        }else{
             $text .=get_string('nosharedwildcard', 'qtype_calculated'); 
        }
        return  $text ;
    }    


/// BACKUP FUNCTIONS ////////////////////////////

    /*
     * Backup the data in the question
     *
     * This is used in question/backuplib.php
     */
    function backup($bf,$preferences,$question,$level=6) {

        $status = true;

        $calculateds = get_records("question_calculated","question",$question,"id");
        //If there are calculated-s
        if ($calculateds) {
            //Iterate over each calculateds
            foreach ($calculateds as $calculated) {
                $status = $status &&fwrite ($bf,start_tag("CALCULATED",$level,true));
                //Print calculated contents
                fwrite ($bf,full_tag("ANSWER",$level+1,false,$calculated->answer));
                fwrite ($bf,full_tag("TOLERANCE",$level+1,false,$calculated->tolerance));
                fwrite ($bf,full_tag("TOLERANCETYPE",$level+1,false,$calculated->tolerancetype));
                fwrite ($bf,full_tag("CORRECTANSWERLENGTH",$level+1,false,$calculated->correctanswerlength));
                fwrite ($bf,full_tag("CORRECTANSWERFORMAT",$level+1,false,$calculated->correctanswerformat));
                //Now backup numerical_units
                $status = question_backup_numerical_units($bf,$preferences,$question,7);
                //Now backup required dataset definitions and items...
                $status = question_backup_datasets($bf,$preferences,$question,7);
                //End calculated data
                $status = $status &&fwrite ($bf,end_tag("CALCULATED",$level,true));
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

        //Get the calculated-s array
        $calculateds = $info['#']['CALCULATED'];

        //Iterate over calculateds
        for($i = 0; $i < sizeof($calculateds); $i++) {
            $cal_info = $calculateds[$i];
            //traverse_xmlize($cal_info);                                                                 //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //Now, build the question_calculated record structure
            $calculated->question = $new_question_id;
            $calculated->answer = backup_todb($cal_info['#']['ANSWER']['0']['#']);
            $calculated->tolerance = backup_todb($cal_info['#']['TOLERANCE']['0']['#']);
            $calculated->tolerancetype = backup_todb($cal_info['#']['TOLERANCETYPE']['0']['#']);
            $calculated->correctanswerlength = backup_todb($cal_info['#']['CORRECTANSWERLENGTH']['0']['#']);
            $calculated->correctanswerformat = backup_todb($cal_info['#']['CORRECTANSWERFORMAT']['0']['#']);

            ////We have to recode the answer field
            $answer = backup_getid($restore->backup_unique_code,"question_answers",$calculated->answer);
            if ($answer) {
                $calculated->answer = $answer->new_id;
            }

            //The structure is equal to the db, so insert the question_calculated
            $newid = insert_record ("question_calculated",$calculated);

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

            //Now restore numerical_units
            $status = question_restore_numerical_units ($old_question_id,$new_question_id,$cal_info,$restore);

            //Now restore dataset_definitions
            if ($status && $newid) {
                $status = question_restore_dataset_definitions ($old_question_id,$new_question_id,$cal_info,$restore);
            }

            if (!$newid) {
                $status = false;
            }
        }

        return $status;
    }
}
//// END OF CLASS ////

//////////////////////////////////////////////////////////////////////////
//// INITIATION - Without this line the question type is not in use... ///
//////////////////////////////////////////////////////////////////////////
$QTYPES['calculated']= new question_calculated_qtype();
// The following adds the questiontype to the menu of types shown to teachers
$QTYPE_MENU['calculated'] = get_string("calculated", "quiz");

function qtype_calculated_calculate_answer($formula, $individualdata,
        $tolerance, $tolerancetype, $answerlength, $answerformat='1', $unit='') {
/// The return value has these properties:
/// ->answer    the correct answer
/// ->min       the lower bound for an acceptable response
/// ->max       the upper bound for an accetpable response

    /// Exchange formula variables with the correct values...
    global $QTYPES;
    $answer = $QTYPES['calculated']->substitute_variables($formula, $individualdata);
    if ('1' == $answerformat) { /* Answer is to have $answerlength decimals */
        /*** Adjust to the correct number of decimals ***/

        $calculated->answer = round($answer, $answerlength);

        if ($answerlength) {
            /* Try to include missing zeros at the end */

            if (ereg('^(.*\\.)(.*)$', $calculated->answer, $regs)) {
                $calculated->answer = $regs[1] . substr(
                        $regs[2] . '00000000000000000000000000000000000000000x',
                        0, $answerlength)
                        . $unit;
            } else {
                $calculated->answer .=
                        substr('.00000000000000000000000000000000000000000x',
                        0, $answerlength + 1) . $unit;
            }
        } else {
            /* Attach unit */
            $calculated->answer .= $unit;
        }

    } else if ($answer) { // Significant figures does only apply if the result is non-zero

        // Convert to positive answer...
        if ($answer < 0) {
            $answer = -$answer;
            $sign = '-';
        } else {
            $sign = '';
        }

        // Determine the format 0.[1-9][0-9]* for the answer...
        $p10 = 0;
        while ($answer < 1) {
            --$p10;
            $answer *= 10;
        }
        while ($answer >= 1) {
            ++$p10;
            $answer /= 10;
        }
        // ... and have the answer rounded of to the correct length
        $answer = round($answer, $answerlength);

        // Have the answer written on a suitable format,
        // Either scientific or plain numeric
        if (-2 > $p10 || 4 < $p10) {
            // Use scientific format:
            $eX = 'e'.--$p10;
            $answer *= 10;
            if (1 == $answerlength) {
                $calculated->answer = $sign.$answer.$eX.$unit;
            } else {
                // Attach additional zeros at the end of $answer,
                $answer .= (1==strlen($answer) ? '.' : '')
                        . '00000000000000000000000000000000000000000x';
                $calculated->answer = $sign
                        .substr($answer, 0, $answerlength +1).$eX.$unit;
            }
        } else {
            // Stick to plain numeric format
            $answer *= "1e$p10";
            if (0.1 <= $answer / "1e$answerlength") {
                $calculated->answer = $sign.$answer.$unit;
            } else {
                // Could be an idea to add some zeros here
                $answer .= (ereg('^[0-9]*$', $answer) ? '.' : '')
                        . '00000000000000000000000000000000000000000x';
                $oklen = $answerlength + ($p10 < 1 ? 2-$p10 : 1);
                $calculated->answer = $sign.substr($answer, 0, $oklen).$unit;
            }
        }

    } else {
        $calculated->answer = 0.0;
    }

    /// Return the result
    return $calculated;
}


function qtype_calculated_find_formula_errors($formula) {
/// Validates the formula submitted from the question edit page.
/// Returns false if everything is alright.
/// Otherwise it constructs an error message

    // Strip away dataset names
    while (ereg('\\{[[:alpha:]][^>} <{"\']*\\}', $formula, $regs)) {
        $formula = str_replace($regs[0], '1', $formula);
    }

    // Strip away empty space and lowercase it
    $formula = strtolower(str_replace(' ', '', $formula));

    $safeoperatorchar = '-+/*%>:^~<?=&|!'; /* */
    $operatorornumber = "[$safeoperatorchar.0-9eE]";


    while (ereg("(^|[$safeoperatorchar,(])([a-z0-9_]*)\\(($operatorornumber+(,$operatorornumber+((,$operatorornumber+)+)?)?)?\\)",
            $formula, $regs)) {

        switch ($regs[2]) {
            // Simple parenthesis
            case '':
                if ($regs[4] || strlen($regs[3])==0) {
                    return get_string('illegalformulasyntax', 'quiz', $regs[0]);
                }
                break;

            // Zero argument functions
            case 'pi':
                if ($regs[3]) {
                    return get_string('functiontakesnoargs', 'quiz', $regs[2]);
                }
                break;

            // Single argument functions (the most common case)
            case 'abs': case 'acos': case 'acosh': case 'asin': case 'asinh':
            case 'atan': case 'atanh': case 'bindec': case 'ceil': case 'cos':
            case 'cosh': case 'decbin': case 'decoct': case 'deg2rad':
            case 'exp': case 'expm1': case 'floor': case 'is_finite':
            case 'is_infinite': case 'is_nan': case 'log10': case 'log1p':
            case 'octdec': case 'rad2deg': case 'sin': case 'sinh': case 'sqrt':
            case 'tan': case 'tanh':
                if ($regs[4] || empty($regs[3])) {
                    return get_string('functiontakesonearg','quiz',$regs[2]);
                }
                break;

            // Functions that take one or two arguments
            case 'log': case 'round':
                if ($regs[5] || empty($regs[3])) {
                    return get_string('functiontakesoneortwoargs','quiz',$regs[2]);
                }
                break;

            // Functions that must have two arguments
            case 'atan2': case 'fmod': case 'pow':
                if ($regs[5] || empty($regs[4])) {
                    return get_string('functiontakestwoargs', 'quiz', $regs[2]);
                }
                break;

            // Functions that take two or more arguments
            case 'min': case 'max':
                if (empty($regs[4])) {
                    return get_string('functiontakesatleasttwo','quiz',$regs[2]);
                }
                break;

            default:
                return get_string('unsupportedformulafunction','quiz',$regs[2]);
        }

        // Exchange the function call with '1' and then chack for
        // another function call...
        if ($regs[1]) {
            // The function call is proceeded by an operator
            $formula = str_replace($regs[0], $regs[1] . '1', $formula);
        } else {
            // The function call starts the formula
            $formula = ereg_replace("^$regs[2]\\([^)]*\\)", '1', $formula);
        }
    }

    if (ereg("[^$safeoperatorchar.0-9eE]+", $formula, $regs)) {
        return get_string('illegalformulasyntax', 'quiz', $regs[0]);
    } else {
        // Formula just might be valid
        return false;
    }
}

function dump($obj) {
    echo "<pre>\n";
    var_dump($obj);
    echo "</pre><br />\n";
}

?>
