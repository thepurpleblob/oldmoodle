<?php  // $Id$

/// Local Library of functions and constants for module scorm

/**
* Create a new temporary subdirectory with a random name in the given path
*
* @param string $strpath The scorm data directory
* @return string/boolean
*/
function scorm_datadir($strPath)
{
    global $CFG;

    if (is_dir($strPath)) {
        do {
            // Create a random string of 8 chars
            $randstring = NULL;
            $lchar = '';
            $len = 8;
            for ($i=0; $i<$len; $i++) {
                $char = chr(rand(48,122));
                while (!ereg('[a-zA-Z0-9]', $char)){
                    if ($char == $lchar) continue;
                        $char = chr(rand(48,90));
                    }
                    $randstring .= $char;
                    $lchar = $char;
            } 
            $datadir='/'.randstring;
        } while (file_exists($strPath.$datadir));
        mkdir($strPath.$datadir, $CFG->directorypermissions);
        @chmod($strPath.$datadir, $CFG->directorypermissions);  // Just in case mkdir didn't do it
        return $strPath.$datadir;
    } else {
        return false;
    }
}

/**
* Given a package directory, this function will check if the package is valid
*
* @param string $packagedir The package directory
* @return mixed
*/
function scorm_validate($packagedir) {
    $validation = new stdClass();
    if (is_file($packagedir.'/imsmanifest.xml')) {
        $validation->result = 'found';
        $validation->pkgtype = 'SCORM';
    } else {
        if ($handle = opendir($packagedir)) {
            while (($file = readdir($handle)) !== false) {
                $ext = substr($file,strrpos($file,'.'));
                if (strtolower($ext) == '.cst') {
                    $validation->result = 'found';
                    $validation->pkgtype = 'AICC';
                    break;
                }
            }
            closedir($handle);
        }
        if (!isset($validation)) {
            $validation->result = 'nomanifest';
            $validation->pkgtype = 'SCORM';
        }
    }
    return $validation;
}

function scorm_get_user_data($userid) {
/// Gets user info required to display the table of scorm results
/// for report.php

    return get_record('user','id',$userid,'','','','','firstname, lastname, picture');
}

function scorm_string_wrap($stringa, $len=15) {
// Crop the given string into max $len characters lines
    if (strlen($stringa) > $len) {
        $words = explode(' ', $stringa);
        $newstring = '';
        $substring = '';
        foreach ($words as $word) {
           if ((strlen($substring)+strlen($word)+1) < $len) {
               $substring .= ' '.$word;
           } else {
               $newstring .= ' '.$substring.'<br />';
               $substring = $word;
           }
        }
        if (!empty($substring)) {
            $newstring .= ' '.$substring;
        }
        return $newstring;
    } else {
        return $stringa;
    }
}

