<?php

    // THESE CONSTANTS ARE USED FOR THE REPORTING PAGE.

    define('STATS_REPORT_LOGINS',1); // double impose logins and unqiue logins on a line graph. site course only.
    define('STATS_REPORT_READS',2); // double impose student reads and teacher reads on a line graph. 
    define('STATS_REPORT_WRITES',3); // double impose student writes and teacher writes on a line graph.
    define('STATS_REPORT_ACTIVITY',4); // 2+3 added up, teacher vs student.
    define('STATS_REPORT_STUDENTACTIVITY',5); // all student activity, reads vs writes
    define('STATS_REPORT_TEACHERACTIVITY',6); // all teacher activity, reads vs writes

    // user level stats reports.
    define('STATS_REPORT_USER_ACTIVITY',7);
    define('STATS_REPORT_USER_ALLACTIVITY',8);
    define('STATS_REPORT_USER_LOGINS',9);
    define('STATS_REPORT_USER_VIEW',10);  // this is the report you see on the user profile.

    // start after 0 = show dailies.
    define('STATS_TIME_LASTWEEK',1);
    define('STATS_TIME_LAST2WEEKS',2);
    define('STATS_TIME_LAST3WEEKS',3);
    define('STATS_TIME_LAST4WEEKS',4);

    // start after 10 = show weeklies
    define('STATS_TIME_LAST2MONTHS',12);

    define('STATS_TIME_LAST3MONTHS',13);
    define('STATS_TIME_LAST4MONTHS',14);
    define('STATS_TIME_LAST5MONTHS',15);
    define('STATS_TIME_LAST6MONTHS',16);

    // start after 20 = show monthlies
    define('STATS_TIME_LAST7MONTHS',27);
    define('STATS_TIME_LAST8MONTHS',28);
    define('STATS_TIME_LAST9MONTHS',29);
    define('STATS_TIME_LAST10MONTHS',30);
    define('STATS_TIME_LAST11MONTHS',31);
    define('STATS_TIME_LASTYEAR',32);

    // different modes for what reports to offer
    define('STATS_MODE_GENERAL',1);
    define('STATS_MODE_DETAILED',2);

    // return codes - whether to rerun
    define('STATS_RUN_COMPLETE',1);
    define('STATS_RUN_ABORTED',0);

