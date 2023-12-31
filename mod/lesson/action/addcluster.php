<?php

/************** add cluster ************************************/

    if (!isteacher($course->id)) {
        error("Only teachers can look at this page");
    }
    
    confirm_sesskey();

    // first get the preceeding page
    // if $pageid = 0, then we are inserting a new page at the beginning of the lesson
    $pageid = required_param('pageid', PARAM_INT);
        
    $timenow = time();
    
    if ($pageid == 0) {
        if (!$page = get_record("lesson_pages", "prevpageid", 0, "lessonid", $lesson->id)) {
            error("Error: Add cluster: page record not found");
        }
    } else {
        if (!$page = get_record("lesson_pages", "id", $pageid)) {
            error("Error: Add cluster: page record not found");
        }
    }
    $newpage = new stdClass;
    $newpage->lessonid = $lesson->id;
    $newpage->prevpageid = $pageid;
    if ($pageid != 0) {
        $newpage->nextpageid = $page->nextpageid;
    } else {
        $newpage->nextpageid = $page->id;
    }
    $newpage->qtype = LESSON_CLUSTER;
    $newpage->timecreated = $timenow;
    $newpage->title = get_string("clustertitle", "lesson");
    $newpage->contents = get_string("clustertitle", "lesson");
    if (!$newpageid = insert_record("lesson_pages", $newpage)) {
        error("Insert page: new page not inserted");
    }
    // update the linked list...
    if ($pageid != 0) {
        if (!set_field("lesson_pages", "nextpageid", $newpageid, "id", $pageid)) {
            error("Add cluster: unable to update link");
        }
    }
    
    if ($pageid == 0) {
        $page->nextpageid = $page->id;
    }        
    if ($page->nextpageid) {
        // the new page is not the last page
        if (!set_field("lesson_pages", "prevpageid", $newpageid, "id", $page->nextpageid)) {
            error("Insert page: unable to update previous link");
        }
    }
    // ..and the single "answer"
    $newanswer = new stdClass;
    $newanswer->lessonid = $lesson->id;
    $newanswer->pageid = $newpageid;
    $newanswer->timecreated = $timenow;
    $newanswer->jumpto = LESSON_CLUSTERJUMP;
    if(!$newanswerid = insert_record("lesson_answers", $newanswer)) {
        error("Add cluster: answer record not inserted");
    }
    redirect("view.php?id=$cm->id", get_string('addedcluster', 'lesson'));
?>