function scorm_eval_prerequisites($prerequisites,$usertracks) {
    $element = '';
    $stack = array();
    $statuses = array(
                'passed' => 'passed',
                'completed' => 'completed',
                'failed' => 'failed',
                'incomplete' => 'incomplete',
                'browsed' => 'browsed',
                'not attempted' => 'notattempted',
                'p' => 'passed',
                'c' => 'completed',
                'f' => 'failed',
                'i' => 'incomplete',
                'b' => 'browsed',
                'n' => 'notattempted'
                );
    $i=0;  
    while ($i<strlen($prerequisites)) {
        $symbol = $prerequisites[$i];
        switch ($symbol) {
            case '&':
            case '|':
                $symbol .= $symbol;
            case '~':
            case '(':
            case ')':
            case '*':
            //case '{':
            //case '}':
            //case ',':
                $element = trim($element);
                
                if (!empty($element)) {
                    $element = trim($element);
                    if (isset($usertracks[$element])) {
                        $element = '((\''.$usertracks[$element]->status.'\' == \'completed\') || '.
                                  '(\''.$usertracks[$element]->status.'\' == \'passed\'))'; 
                    } else if (($operator = strpos($element,'=')) !== false) {
                        $item = trim(substr($element,0,$operator));
                        if (!isset($usertracks[$item])) {
                            return false;
                        }
                        
                        $value = trim(trim(substr($element,$operator+1)),'"');
                        if (isset($statuses[$value])) {
                            $status = $statuses[$value];
                        } else {
                            return false;
                        }
                                              
                        $element = '(\''.$usertracks[$item]->status.'\' == \''.$status.'\')';
                    } else if (($operator = strpos($element,'<>')) !== false) {
                        $item = trim(substr($element,0,$operator));
                        if (!isset($usertracks[$item])) {
                            return false;
                        }
                        
                        $value = trim(trim(substr($element,$operator+2)),'"');
                        if (isset($statuses[$value])) {
                            $status = $statuses[$value];
                        } else {
                            return false;
                        }
                        
                        $element = '(\''.$usertracks[$item]->status.'\' != \''.$status.'\')';
                    } else if (is_numeric($element)) {
                        if ($symbol == '*') {
                            $symbol = '';
                            $open = strpos($prerequisites,'{',$i);
                            $opened = 1;
                            $closed = 0;
                            for ($close=$open+1; (($opened > $closed) && ($close<strlen($prerequisites))); $close++) { 
                                 if ($prerequisites[$close] == '}') {
                                     $closed++;
                                 } else if ($prerequisites[$close] == '{') {
                                     $opened++;
                                 }
                            } 
                            $i = $close;
                            
                            $setelements = explode(',', substr($prerequisites, $open+1, $close-($open+1)-1));
                            $settrue = 0;
                            foreach ($setelements as $setelement) {
                                if (scorm_eval_prerequisites($setelement,$usertracks)) {
                                    $settrue++;
                                }
                            }
                            
                            if ($settrue >= $element) {
                                $element = 'true'; 
                            } else {
                                $element = 'false';
                            }
                        }
                    } else {
                        return false;
                    }
                    
                    array_push($stack,$element);
                    $element = '';
                }
                if ($symbol == '~') {
                    $symbol = '!';
                }
                if (!empty($symbol)) {
                    array_push($stack,$symbol);
                }
            break;
            default:
                $element .= $symbol;
            break;
        }
        $i++;
    }
    if (!empty($element)) {
        $element = trim($element);
        if (isset($usertracks[$element])) {
            $element = '((\''.$usertracks[$element]->status.'\' == \'completed\') || '.
                       '(\''.$usertracks[$element]->status.'\' == \'passed\'))'; 
        } else if (($operator = strpos($element,'=')) !== false) {
            $item = trim(substr($element,0,$operator));
            if (!isset($usertracks[$item])) {
                return false;
            }
            
            $value = trim(trim(substr($element,$operator+1)),'"');
            if (isset($statuses[$value])) {
                $status = $statuses[$value];
            } else {
                return false;
            }
            
            $element = '(\''.$usertracks[$item]->status.'\' == \''.$status.'\')';
        } else if (($operator = strpos($element,'<>')) !== false) {
            $item = trim(substr($element,0,$operator));
            if (!isset($usertracks[$item])) {
                return false;
            }
            
            $value = trim(trim(substr($element,$operator+1)),'"');
            if (isset($statuses[$value])) {
                $status = $statuses[$value];
            } else {
                return false;
            }
            
            $element = '(\''.$usertracks[$item]->status.'\' != \''.trim($status).'\')';
        } else {
            return false;
        }
        
        array_push($stack,$element);
    }
    return eval('return '.implode($stack).';');
}


function scorm_insert_track($userid,$scormid,$scoid,$element,$value) {
    $id = null;
    if ($track = get_record_select('scorm_scoes_track',"userid='$userid' AND scormid='$scormid' AND scoid='$scoid' AND element='$element'")) {
        $track->value = $value;
        $track->timemodified = time();
        $id = update_record('scorm_scoes_track',$track);
    } else {
        $track->userid = $userid;
        $track->scormid = $scormid;
        $track->scoid = $scoid;
        $track->element = $element;
        $track->value = addslashes($value);
        $track->timemodified = time();
        $id = insert_record('scorm_scoes_track',$track);
    }
    return $id;
}


function scorm_add_time($a, $b) {
    $aes = explode(':',$a);
    $bes = explode(':',$b);
    $aseconds = explode('.',$aes[2]);
    $bseconds = explode('.',$bes[2]);
    $change = 0;

    $acents = 0;  //Cents
    if (count($aseconds) > 1) {
        $acents = $aseconds[1];
    }
    $bcents = 0;
    if (count($bseconds) > 1) {
        $bcents = $bseconds[1];
    }
    $cents = $acents + $bcents;
    $change = floor($cents / 100);
    $cents = $cents - ($change * 100);
    if (floor($cents) < 10) {
        $cents = '0'. $cents;
    }

    $secs = $aseconds[0] + $bseconds[0] + $change;  //Seconds
    $change = floor($secs / 60);
    $secs = $secs - ($change * 60);
    if (floor($secs) < 10) {
        $secs = '0'. $secs;
    }

    $mins = $aes[1] + $bes[1] + $change;   //Minutes
    $change = floor($mins / 60);
    $mins = $mins - ($change * 60);
    if ($mins < 10) {
        $mins = '0' .  $mins;
    }

    $hours = $aes[0] + $bes[0] + $change;  //Hours
    if ($hours < 10) {
        $hours = '0' . $hours;
    }

    if ($cents != '0') {
        return $hours . ":" . $mins . ":" . $secs . '.' . $cents;
    } else {
        return $hours . ":" . $mins . ":" . $secs;
    }
}