function stats_cron_daily () {
    global $CFG;

    if (empty($CFG->enablestats)) {
        return;
    }

    if (!$timestart = stats_get_start_from('daily')) {
        return;
    }


    // check to make sure we're due to run, at least one day after last run
    $midnight = usergetmidnight(time());
    
    if ($midnight <= $CFG->statslastdaily) {
        return;
    }

    //and we're not before our runtime
    if ((date('G') <= $CFG->statsruntimestarthour) && (date('i') < $CFG->statsruntimestartminute)) {
        return;
    }

    mtrace("Running daily statisics gathering...");
    set_config('statslastdaily',time());


    $return = STATS_RUN_COMPLETE; // optimistic

    static $daily_modules;
    
    if (empty($daily_modules)) {
        $mods = get_records("modules");
        foreach ($mods as $mod) {
            require_once($CFG->dirroot.'/mod/'.$mod->name.'/lib.php');
            $fname = $mod->name.'_get_daily_stats';
            if (function_exists($fname)) {
                $daily_modules[$mod] = $fname;
            }
        }
    }

    $nextmidnight = $timestart + (60*60*24);

    if (!$courses = get_records('course','','','','id,1')) {
        return;
    }
    
    $days = 0;
    while ($midnight >= $nextmidnight) {

        $timesql = " (l.time > $timestart AND l.time < $nextmidnight) ";
            
        foreach ($courses as $course) {

            $stat->students = count_records('user_students','course',$course->id);
            $stat->teachers = count_records('user_teachers','course',$course->id);
            
            $sql = 'SELECT COUNT(DISTINCT(l.userid)) FROM '.$CFG->prefix.'log l JOIN '.$CFG->prefix
                .'user_students us ON us.userid = l.userid WHERE l.course = '.$course->id.' AND us.course = '.$course->id.' AND '.$timesql;
            $stat->activestudents = count_records_sql($sql);
            
            $sql = str_replace('students','teachers',$sql);
            $stat->activeteachers = count_records_sql($sql);
            
            $sql = 'SELECT COUNT(l.id) FROM '.$CFG->prefix.'log l JOIN '.$CFG->prefix
                .'user_students us ON us.userid = l.userid WHERE l.course = '.$course->id.' AND us.course = '.$course->id
                .' AND '.$timesql .' '.stats_get_action_sql_in('view');
            $stat->studentreads = count_records_sql($sql);
            
            $sql = str_replace('students','teachers',$sql);
            $stat->teacherreads = count_records_sql($sql);

            $sql = 'SELECT COUNT(l.id) FROM '.$CFG->prefix.'log l JOIN '.$CFG->prefix
                .'user_students us ON us.userid = l.userid WHERE l.course = '.$course->id.' AND us.course = '.$course->id
                .' AND '.$timesql.' '.stats_get_action_sql_in('post');
            $stat->studentwrites = count_records_sql($sql);
            
            $sql = str_replace('students','teachers',$sql);
            $stat->teacherwrites = count_records_sql($sql);

            $stat->logins = 0;
            $stat->uniquelogins = 0;
            if ($course->id == SITEID) {
                $sql = 'SELECT count(l.id) FROM '.$CFG->prefix.'log l WHERE l.action = \'login\' AND '.$timesql;
                $stat->logins = count_records_sql($sql);
                $sql = 'SELECT COUNT(DISTINCT(l.userid)) FROM '.$CFG->prefix.'log l WHERE l.action = \'login\' AND '.$timesql;
                $stat->uniquelogins = count_records_sql($sql);
            }

            $stat->courseid = $course->id;
            $stat->timeend = $nextmidnight;
            insert_record('stats_daily',$stat,false); // don't worry about the return id, we don't need it.

            $students = array();
            $teachers = array();

            // now do logins.
            if ($course->id == SITEID) {
                $sql = 'SELECT count(l.id) as count,l.userid FROM '.$CFG->prefix.'log l WHERE action = \'login\' AND '.$timesql.' GROUP BY userid';
                
                $logins = get_records_sql($sql);
                foreach ($logins as $l) {
                    $stat->reads = $l->count;
                    $stat->userid = $l->userid;
                    $stat->timeend = $nextmidnight;
                    $stat->courseid = SITEID;
                    $stat->writes = 0;
                    $stat->stattype = 'logins';
                    $stat->roleid = 1; 
                    insert_record('stats_user_daily',$stat,false);
                }
            }


            stats_get_course_users($course,$timesql,$students,$teachers);

            foreach ($students as $user) {
                stats_do_daily_user_cron($course,$user,1,$timesql,$nextmidnight,'daily',$daily_mods);
            }
            foreach ($teachers as $user) {
                stats_do_daily_user_cron($course,$user,2,$timesql,$nextmidnight,'daily',$daily_mods);
            }
        }
        $timestart = $nextmidnight;
        $nextmidnight = $nextmidnight + (60*60*24);
        $days++;

        if (!stats_check_runtime()) {
            mtrace("Stopping early! reached maxruntime");
            $return = STATS_RUN_ABORTED;
            break;
        }
    }
    mtrace("Completed $days days");
    return $return;

}


