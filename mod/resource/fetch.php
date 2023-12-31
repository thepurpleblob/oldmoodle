<?php  // $Id$
       // Fetches an external URL and passes it through the filters

    die; //not used anymore, please FIX SC #99 before enabling

    require_once("../../config.php");
    require_once("lib.php");

    $id = required_param('id', PARAM_INT);     // Course Module ID
    $url = required_param('url', PARAM_URL);    // url to fetch

    if (! $cm = get_coursemodule_from_id('resource', $id)) {
        error("Course Module ID was incorrect");
    }

    if (! $course = get_record("course", "id", $cm->course)) {
        error("Course is misconfigured");
    }

    require_course_login($course, true, $cm);

    if (! $resource = get_record("resource", "id", $cm->instance)) {
        error("Resource ID was incorrect");
    }

    $content = resource_fetch_remote_file($cm, $url);

    $formatoptions->noclean = true;
    echo format_text($content->results, FORMAT_HTML, $formatoptions, $course->id);

?>