function scorm_external_link($link) {
// check if a link is external
    $result = false;
    $link = strtolower($link);
    if (substr($link,0,7) == 'http://') {
        $result = true;
    } else if (substr($link,0,8) == 'https://') {
        $result = true;
    } else if (substr($link,0,4) == 'www.') {
        $result = true;
    }
    return $result;
}

function scorm_grade_user($scoes, $userid, $grademethod=VALUESCOES) {
    $scores = NULL; 
    $scores->scoes = 0;
    $scores->values = 0;
    $scores->max = 0;
    $scores->sum = 0;

    if (!$scoes) {
        return '';
    }

    foreach ($scoes as $sco) { 
        if ($userdata=scorm_get_tracks($sco->id, $userid)) {
            if (($userdata->status == 'completed') || ($userdata->status == 'passed')) {
                $scores->scoes++;
            }       
            if (!empty($userdata->score_raw)) {
                $scores->values++;
                $scores->sum += $userdata->score_raw;
                $scores->max = ($userdata->score_raw > $scores->max)?$userdata->score_raw:$scores->max;
            }       
        }       
    }
    switch ($grademethod) {
        case VALUEHIGHEST:
            return $scores->max;
        break;  
        case VALUEAVERAGE:
            if ($scores->values > 0) {
                return $scores->sum/$scores->values;
            } else {
                return 0;
            }       
        break;  
        case VALUESUM:
            return $scores->sum;
        break;  
        case VALUESCOES:
            return $scores->scoes;
        break;  
    }
}

function scorm_count_launchable($scormid,$organization) {
    return count_records_select('scorm_scoes',"scorm=$scormid AND organization='$organization' AND launch<>''");
}