function stats_cron_weekly () {

    global $CFG;

    if (empty($CFG->enablestats)) {
        return;
    }

    if (!$timestart = stats_get_start_from('weekly')) {
        return;
    }
    
    // check to make sure we're due to run, at least one week after last run
    $sunday = stats_get_base_weekly(); 

    if ($sunday <= $CFG->statslastweekly) {
        return;
    }

    //and we're not before our runtime
    if ((date('G') <= $CFG->statsruntimestarthour) && (date('i') < $CFG->statsruntimestartminute)) {
        return;
    }

    mtrace("Running weekly statisics gathering...");
    set_config('statslastweekly',time());

    $return = STATS_RUN_COMPLETE; // optimistic

    static $weekly_modules;
    
    if (empty($weekly_modules)) {
        $mods = get_records("modules");
        foreach ($mods as $mod) {
            require_once($CFG->dirroot.'/mod/'.$mod->name.'/lib.php');
            $fname = $mod->name.'_get_weekly_stats';
            if (function_exists($fname)) {
                $weekly_modules[$mod] = $fname;
            }
        }
    }

    $nextsunday = $timestart + (60*60*24*7);

    if (!$courses = get_records('course','','','','id,1')) {
        return;
    }
    
    $weeks = 0;
    while ($sunday >= $nextsunday) {

        $timesql = " (timeend > $timestart AND timeend < $nextsunday) ";

        foreach ($courses as $course) {
            
            $sql = 'SELECT ceil(avg(students)) as students, ceil(avg(teachers)) as teachers, 
                       ceil(avg(activestudents)) as activestudents,ceil(avg(activeteachers)) as activeteachers,
                       sum(studentreads) as studentreads, sum(studentwrites) as studentwrites,
                       sum(teacherreads) as teacherreads, sum(teacherwrites) as teacherwrites,
                       sum(logins) as logins FROM '.$CFG->prefix.'stats_daily WHERE courseid = '.$course->id.' AND '.$timesql;

            $stat = get_record_sql($sql);
            
            $stat->uniquelogins = 0;
            if ($course->id == SITEID) {
                $sql = 'SELECT COUNT(DISTINCT(l.userid)) FROM '.$CFG->prefix.'log l WHERE l.action = \'login\' AND '
                    .str_replace('timeend','time',$timesql);
                $stat->uniquelogins = count_records_sql($sql);
            }
            
            $stat->courseid = $course->id;
            $stat->timeend = $nextsunday;
            insert_record('stats_weekly',$stat,false); // don't worry about the return id, we don't need it.

            $students = array();
            $teachers = array();

            stats_get_course_users($course,$timesql,$students,$teachers);

            foreach ($students as $user) {
                stats_do_aggregate_user_cron($course,$user,1,$timesql,$nextsunday,'weekly',$weekly_mods);
            }

            foreach ($teachers as $user) {
                stats_do_aggregate_user_cron($course,$user,2,$timesql,$nextsunday,'weekly',$weekly_mods);
            }
        }

        $timestart = $nextsunday;
        $nextsunday = $nextsunday + (60*60*24*7);
        $weeks++;

        if (!stats_check_runtime()) {
            mtrace("Stopping early! reached maxruntime");
            $return = STATS_RUN_ABORTED;
            break;
        }
    }
    mtrace("Completed $weeks weeks");
    return $return;
}
    

function stats_cron_monthly () {
    global $CFG;

    if (empty($CFG->enablestats)) {
        return;
    }

    if (!$timestart = stats_get_start_from('monthly')) {
        return;
    }
    
    // check to make sure we're due to run, at least one month after last run
    $monthend = stats_get_base_monthly();
    
    if ($monthend <= $CFG->statslastmonthly) {
        return;
    }
    
    //and we're not before our runtime
    if ((date('G') <= $CFG->statsruntimestarthour) && (date('i') < $CFG->statsruntimestartminute)) {
        return;
    }

    mtrace("Running monthly statisics gathering...");
    set_config('statslastmonthly',time());

    $return = STATS_RUN_COMPLETE; // optimistic

    static $monthly_modules;
    
    if (empty($monthly_modules)) {
        $mods = get_records("modules");
        foreach ($mods as $mod) {
            require_once($CFG->dirroot.'/mod/'.$mod->name.'/lib.php');
            $fname = $mod->name.'_get_monthly_stats';
            if (function_exists($fname)) {
                $monthly_modules[$mod] = $fname;
            }
        }
    }
    
    $nextmonthend = stats_get_next_monthend($timestart);

    if (!$courses = get_records('course','','','','id,1')) {
        return;
    }
    
    $months = 0;
    while ($monthend >= $nextmonthend) {

        $timesql = " (timeend > $timestart AND timeend < $nextmonthend) ";

        foreach ($courses as $course) {

            $sql = 'SELECT ceil(avg(students)) as students, ceil(avg(teachers)) as teachers, ceil(avg(activestudents)) as activestudents,ceil(avg(activeteachers)) as activeteachers,
                       sum(studentreads) as studentreads, sum(studentwrites) as studentwrites, sum(teacherreads) as teacherreads, sum(teacherwrites) as teacherwrites,
                       sum(logins) as logins FROM '.$CFG->prefix.'stats_daily WHERE courseid = '.$course->id.' AND '.$timesql;
            
            $stat->uniquelogins = 0;
            if ($course->id == SITEID) {
                $sql = 'SELECT COUNT(DISTINCT(l.userid)) FROM '.$CFG->prefix.'log l WHERE l.action = \'login\' AND '.str_replace('timeend','time',$timesql);
                $stat->uniquelogins = count_records_sql($sql);
            }
            
            $stat->courseid = $course->id;
            $stat->timeend = $nextmonthend;
            insert_record('stats_monthly',$stat,false); // don't worry about the return id, we don't need it.

            $students = array();
            $teachers = array();
            
            stats_get_course_users($course,$timesql,$students,$teachers);
            
            foreach ($students as $user) {
                stats_do_aggregate_user_cron($course,$user,1,$timesql,$nextmonthend,'monthly',$monthly_mods);
            }
            
            foreach ($teachers as $user) {
                stats_do_aggregate_user_cron($course,$user,2,$timesql,$nextmonthend,'monthly',$monthly_mods);
            }
        }

        $timestart = $nextmonthend;
        $nextmonthend = stats_get_next_monthend($timestart);
        $months++;
        if (!stats_check_runtime()) {
            mtrace("Stopping early! reached maxruntime");
            break;
            $return = STATS_RUN_ABORTED;
        }
    }
    mtrace("Completed $months months");
    return $return;
}

function stats_get_start_from($str) {
    global $CFG;

    // if it's not our first run, just return the most recent.
    if ($timeend = get_field_sql('SELECT timeend FROM '.$CFG->prefix.'stats_'.$str.' ORDER BY timeend DESC LIMIT 1')) {
        return $timeend;
    }
    
    // decide what to do based on our config setting (either all or none or a timestamp)
    $function = 'stats_get_base_'.$str;
    switch ($CFG->statsfirstrun) {
        case 'all': 
            return $function(get_field_sql('SELECT time FROM '.$CFG->prefix.'log ORDER BY time LIMIT 1'));
            break;
        case 'none'; 
            return $function(time()-(60*60*24));
            break;
        default:
            if (is_numeric($CFG->statsfirstrun)) {
                return $function(time() - $CFG->statsfirstrun);
            }
            return false;
            break;
    }
}

function stats_get_base_daily($time=0) {
    if (empty($time)) {
        $time = time();
    }
    return usergetmidnight($time);
}

function stats_get_base_weekly($time=0) {
    if (empty($time)) {
        $time = time();
    }
    // if we're currently a monday, last monday will take us back a week
    $str = 'last monday';
    if (date('D',$time) == 'Mon')
        $str = 'now';

    return usergetmidnight(strtotime($str,$time));
}

function stats_get_base_monthly($time=0) {
    if (empty($time)) {
        $time = time();
    }
    return usergetmidnight(strtotime(date('1-M-Y',$time)));
}

function stats_get_next_monthend($lastmonth) {
    return usergetmidnight(strtotime(date('1-M-Y',$lastmonth).' + 1 month'));
}

function stats_clean_old() {
    // delete dailies older than 2 months (to be safe)
    $deletebefore = stats_get_next_monthend(strtotime('-2 months',time()));
    delete_records_select('stats_daily',"timeend < $deletebefore");
    delete_records_select('stats_user_daily',"timeend < $deletebefore");
    
    // delete weeklies older than 8 months (to be safe)
    $deletebefore = stats_get_next_monthend(strtotime('-8 months',time()));
    delete_records_select('stats_weekly',"timeend < $deletebefore");
    delete_records_select('stats_user_weekly',"timeend < $deletebefore");

    // don't delete monthlies
}