function scorm_get_toc($scorm,$liststyle,$currentorg='',$scoid='',$mode='normal',$play=false) {
    global $USER, $CFG;

    $strexpand = get_string('expcoll','scorm');
    
    $result = new stdClass();
    $result->toc = "<ul id='0' class='$liststyle'>\n";
    $result->prerequisites = true;
    $incomplete = false;
    $organizationsql = '';
    
    if (!empty($currentorg)) {
        if (($organizationtitle = get_field('scorm_scoes','title','scorm',$scorm->id,'identifier',$currentorg)) != '') {
            $result->toc .= "\t<li>$organizationtitle</li>\n";
        }
        $organizationsql = "AND organization='$currentorg'";
    }
    if ($scoes = get_records_select('scorm_scoes',"scorm='$scorm->id' $organizationsql order by id ASC")){
        $usertracks = array();
        foreach ($scoes as $sco) {
            if (!empty($sco->launch)) {
                if ($usertrack=scorm_get_tracks($sco->id,$USER->id)) {
                    if ($usertrack->status == '') {
                        $usertrack->status = 'notattempted';
                    }
                    $usertracks[$sco->identifier] = $usertrack;
                }
            }
        }

        $level=0;
        $sublist=1;
        $previd = 0;
        $nextid = 0;
        $findnext = false;
        $parents[$level]='/';
        
        foreach ($scoes as $sco) {
            if ($parents[$level]!=$sco->parent) {
                if ($newlevel = array_search($sco->parent,$parents)) {
                    for ($i=0; $i<($level-$newlevel); $i++) {
                        $result->toc .= "\t\t</ul></li>\n";
                    }
                    $level = $newlevel;
                } else {
                    $i = $level;
                    $closelist = '';
                    while (($i > 0) && ($parents[$level] != $sco->parent)) {
                        $closelist .= "\t\t</ul></li>\n";
                        $i--;
                    }
                    if (($i == 0) && ($sco->parent != $currentorg)) {
                        $result->toc .= "\t\t<li><ul id='$sublist' class='$liststyle'>\n";
                        $level++;
                    } else {
                        $result->toc .= $closelist;
                        $level = $i;
                    }
                    $parents[$level]=$sco->parent;
                }
            }
            $result->toc .= "\t\t<li>";
            $nextsco = next($scoes);
            if (($nextsco !== false) && ($sco->parent != $nextsco->parent) && (($level==0) || (($level>0) && ($nextsco->parent == $sco->identifier)))) {
                $sublist++;
                $result->toc .= '<a href="javascript:expandCollide(img'.$sublist.','.$sublist.');"><img id="img'.$sublist.'" src="'.$CFG->wwwroot.'/mod/scorm/pix/minus.gif" alt="'.$strexpand.'" title="'.$strexpand.'"/></a>';
            } else {
                $result->toc .= '<img src="'.$CFG->wwwroot.'/mod/scorm/pix/spacer.gif" />';
            }
            if (empty($sco->title)) {
                $sco->title = $sco->identifier;
            }
            if (!empty($sco->launch)) {
                $startbold = '';
                $endbold = '';
                $score = '';
                if (empty($scoid) && ($mode != 'normal')) {
                    $scoid = $sco->id;
                }
                if (isset($usertracks[$sco->identifier])) {
                    $usertrack = $usertracks[$sco->identifier];
                    $strstatus = get_string($usertrack->status,'scorm');
                    $result->toc .= "<img src='".$CFG->wwwroot."/mod/scorm/pix/".$usertrack->status.".gif' alt='$strstatus' title='$strstatus' />";
                    if (($usertrack->status == 'notattempted') || ($usertrack->status == 'incomplete') || ($usertrack->status == 'browsed')) {
                        $incomplete = true;
                        if ($play && empty($scoid)) {
                            $scoid = $sco->id;
                        }
                    }
                    if ($usertrack->score_raw != '') {
                        $score = '('.get_string('score','scorm').':&nbsp;'.$usertrack->score_raw.')';
                    }
                } else {
                    if ($play && empty($scoid)) {
                        $scoid = $sco->id;
                    }
                    if ($sco->scormtype == 'sco') {
                        $result->toc .= '<img src="'.$CFG->wwwroot.'/mod/scorm/pix/notattempted.gif" alt="'.get_string('notattempted','scorm').'" title="'.get_string('notattempted','scorm').'" />';
                        $incomplete = true;
                    } else {
                        $result->toc .= '<img src="'.$CFG->wwwroot.'/mod/scorm/pix/asset.gif" alt="'.get_string('asset','scorm').'" title="'.get_string('asset','scorm').'" />';
                    }
                }
                if ($sco->id == $scoid) {
                    $startbold = '<b>';
                    $endbold = '</b>';
                    $findnext = true;
                    $shownext = $sco->next;
                    $showprev = $sco->previous;
                }
                
                if (($nextid == 0) && (scorm_count_launchable($scorm->id,$currentorg) > 1) && ($nextsco!==false) && (!$findnext)) {
                    if (!empty($sco->launch)) {
                        $previd = $sco->id;
                    }
                }
                if (empty($sco->prerequisites) || scorm_eval_prerequisites($sco->prerequisites,$usertracks)) {
                    if ($sco->id == $scoid) {
                        $result->prerequisites = true;
                    }
                    $result->toc .= '&nbsp'.$startbold.'<a href="'.$CFG->wwwroot.'/mod/scorm/player.php?a='.$scorm->id.'&currentorg='.$currentorg.'&mode='.$mode.'&scoid='.$sco->id.'">'.format_string($sco->title).'</a>'.$score.$endbold."</li>\n";
                } else {
                    if ($sco->id == $scoid) {
                        $result->prerequisites = false;
                    }
                    $result->toc .= '&nbsp;'.$sco->title."</li>\n";
                }
            } else {
                $result->toc .= '&nbsp;'.$sco->title."</li>\n";
            }
            if (($nextsco !== false) && ($nextid == 0) && ($findnext)) {
                if (!empty($nextsco->launch)) {
                    $nextid = $nextsco->id;
                }
            }
        }
        for ($i=0;$i<$level;$i++) {
            $result->toc .= "\t\t</ul></li>\n";
        }
        
        if ($play) {
            $sco = get_record('scorm_scoes','id',$scoid);
            $sco->previd = $previd;
            $sco->nextid = $nextid;
            $result->sco = $sco;
            $result->incomplete = $incomplete;
        } else {
            $result->incomplete = $incomplete;
        }
    }
    $result->toc .= "\t</ul>\n";
    
    return $result;
}

function scorm_get_tracks($scoid,$userid) {
/// Gets all tracks of specified sco and user
    global $CFG;

    $attemptsql = '';
    if ($lastattempt = get_record('scorm_sco_tracks', 'user', $USER->id, 'scorm', $scorm->id, '', '', 'max(attempt) as a')) {
        $attemptsql = ' AND attempt='.$lastattempt;
    }
    if ($tracks = get_records_select('scorm_scoes_track',"userid=$userid AND scoid=$scoid".$attemptsql,'element ASC')) {
        $usertrack->userid = $userid;
        $usertrack->scoid = $scoid; 
        $usertrack->score_raw = '';
        $usertrack->status = '';
        $usertrack->total_time = '00:00:00';
        $usertrack->session_time = '00:00:00';
        $usertrack->timemodified = 0;
        foreach ($tracks as $track) {
            $element = $track->element;
            $usertrack->{$element} = $track->value;
            switch ($element) {
                case 'cmi.core.lesson_status':
                case 'cmi.completion_status':
                    if ($track->value == 'not attempted') {
                        $track->value = 'notattempted';
                    }       
                    $usertrack->status = $track->value;
                break;  
                case 'cmi.core.score.raw':
                case 'cmi.score.raw':
                    $usertrack->score_raw = $track->value;
                break;  
                case 'cmi.core.session_time':
                case 'cmi.session_time':
                    $usertrack->session_time = $track->value;
                break;  
                case 'cmi.core.total_time':
                case 'cmi.total_time':
                    $usertrack->total_time = $track->value;
                break;  
            }       
            if (isset($track->timemodified) && ($track->timemodified > $usertrack->timemodified)) {
                $usertrack->timemodified = $track->timemodified;
            }       
        }       
        return $usertrack;
    } else {
        return false;
    }
}