function stats_get_parameters($time,$report) {
    if ($time < 10) { // dailies
        // number of days to go back = 7* time
        $param->limit = 7*$time;
        $param->table = 'daily';
        $param->timeafter = strtotime("-".($time*7)." days",stats_get_base_daily());
    } elseif ($time < 20) { // weeklies
        // number of weeks to go back = time - 10 * 4 (weeks) + base week
        $param->limit = ($time - 10) * 4;
        $param->table = 'weekly';
        $param->timeafter = strtotime("-".($time - 10)." weeks",stats_get_base_weekly());
    } else { // monthlies.
        // number of months to go back = time - 20 * months + base month
        $param->limit = $time - 20;
        $param->table = 'monthly';
        $param->timeafter = strtotime("-".($time - 20)." months",stats_get_base_monthly());
    }

    switch ($report) {
    case STATS_REPORT_LOGINS:
        $param->fields = 'logins as line1,uniquelogins as line2';
        $param->line1 = get_string('statslogins');
        $param->line2 = get_string('statsuniquelogins');
        break;
    case STATS_REPORT_READS:
        $param->fields = 'studentreads as line1,teacherreads as line2';
        $param->line1 = get_string('statsstudentreads');
        $param->line2 = get_string('statsteacherreads');
        break;
    case STATS_REPORT_WRITES:
        $param->fields = 'studentwrites as line1,teacherwrites as line2';
        $param->line1 = get_string('statsstudentwrites');
        $param->line2 = get_string('statsteacherwrites');
        break;
    case STATS_REPORT_ACTIVITY:
        $param->fields = 'studentreads+studentwrites as line1, teacherreads+teacherwrites as line2';
        $param->line1 = get_string('statsstudentactivity');
        $param->line2 = get_string('statsteacheractivity');
        break;
    case STATS_REPORT_STUDENTACTIVITY:
        $param->fields = 'studentreads as line1,studentwrites as line2';
        $param->line1 = get_string('statsstudentreads');
        $param->line2 = get_string('statsstudentwrites');
        break;
    case STATS_REPORT_TEACHERACTIVITY:
        $param->fields = 'teacherreads as line1,teacherwrites as line2';
        $param->line1 = get_string('statsteacherreads');
        $param->line2 = get_string('statsteacherwrites');
        break;
    case STATS_REPORT_USER_ACTIVITY:
        $param->fields = 'reads as line1, writes as line2';
        $param->line1 = get_string('statsuserreads');
        $param->line2 = get_string('statsuserwrites');
        $param->stattype = 'activity';
        break;
    case STATS_REPORT_USER_ALLACTIVITY:
        $param->fields = 'reads+writes as line1';
        $param->line1 = get_string('statsuseractivity');
        $param->stattype = 'activity';
        break;
    case STATS_REPORT_USER_LOGINS:
        $param->fields = 'reads as line1';
        $param->line1 = get_string('statsuserlogins');
        $param->stattype = 'logins';
        break;
    case STATS_REPORT_USER_VIEW:
        $param->fields = 'reads as line1, writes as line2, reads+writes as line3';
        $param->line1 = get_string('statsuserreads');
        $param->line2 = get_string('statsuserwrites');
        $param->line3 = get_string('statsuseractivity');
        $param->stattype = 'activity';
        break;
    }
    
    return $param;
} 

function stats_get_view_actions() {
    return array('view','view all','history');
}

function stats_get_post_actions() {
    return array('add','delete','edit','add mod','delete mod','edit section'.'enrol','loginas','new','unenrol','update','update mod');
}

function stats_get_action_sql_in($str) {
    global $CFG;
    
    $mods = get_records('modules');
    $function = 'stats_get_'.$str.'_actions';
    $actions = $function();
    foreach ($mods as $mod) {
        require_once($CFG->dirroot.'/mod/'.$mod->name.'/lib.php');
        $function = $mod->name.'_get_'.$str.'_actions';
        if (function_exists($function)) {
            $actions = array_merge($actions,$function());
        }
    }
    $actions = array_unique($actions);
    if (empty($actions)) {
        return ' ';
    } else if (count($actions) == 1) {
        return ' AND l.action = '.array_pop($actions).' ';
    } else {
        return ' AND l.action IN (\''.implode('\',\'',$actions).'\') ';
    }
}


function stats_get_course_users($course,$timesql,&$students, &$teachers) {
    global $CFG;
    
    $timesql = str_replace('timeend','l.time',$timesql);

    $sql = 'SELECT DISTINCT(l.userid) as userid,1 as roleid FROM '.$CFG->prefix.'log l JOIN '.$CFG->prefix
        .'user_students us ON us.userid = l.userid WHERE l.course = '.$course->id.' AND us.course = '.$course->id.' AND '.$timesql;
    $students = get_records_sql($sql);

    $sql = str_replace('students','teachers',$sql);
    $sql = str_replace(',1',',2',$sql);

    $teachers = get_records_sql($sql);

}

function stats_do_daily_user_cron($course,$user,$roleid,$timesql,$timeend,$mods) {

    global $CFG;

    $stat = new StdClass;
    $stat->userid   = $user->userid;
    $stat->roleid   = $roleid;
    $stat->courseid = $course->id;
    $stat->stattype = 'activity';
    $stat->timeend  = $timeend;
    
    $sql = 'SELECT COUNT(l.id) FROM '.$CFG->prefix.'log l WHERE l.userid = '.$user->userid
        .' AND  l.course = '.$course->id
        .' AND '.$timesql .' '.stats_get_action_sql_in('view');

    $stat->reads  = count_records_sql($sql);
    
    $sql = 'SELECT COUNT(l.id) FROM '.$CFG->prefix.'log l WHERE l.userid = '.$user->userid
        .' AND l.course = '.$course->id
        .' AND '.$timesql.' '.stats_get_action_sql_in('post');

    $stat->writes = count_records_sql($sql);
                
    insert_record('stats_user_daily',$stat,false);

    // now ask the modules if they want anything.
    foreach ($mods as $mod => $fname) {
        mtrace('  doing daily statistics for '.$mod->name);
        $fname($course,$user,$timeend,($roleid == 'student') ? 1 : 2);
    }
}