/// Library of functions and constants for parsing packages

function scorm_parse($scorm) {
    global $CFG;

    // Parse scorm manifest
    if ($scorm->pkgtype == 'AICC') {
        $scorm->launch = scorm_parse_aicc($scorm->dir.'/'.$scorm->id,$scorm->id);
    } else {
        if (basename($scorm->reference) != 'imsmanifest.xml') {
            $scorm->launch = scorm_parse_scorm($scorm->dir.'/'.$scorm->id,$scorm->id);
        } else {
            $scorm->launch = scorm_parse_scorm($CFG->dataroot.'/'.$scorm->course.'/'.dirname($scorm->reference),$scorm->id);
        }
    }

    return $scorm->launch;
}

/**
* Take the header row of an AICC definition file
* and returns sequence of columns and a pointer to
* the sco identifier column.
*
* @param string $row AICC header row
* @param string $mastername AICC sco identifier column
* @return mixed
*/
function scorm_get_aicc_columns($row,$mastername='system_id') {
    $tok = strtok(strtolower($row),"\",\n\r");
    $result->columns = array();
    $i=0;
    while ($tok) {
        if ($tok !='') {
            $result->columns[] = $tok;
            if ($tok == $mastername) {
                $result->mastercol = $i;
            }
            $i++;
        }
        $tok = strtok("\",\n\r");
    }
    return $result;
}

/**
* Given a colums array return a string containing the regular
* expression to match the columns in a text row.
*
* @param array $column The header columns
* @param string $remodule The regular expression module for a single column
* @return string
*/
function scorm_forge_cols_regexp($columns,$remodule='(".*")?,') {
    $regexp = '/^';
    foreach ($columns as $column) {
        $regexp .= $remodule;
    }
    $regexp = substr($regexp,0,-1) . '/';
    return $regexp;
}

function scorm_parse_aicc($pkgdir,$scormid){
    $version = 'AICC';
    $ids = array();
    $courses = array();
    if ($handle = opendir($pkgdir)) {
        while (($file = readdir($handle)) !== false) {
            $ext = substr($file,strrpos($file,'.'));
            $extension = strtolower(substr($ext,1));
            $id = strtolower(basename($file,$ext));
            $ids[$id]->$extension = $file;
        }
        closedir($handle);
    }
    foreach ($ids as $courseid => $id) {
        if (isset($id->crs)) {
            if (is_file($pkgdir.'/'.$id->crs)) {
                $rows = file($pkgdir.'/'.$id->crs);
                foreach ($rows as $row) {
                    if (preg_match("/^(.+)=(.+)$/",$row,$matches)) {
                        switch (strtolower(trim($matches[1]))) {
                            case 'course_id':
                                $courses[$courseid]->id = trim($matches[2]);
                            break;
                            case 'course_title':
                                $courses[$courseid]->title = trim($matches[2]);
                            break;
                            case 'version':
                                $courses[$courseid]->version = 'AICC_'.trim($matches[2]);
                            break;
                        }
                    }
                }
            }
        }
        if (isset($id->des)) {
            $rows = file($pkgdir.'/'.$id->des);
            $columns = scorm_get_aicc_columns($rows[0]);
            $regexp = scorm_forge_cols_regexp($columns->columns);
            for ($i=1;$i<count($rows);$i++) {
                if (preg_match($regexp,$rows[$i],$matches)) {
                    for ($j=0;$j<count($columns->columns);$j++) {
                        $column = $columns->columns[$j];
                        $courses[$courseid]->elements[substr(trim($matches[$columns->mastercol+1]),1,-1)]->$column = substr(trim($matches[$j+1]),1,-1);
                    }
                }
            }
        }
        if (isset($id->au)) {
            $rows = file($pkgdir.'/'.$id->au);
            $columns = scorm_get_aicc_columns($rows[0]);
            $regexp = scorm_forge_cols_regexp($columns->columns);
            for ($i=1;$i<count($rows);$i++) {
                if (preg_match($regexp,$rows[$i],$matches)) {
                    for ($j=0;$j<count($columns->columns);$j++) {
                        $column = $columns->columns[$j];
                        $courses[$courseid]->elements[substr(trim($matches[$columns->mastercol+1]),1,-1)]->$column = substr(trim($matches[$j+1]),1,-1);
                    }
                }
            }
        }
        if (isset($id->cst)) {
            $rows = file($pkgdir.'/'.$id->cst);
            $columns = scorm_get_aicc_columns($rows[0],'block');
            $regexp = scorm_forge_cols_regexp($columns->columns,'(.+)?,');
            for ($i=1;$i<count($rows);$i++) {
                if (preg_match($regexp,$rows[$i],$matches)) {
                    for ($j=0;$j<count($columns->columns);$j++) {
                        if ($j != $columns->mastercol) {
                            $courses[$courseid]->elements[substr(trim($matches[$j+1]),1,-1)]->parent = substr(trim($matches[$columns->mastercol+1]),1,-1);
                        }
                    }
                }
            }
        }
        if (isset($id->ort)) {
            $rows = file($pkgdir.'/'.$id->ort);
        }
        if (isset($id->pre)) {
            $rows = file($pkgdir.'/'.$id->pre);
            $columns = scorm_get_aicc_columns($rows[0],'structure_element');
            $regexp = scorm_forge_cols_regexp($columns->columns,'(.+),');
            for ($i=1;$i<count($rows);$i++) {
                if (preg_match($regexp,$rows[$i],$matches)) {
                    $courses[$courseid]->elements[$columns->mastercol+1]->prerequisites = substr(trim($matches[1-$columns->mastercol+1]),1,-1);
                }
            }
        }
        if (isset($id->cmp)) {
            $rows = file($pkgdir.'/'.$id->cmp);
        }
    }
    //print_r($courses);
    $launch = 0;
    if (isset($courses)) {
        foreach ($courses as $course) {
            unset($sco);
            $sco->identifier = $course->id;
            $sco->scorm = $scormid;
            $sco->organization = '';
            $sco->title = $course->title;
            $sco->parent = '/';
            $sco->launch = '';
            $sco->scormtype = '';
            //print_r($sco);
            $id = insert_record('scorm_scoes',$sco);
            if ($launch == 0) {
                $launch = $id;
            }
            if (isset($course->elements)) {
                foreach($course->elements as $element) {
                    unset($sco);
                    $sco->identifier = $element->system_id;
                    $sco->scorm = $scormid;
                    $sco->organization = $course->id;
                    $sco->title = $element->title;
                    if (strtolower($element->parent) == 'root') {
                        $sco->parent = '/';
                    } else {
                        $sco->parent = $element->parent;
                    }
                    if (isset($element->file_name)) {
                        $sco->launch = $element->file_name;
                        $sco->scormtype = 'sco';
                    } else {
                        $element->file_name = '';
                        $sco->scormtype = '';
                    }
                    if (!isset($element->prerequisites)) {
                        $element->prerequisites = '';
                    }
                    $sco->prerequisites = $element->prerequisites;
                    if (!isset($element->max_time_allowed)) {
                        $element->max_time_allowed = '';
                    }
                    $sco->maxtimeallowed = $element->max_time_allowed;
                    if (!isset($element->time_limit_action)) {
                        $element->time_limit_action = '';
                    }
                    $sco->timelimitaction = $element->time_limit_action;
                    if (!isset($element->mastery_score)) {
                        $element->mastery_score = '';
                    }
                    $sco->masteryscore = $element->mastery_score;
                    $sco->previous = 0;
                    $sco->next = 0;
                    $id = insert_record('scorm_scoes',$sco);
                    if ($launch==0) {
                        $launch = $id;
                    }
                }
            }
        }
    }
    set_field('scorm','version','AICC','id',$scormid);
    return $launch;
}

function scorm_get_resources($blocks) {
    foreach ($blocks as $block) {
        if ($block['name'] == 'RESOURCES') {
            foreach ($block['children'] as $resource) {
                if ($resource['name'] == 'RESOURCE') {
                    $resources[addslashes($resource['attrs']['IDENTIFIER'])] = $resource['attrs'];
                }
            }
        }
    }
    return $resources;
}