function stats_do_aggregate_user_cron($course,$user,$roleid,$timesql,$timeend,$timestr,$mods) {

    global $CFG;

    $stat = new StdClass;
    $stat->userid   = $user->userid;
    $stat->roleid   = $roleid;
    $stat->courseid = $course->id;
    $stat->stattype = 'activity';
    $stat->timeend  = $timeend;

    $sql = 'SELECT sum(reads) as reads, sum(writes) as writes FROM '.$CFG->prefix.'stats_user_daily WHERE courseid = '.$course->id.' AND '.$timesql
        ." AND roleid=2 AND userid = ".$stat->userid." AND stattype='activity'"; // add on roleid in case they have teacher and student records.
    
    $r = get_record_sql($sql);
    $stat->reads = (empty($r->reads)) ? 0 : $r->reads;
    $stat->writes = (empty($r->writes)) ? 0 : $r->writes;
    
    insert_record('stats_user_'.$timestr,$stat,false);

    if ($course->id == SITEID) {
        $sql = 'SELECT sum(reads) as reads, sum(writes) as writes FROM '.$CFG->prefix.'stats_user_daily WHERE courseid = '.$course->id.' AND '.$timesql
            ." AND roleid=2 AND userid = ".$stat->userid." AND stattype='logins'"; // add on roleid in case they have teacher and student records.
        
        $r = get_record_sql($sql);
        $stat->reads = (empty($r->reads)) ? 0 : $r->reads;
        $stat->writes = (empty($r->writes)) ? 0 : $r->writes;
        
        $stat->stattype = 'logins';
        
        insert_record('stats_user_'.$timestr,$stat,false);
    }
 
    // now ask the modules if they want anything.
    foreach ($mods as $mod => $fname) {
        mtrace('  doing '.$timestr.' statistics for '.$mod->name);
        $fname($course,$user,$timeend,($roleid == 'student') ? 1 : 2);
    }
}

function stats_get_time_options($now,$lastweekend,$lastmonthend,$earliestday,$earliestweek,$earliestmonth) {

    $now = stats_get_base_daily(time());
    // it's really important that it's TIMEEND in the table. ie, tuesday 00:00:00 is monday night.
    // so we need to take a day off here (essentially add a day to $now
    $now += 60*60*24;

    if ($now - (60*60*24*7) >= $earliestday) {
        $timeoptions[STATS_TIME_LASTWEEK] = get_string('numweeks','moodle',1);
    }
    if ($now - (60*60*24*14) >= $earliestday) {
        $timeoptions[STATS_TIME_LAST2WEEKS] = get_string('numweeks','moodle',2);
    }
    if ($now - (60*60*24*21) >= $earliestday) {
        $timeoptions[STATS_TIME_LAST3WEEKS] = get_string('numweeks','moodle',3); 
    }
    if ($now - (60*60*24*28) >= $earliestday) {
        $timeoptions[STATS_TIME_LAST4WEEKS] = get_string('numweeks','moodle',4);// show dailies up to (including) here.
    }
    if ($lastweekend - (60*60*24*56) >= $earliestweek) {
        $timeoptions[STATS_TIME_LAST2MONTHS] = get_string('nummonths','moodle',2);
    }
    if ($lastweekend - (60*60*24*84) >= $earliestweek) {
        $timeoptions[STATS_TIME_LAST3MONTHS] = get_string('nummonths','moodle',3);
    }
    if ($lastweekend - (60*60*24*112) >= $earliestweek) {
        $timeoptions[STATS_TIME_LAST4MONTHS] = get_string('nummonths','moodle',4);
    }
    if ($lastweekend - (60*60*24*140) >= $earliestweek) {
        $timeoptions[STATS_TIME_LAST5MONTHS] = get_string('nummonths','moodle',5);
    }
    if ($lastweekend - (60*60*24*168) >= $earliestweek) {
        $timeoptions[STATS_TIME_LAST6MONTHS] = get_string('nummonths','moodle',6); // show weeklies up to (including) here
    }
    if (strtotime('-7 months',$lastmonthend) >= $earliestmonth) {
        $timeoptions[STATS_TIME_LAST7MONTHS] = get_string('nummonths','moodle',7);
    }
    if (strtotime('-8 months',$lastmonthend) >= $earliestmonth) {
        $timeoptions[STATS_TIME_LAST8MONTHS] = get_string('nummonths','moodle',8);
    }
    if (strtotime('-9 months',$lastmonthend) >= $earliestmonth) {
        $timeoptions[STATS_TIME_LAST9MONTHS] = get_string('nummonths','moodle',9);
    }
    if (strtotime('-10 months',$lastmonthend) >= $earliestmonth) {
        $timeoptions[STATS_TIME_LAST10MONTHS] = get_string('nummonths','moodle',10);
    }
    if (strtotime('-11 months',$lastmonthend) >= $earliestmonth) {
        $timeoptions[STATS_TIME_LAST11MONTHS] = get_string('nummonths','moodle',11);
    }
    if (strtotime('-1 year',$lastmonthend) >= $earliestmonth) {
        $timeoptions[STATS_TIME_LASTYEAR] = get_string('lastyear');
    }

    return $timeoptions;
}

function stats_get_report_options($courseid,$mode) {
    
    $reportoptions = array();

    switch ($mode) {
    case STATS_MODE_GENERAL:
        $reportoptions[STATS_REPORT_ACTIVITY] = get_string('statsreport'.STATS_REPORT_ACTIVITY);
        $reportoptions[STATS_REPORT_STUDENTACTIVITY] = get_string('statsreport'.STATS_REPORT_STUDENTACTIVITY);
        $reportoptions[STATS_REPORT_TEACHERACTIVITY] = get_string('statsreport'.STATS_REPORT_TEACHERACTIVITY);
        $reportoptions[STATS_REPORT_READS] = get_string('statsreport'.STATS_REPORT_READS);
        $reportoptions[STATS_REPORT_WRITES] = get_string('statsreport'.STATS_REPORT_WRITES);
        if ($courseid == SITEID) {
            $reportoptions[STATS_REPORT_LOGINS] = get_string('statsreport'.STATS_REPORT_LOGINS);
        }
        
        break;
    case STATS_MODE_DETAILED:
        $reportoptions[STATS_REPORT_USER_ACTIVITY] = get_string('statsreport'.STATS_REPORT_USER_ACTIVITY);
        $reportoptions[STATS_REPORT_USER_ALLACTIVITY] = get_string('statsreport'.STATS_REPORT_USER_ALLACTIVITY);
        if (isadmin()) {
            $site = get_site();
            $reportoptions[STATS_REPORT_USER_LOGINS] = get_string('statsreport'.STATS_REPORT_USER_LOGINS);
        }
    }
  
    return $reportoptions;
}

function stats_fix_zeros($stats,$timeafter,$timestr,$line2=true,$line3=false) {

    $now = time();
    $timestr = str_replace('user_','',$timestr); // just in case.

    $times = array();
    // add something to timeafter since it is our absolute base
    $timeafter += 60*60*24;
    while ($timeafter < $now) {
        $times[] = $timeafter;
        if ($timestr == 'daily') {
            $timeafter += (60*60*24) ;
        } else if ($timestr == 'weekly') {
            $timeafter += (60*60*24*7);
        } else if ($timestr == 'monthly') {
            $timeafter = stats_get_next_monthend($timeafter);
        } else {
            return $stats; // this will put us in a never ending loop.
        }
    }

    foreach ($times as $time) {
        if (!array_key_exists($time,$stats)) {
            $newobj = new StdClass;
            $newobj->timeend = $time;
            $newobj->id = 0;
            $newobj->line1 = 0;
            if (!empty($line2)) {
                $newobj->line2 = 0;
            }
            if (!empty($line3)) {
                $newobj->line3 = 0;
            }
            $stats[$time] = $newobj;
        }
    }
    
    krsort($stats);
    return $stats;

}

function stats_check_runtime() {
    global $CFG;
    
    if (empty($CFG->statsmaxruntime)) {
        return true;
    }
    
    if ((time() - $CFG->statsrunning) < $CFG->statsmaxruntime) {
        return true;
    }
    
    return false; // we've gone over! 
        
}


?>