function scorm_get_manifest($blocks,$scoes) {
    static $parents = array();
    static $resources;

    static $manifest;
    static $organization;

    if (count($blocks) > 0) {
        foreach ($blocks as $block) {
            switch ($block['name']) {
                case 'METADATA':
                    if (isset($block['children'])) {
                        foreach ($block['children'] as $metadata) {
                            if ($metadata['name'] == 'SCHEMAVERSION') {
                                if (empty($scoes->version)) {
                                    if (preg_match("/^(1\.2)$|^(CAM )?(1\.3)$/",$metadata['tagData'],$matches)) {
                                        $scoes->version = 'SCORM_'.$matches[count($matches)-1];
                                    } else {
                                        $scoes->version = 'SCORM_1.2';
                                    }
                                }
                            }
                        }
                    }
                break;
                case 'MANIFEST':
                    $manifest = addslashes($block['attrs']['IDENTIFIER']);
                    $organization = '';
                    $resources = array();
                    $resources = scorm_get_resources($block['children']);
                    $scoes = scorm_get_manifest($block['children'],$scoes);
                    if (count($scoes->elements) <= 0) {
                        foreach ($resources as $item => $resource) {
                            if (!empty($resource['HREF'])) {
                                $sco = new stdClass();
                                $sco->identifier = $item;
                                $sco->title = $item;
                                $sco->parent = '/';
                                $sco->launch = addslashes($resource['HREF']);
                                $sco->scormtype = addslashes($resource['ADLCP:SCORMTYPE']);
                                $scoes->elements[$manifest][$organization][$item] = $sco;
                            }
                        }
                    }
                break;
                case 'ORGANIZATIONS':
                    if (!isset($scoes->defaultorg)) {
                        $scoes->defaultorg = addslashes($block['attrs']['DEFAULT']);
                    }
                    $scoes = scorm_get_manifest($block['children'],$scoes);
                break;
                case 'ORGANIZATION':
                    $identifier = addslashes($block['attrs']['IDENTIFIER']);
                    $organization = '';
                    $scoes->elements[$manifest][$organization][$identifier]->identifier = $identifier;
                    $scoes->elements[$manifest][$organization][$identifier]->parent = '/';
                    $scoes->elements[$manifest][$organization][$identifier]->launch = '';
                    $scoes->elements[$manifest][$organization][$identifier]->scormtype = '';

                    $parents = array();
                    $parent = new stdClass();
                    $parent->identifier = $identifier;
                    $parent->organization = $organization;
                    array_push($parents, $parent);
                    $organization = $identifier;

                    $scoes = scorm_get_manifest($block['children'],$scoes);
                    
                    array_pop($parents);
                break;
                case 'ITEM':
                    $parent = array_pop($parents);
                    array_push($parents, $parent);
                    
                    $identifier = addslashes($block['attrs']['IDENTIFIER']);
                    $scoes->elements[$manifest][$organization][$identifier]->identifier = $identifier;
                    $scoes->elements[$manifest][$organization][$identifier]->parent = $parent->identifier;
                    if (!isset($block['attrs']['ISVISIBLE'])) {
                        $block['attrs']['ISVISIBLE'] = 'true';
                    }
                    $scoes->elements[$manifest][$organization][$identifier]->isvisible = addslashes($block['attrs']['ISVISIBLE']);
                    if (!isset($block['attrs']['PARAMETERS'])) {
                        $block['attrs']['PARAMETERS'] = '';
                    }
                    $scoes->elements[$manifest][$organization][$identifier]->parameters = addslashes($block['attrs']['PARAMETERS']);
                    if (!isset($block['attrs']['IDENTIFIERREF'])) {
                        $scoes->elements[$manifest][$organization][$identifier]->launch = '';
                        $scoes->elements[$manifest][$organization][$identifier]->scormtype = 'asset';
                    } else {
                        $idref = addslashes($block['attrs']['IDENTIFIERREF']);
                        $base = '';
                        if (isset($resources[$idref]['XML:BASE'])) {
                            $base = $resources[$idref]['XML:BASE'];
                        }
                        $scoes->elements[$manifest][$organization][$identifier]->launch = addslashes($base.$resources[$idref]['HREF']);
                        if (empty($resources[$idref]['ADLCP:SCORMTYPE'])) {
                            $resources[$idref]['ADLCP:SCORMTYPE'] = 'asset';
                        }
                        $scoes->elements[$manifest][$organization][$identifier]->scormtype = addslashes($resources[$idref]['ADLCP:SCORMTYPE']);
                    }

                    $parent = new stdClass();
                    $parent->identifier = $identifier;
                    $parent->organization = $organization;
                    array_push($parents, $parent);

                    $scoes = scorm_get_manifest($block['children'],$scoes);
                    
                    array_pop($parents);
                break;
                case 'TITLE':
                    $parent = array_pop($parents);
                    array_push($parents, $parent);
                    $scoes->elements[$manifest][$parent->organization][$parent->identifier]->title = addslashes($block['tagData']);
                break;
                case 'ADLCP:PREREQUISITES':
                    if ($block['attrs']['TYPE'] == 'aicc_script') {
                        $parent = array_pop($parents);
                        array_push($parents, $parent);
                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->prerequisites = addslashes($block['tagData']);
                    }
                break;
                case 'ADLCP:MAXTIMEALLOWED':
                    $parent = array_pop($parents);
                    array_push($parents, $parent);
                    $scoes->elements[$manifest][$parent->organization][$parent->identifier]->maxtimeallowed = addslashes($block['tagData']);
                break;
                case 'ADLCP:TIMELIMITACTION':
                    $parent = array_pop($parents);
                    array_push($parents, $parent);
                    $scoes->elements[$manifest][$parent->organization][$parent->identifier]->timelimitaction = addslashes($block['tagData']);
                break;
                case 'ADLCP:DATAFROMLMS':
                    $parent = array_pop($parents);
                    array_push($parents, $parent);
                    $scoes->elements[$manifest][$parent->organization][$parent->identifier]->datafromlms = addslashes($block['tagData']);
                break;
                case 'ADLCP:MASTERYSCORE':
                    $parent = array_pop($parents);
                    array_push($parents, $parent);
                    $scoes->elements[$manifest][$parent->organization][$parent->identifier]->masteryscore = addslashes($block['tagData']);
                break;
            }
        }
    }

    return $scoes;
}

function scorm_parse_scorm($pkgdir,$scormid) {
    global $CFG;
    
    $launch = 0;
    $manifestfile = $pkgdir.'/imsmanifest.xml';

    if (is_file($manifestfile)) {
    
        $xmlstring = file_get_contents($manifestfile);
        $objXML = new xml2Array();
        $manifests = $objXML->parse($xmlstring);
            
        $scoes = new stdClass();
        $scoes->version = '';
        $scoes = scorm_get_manifest($manifests,$scoes);

        if (count($scoes->elements) > 0) {
            foreach ($scoes->elements as $manifest => $organizations) {
                foreach ($organizations as $organization => $items) {
                    foreach ($items as $identifier => $item) {
                        $item->scorm = $scormid;
                        $item->manifest = $manifest;
                        $item->organization = $organization;
                        $id = insert_record('scorm_scoes',$item);
                
                        if (($launch == 0) && ((empty($scoes->defaultorg)) || ($scoes->defaultorg == $identifier))) {
                            $launch = $id;
                        }
                    }
                }
            }
            set_field('scorm','version',$scoes->version,'id',$scormid);
        }
    } 
    
    return $launch;
}


/* Usage
 Grab some XML data, either from a file, URL, etc. however you want. Assume storage in $strYourXML;

 $objXML = new xml2Array();
 $arrOutput = $objXML->parse($strYourXML);
 print_r($arrOutput); //print it out, or do whatever!
  
*/
class xml2Array {
   
   var $arrOutput = array();
   var $resParser;
   var $strXmlData;
   
   /**
   * Convert a utf-8 string to html entities
   *
   * @param string $str The UTF-8 string
   * @return string
   */
   function utf8_to_entities($str) {
       $entities = '';
       $values = array();
       $lookingfor = 1;

       for ($i = 0; $i < strlen($str); $i++) {
           $thisvalue = ord($str[$i]);
           if ($thisvalue < 128) {
               $entities .= $str[$i]; // Leave ASCII chars unchanged 
           } else {
               if (count($values) == 0) {
                   $lookingfor = ($thisvalue < 224) ? 2 : 3;
               }
               $values[] = $thisvalue;
               if (count($values) == $lookingfor) {
                   $number = ($lookingfor == 3) ?
                       (($values[0] % 16) * 4096) + (($values[1] % 64) * 64) + ($values[2] % 64):
                       (($values[0] % 32) * 64) + ($values[1] % 64);
                   $entities .= '&#' . $number . ';';
                   $values = array();
                   $lookingfor = 1;
               }
           }
       }
       return $entities;
   }

   /**
   * Parse an XML text string and create an array tree that rapresent the XML structure
   *
   * @param string $strInputXML The XML string
   * @return array
   */
   function parse($strInputXML) {
           $this->resParser = xml_parser_create ('UTF-8');
           xml_set_object($this->resParser,$this);
           xml_set_element_handler($this->resParser, "tagOpen", "tagClosed");
           
           xml_set_character_data_handler($this->resParser, "tagData");
       
           $this->strXmlData = xml_parse($this->resParser,$strInputXML );
           if(!$this->strXmlData) {
               die(sprintf("XML error: %s at line %d",
                           xml_error_string(xml_get_error_code($this->resParser)),
                           xml_get_current_line_number($this->resParser)));
           }
                           
           xml_parser_free($this->resParser);
           
           return $this->arrOutput;
   }
   
   function tagOpen($parser, $name, $attrs) {
       $tag=array("name"=>$name,"attrs"=>$attrs); 
       array_push($this->arrOutput,$tag);
   }
   
   function tagData($parser, $tagData) {
       if(trim($tagData)) {
           if(isset($this->arrOutput[count($this->arrOutput)-1]['tagData'])) {
               $this->arrOutput[count($this->arrOutput)-1]['tagData'] .= $this->utf8_to_entities($tagData);
           } else {
               $this->arrOutput[count($this->arrOutput)-1]['tagData'] = $this->utf8_to_entities($tagData);
           }
       }
   }
   
   function tagClosed($parser, $name) {
       $this->arrOutput[count($this->arrOutput)-2]['children'][] = $this->arrOutput[count($this->arrOutput)-1];
       array_pop($this->arrOutput);
   }
}
?>