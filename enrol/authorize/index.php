<?php // $Id$

require_once("../../config.php");
require_once("enrol.php");
require_once("action.php");

define('ORDER_CAPTURE', 'capture');
define('ORDER_DELETE', 'delete');
define('ORDER_REFUND', 'refund');
define('ORDER_VOID', 'void');

if (!($site = get_site())) {
    error("Could not find a site!");
}

require_login();

if (!isadmin()) {
    error("You must be an administrator to use this page.");
}

$csv = optional_param('csv', '', PARAM_ALPHA);
$orderid = optional_param('order', 0, PARAM_INT);

$strs = get_strings(array('user', 'status', 'action', 'delete', 'time',
                   'course', 'confirm', 'yes', 'no', 'none', 'error'));

$authstrs = get_strings(array('paymentmanagement', 'orderid', 'void', 'capture', 'refund',
                      'authorizedpendingcapture','capturedpendingsettle', 'capturedsettled',
                      'settled', 'refunded', 'cancelled', 'expired', 'tested',
                      'transid', 'settlementdate', 'notsettled', 'amount',
                      'howmuch', 'captureyes', 'unenrolstudent'), 'enrol_authorize');

print_header("$site->shortname: $authstr->paymentmanagement", "$site->fullname", "<a href=\"index.php\">$authstr->paymentmanagement</a>", "");

if (!empty($csv)) {
    authorize_csv();
}
elseif (!empty($orderid)) {
    authorize_order_details($orderid);
}
else {
    authorize_orders();
}
print_footer();


function authorize_orders()
{
    global $CFG;
    global $strs, $authstrs;
    require_once($CFG->libdir.'/tablelib.php');

    $perpage = 10;
    $userid = optional_param('user', 0, PARAM_INT);
    $courseid = optional_param('course', 0, PARAM_INT);

    $table = new flexible_table('enrol-authorize');
    $table->set_attribute('width', '100%');
    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('cellpadding', '3');
    $table->set_attribute('id', 'orders');
    $table->set_attribute('class', 'generaltable generalbox');

    $table->define_columns(array('id', 'timecreated', 'userid', 'status', ''));
    $table->define_headers(array($authstrs->orderid, $strs->time, $strs->user, $strs->status, $strs->action));
    $table->define_baseurl($CFG->wwwroot."/enrol/authorize/index.php?course=$courseid&amp;user=$userid");

    $table->sortable(true);
    $table->pageable(true);
    $table->setup();

    $where = "WHERE (status != '" . AN_STATUS_NONE . "') ";
    if ($userid > 0) { $where .= "AND (userid = '" . $userid . "') "; }
    if ($courseid > 0) { $where .= "AND (courseid = '" . $courseid . "') "; }

    $select = "SELECT id, transid, courseid, userid, status, ccname, timecreated, settletime ";
    $from = " FROM {$CFG->prefix}enrol_authorize ";
    if ($sort = $table->get_sql_sort()) {
        $sort = ' ORDER BY ' . $sort;
    }
    else {
        $sort = ' ORDER BY id DESC ';
    }

    $totalcount = count_records_sql('SELECT COUNT(*) ' . $from . $where);
    $table->initialbars($totalcount > $perpage);
    $table->pagesize($perpage, $totalcount);
    if ($table->get_page_start() !== '' && $table->get_page_size() !== '') {
        $limit = ' ' . sql_paging_limit($table->get_page_start(), $table->get_page_size());
    }
    else {
        $limit = '';
    }

    if ($records = get_records_sql($select . $from . $where . $sort . $limit)) {
        foreach ($records as $record) {
            $actionstatus = get_order_status_desc($record);
            $actions = '&nbsp;';
            foreach ($actionstatus->actions as $value) {
                $actions .= "&nbsp;&nbsp;<a href='index.php?$value=yes&amp;order=$record->id'>{$authstrs->$value}</a> ";
            }
            $table->add_data(array(
                "<a href='index.php?order=$record->id'>$record->id</a>",
                userdate($record->timecreated),
                $record->ccname,
                $authstrs->{$actionstatus->status},
                $actions
            ));
        }
    }

    $table->print_html();
}


function authorize_order_details($orderno) {
    global $CFG;
    global $strs, $authstrs;

    $unenrol = optional_param('unenrol', '');
    $cmdconfirm = optional_param('confirm', '', PARAM_ALPHA);

    $cmdcapture = optional_param('capture', '', PARAM_ALPHA);
    $cmddelete = optional_param('delete', '', PARAM_ALPHA);
    $cmdrefund = optional_param('refund', '', PARAM_ALPHA);
    $cmdvoid = optional_param('void', '', PARAM_ALPHA);

    $table->width = '100%';
    $table->size = array('30%', '70%');
    $table->align = array('right', 'left');

    $sql = "SELECT E.*, C.shortname, C.enrolperiod " .
    "FROM {$CFG->prefix}enrol_authorize E " .
    "INNER JOIN {$CFG->prefix}course C ON C.id = E.courseid " .
    "WHERE E.id = '$orderno'";

    $order = get_record_sql($sql);
    if (!$order) {
        notice("Order $orderno not found.", "index.php");
        return;
    }

    echo "<form action='index.php' method='post'>\n";
    echo "<input type='hidden' name='order' value='$order->id'>\n";

    $settled = settled($order);
    $status = get_order_status_desc($order);

    $table->data[] = array("<b>$authstrs->orderid:</b>", $order->id);
    $table->data[] = array("<b>$authstrs->transid:</b>", $order->transid);
    $table->data[] = array("<b>$authstrs->amount:</b>", "$order->currency $order->amount");
    if ((empty($cmdcapture) and empty($cmdrefund) and empty($cmdvoid))) {
        $table->data[] = array("<b>$strs->course:</b>", $order->shortname);
        $table->data[] = array("<b>$strs->status:</b>", $authstrs->{$status->status});
        $table->data[] = array("<b>$strs->user:</b>", $order->ccname);
        $table->data[] = array("<b>$strs->time:</b>", userdate($order->timecreated));
        $table->data[] = array("<b>$authstrs->settlementdate:</b>", $settled ? userdate($order->settletime) : $authstrs->notsettled);
    }
    $table->data[] = array("&nbsp;", "<hr size='1' noshade>\n");

    if (!empty($cmdcapture)) { // CAPTURE
        if (empty($cmdconfirm)) {
            $table->data[] = array("<b>$strs->confirm:</b>",
            "$authstrs->captureyes<br /><a href='index.php?order=$orderno&amp;capture=yes&amp;confirm=yes'>$strs->yes</a>
            &nbsp;&nbsp;&nbsp;&nbsp;<a href='index.php?order=$orderno'>$strs->no</a>");
        }
        else {
            $message = '';
            $extra = NULL;
            $success = authorizenet_action($order, $message, $extra, AN_ACTION_PRIOR_AUTH_CAPTURE);
            update_record("enrol_authorize", $order); // May be expired.
            if (!$success) {
                $table->data[] = array("<b><font color='red'>$strs->error:</font></b>", $message);
            }
            else {
                if (empty($CFG->an_test)) {
                    $timestart = $timeend = 0;
                    if ($order->enrolperiod) {
                        $timestart = time(); // early start
                        $timeend = $order->settletime + $order->enrolperiod; // lately end
                    }
                    if (enrol_student($order->userid, $order->courseid, $timestart, $timeend, 'authorize')) {
                        $user = get_record('user', 'id', $order->userid);
                        $teacher = get_teacher($order->courseid);
                        $a->coursename = $order->shortname;
                        $a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id";
                        email_to_user($user, $teacher,
                                      get_string("enrolmentnew", '', $order->shortname),
                                      get_string('welcometocoursetext', '', $a));
                        redirect("index.php?order=$order->id");
                    }
                    else {
                         $table->data[] = array("<b><font color=red>$strs->error:</font></b>",
                         "Error while trying to enrol ".fullname($user)." in '$order->shortname'");
                    }
                }
                else {
                    $table->data[] = array(get_string('testmode', 'enrol_authorize'), get_string('capturetestwarn', 'enrol_authorize'));
                }
            }
        }
        print_table($table);
    }
    elseif (!empty($cmdrefund)) { // REFUND
        $extra = new stdClass();
        $extra->sum = 0.0;
        $extra->orderid = $order->id;

        $sql = "SELECT SUM(amount) AS refunded FROM {$CFG->prefix}enrol_authorize_refunds " .
               "WHERE (orderid = '" . $order->id . "') AND (status = '" . AN_STATUS_CREDIT . "')";

        if ($refund = get_record_sql($sql)) {
            $extra->sum = doubleval($refund->refunded);
        }
        $upto = format_float($order->amount - $extra->sum, 2);
        if ($upto <= 0) {
            error("Refunded to original amount.");
        }
        else {
            $amount = format_float(optional_param('amount', $upto), 2);
            if (($amount > $upto) || empty($cmdconfirm)) {
                $a->upto = $upto;
                $strcanbecredit = get_string('canbecredit', 'enrol_authorize', $a);
                $table->data[] = array("<b>$authstrs->unenrolstudent</b>",
                    "<input type='checkbox' name='unenrol' value='yes'" . (!empty($unenrol) ? " checked" : "") . ">");
                $table->data[] = array("<b>$authstrs->howmuch</b>",
                    "<input type='hidden' name='confirm' value='yes'>
                     <input type='text' size='5' name='amount' value='$amount'>
                     $strcanbecredit<br /><input type='submit' name='refund' value='$authstrs->refund'>");
            }
            else {
                $extra->amount = $amount;
                $message = '';
                $success = authorizenet_action($order, $message, $extra, AN_ACTION_CREDIT);
                if ($success) {
                    if (empty($CFG->an_test)) {
                        $extra->id = insert_record("enrol_authorize_refunds", $extra);
                        if (!$extra->id) {
                            // to do: email admin
                        }
                        if (!empty($unenrol)) {
                            unenrol_student($order->userid, $order->courseid);
                        }
                        redirect("index.php?order=$order->id");
                    }
                    else {
                        $table->data[] = array(get_string('testmode', 'enrol_authorize'), get_string('credittestwarn', 'enrol_authorize'));
                    }
                }
                else {
                    $table->data[] = array("<b><font color=red>$strs->error:</font></b>", $message);
                }
            }
        }
        print_table($table);
    }
    elseif (!empty($cmdvoid)) { // VOID
        $suborderno = optional_param('suborder', 0, PARAM_INT);
        if (empty($suborderno)) { // cancel original transaction.
            if (empty($cmdconfirm)) {
                $strvoidyes = get_string('voidyes', 'enrol_authorize');
                $table->data[] = array("<b>$strs->confirm:</b>",
                    "$strvoidyes<br /><input type='hidden' name='void' value='yes'>
                     <input type='hidden' name='confirm' value='yes'>
                     <input type='submit' value='$strs->yes'>
                     &nbsp;&nbsp;&nbsp;&nbsp;<a href='index.php?order=$orderno'>$strs->no</a>");
            }
            else {
                $extra = NULL;
                $message = '';
                $success = authorizenet_action($order, $message, $extra, AN_ACTION_VOID);
                update_record("enrol_authorize", $order); // May be expired.
                if ($success) {
                    if (empty($CFG->an_test)) {
                        redirect("index.php?order=$order->id");
                    }
                    else {
                       $table->data[] = array(get_string('testmode', 'enrol_authorize'), get_string('voidtestwarn', 'enrol_authorize'));
                    }
                }
                else {
                    $table->data[] = array("<b><font color='red'>$strs->error:</font></b>", $message);
                }
            }
        }
        else { // cancel refunded transaction
            $suborder = get_record('enrol_authorize_refunds', 'id', $suborderno, 'status', AN_STATUS_CREDIT);
            if (!$suborder) { // not found
                error("Transaction can not be voided because of already been voided.");
            }
            else {
                if (empty($cmdconfirm)) {
                    $a->transid = $suborder->transid;
                    $a->amount = $suborder->amount;
                    $strsubvoidyes = get_string('subvoidyes', 'enrol_authorize', $a);

                    $table->data[] = array("<b>$authstrs->unenrolstudent</b>",
                        "<input type='checkbox' name='unenrol' value='yes'" . (!empty($unenrol) ? " checked" : "") . ">");

                    $table->data[] = array("<b>$strs->confirm:</b>",
                        "$strsubvoidyes<br /><input type='hidden' name='void' value='yes'>
                         <input type='hidden' name='confirm' value='yes'>
                         <input type='hidden' name='suborder' value='$suborderno'>
                         <input type='submit' value='$strs->yes'>
                         &nbsp;&nbsp;&nbsp;&nbsp;<a href='index.php?order=$orderno'>$strs->no</a>");
                }
                else {
                    $message = '';
                    $extra = NULL;
                    $success = authorizenet_action($suborder, $message, $extra, AN_ACTION_VOID);
                    update_record("enrol_authorize", $suborder); // May be expired.
                    if ($success) {
                        if (empty($CFG->an_test)) {
                            if (!empty($unenrol)) {
                                unenrol_student($order->userid, $order->courseid);
                            }
                            redirect("index.php?order=$order->id");
                        }
                        else {
                            $table->data[] = array(get_string('testmode', 'enrol_authorize'), get_string('voidtestwarn', 'enrol_authorize'));
                        }
                    }
                    else {
                        $table->data[] = array("<b><font color='red'>$strs->error:</font></b>", $message);
                    }
                }
            }
        }
        print_table($table);
    }
    elseif (!empty($cmddelete)) { // DELETE
        if (!in_array(ORDER_DELETE, $status->actions)) {
            error("Order $order->id cannot be deleted. Status must be expired.");
        }
        if (empty($cmdconfirm)) {
            $table->data[] = array('<b>Delete?: </b>',
            "<a href='index.php?order=$orderno&amp;delete=yes&amp;confirm=yes'>YES</a>
            &nbsp;&nbsp;&nbsp;&nbsp;<a href='index.php?order=$orderno'>No</a>");
        }
        else {
            delete_records('enrol_authorize', 'id', $orderno);
            redirect("index.php");
        }
        print_table($table);
    }
    else { // SHOW
        $actions = '';
        if (empty($status->actions)) {
            $actions .= $strs->none;
        }
        else {
            foreach ($status->actions as $value) {
                $actions .= "<input type='submit' name='$value' value='{$authstrs->$value}'> ";
            }
        }
        $table->data[] = array("<b>$strs->action</b>", $actions);
        print_table($table);
        if ($settled) { // show refunds.
            echo "<h4>" . get_string('returns', 'enrol_authorize') . "</h4>\n";
            $table2->size = array('15%', '15%', '20%', '35%', '15%');
            $table2->align = array('right', 'right', 'right', 'left', 'right');
            $table2->head = array($authstrs->transid, $authstrs->amount, $strs->status, $authstrs->settlementdate, $strs->action);
            $refunds = get_records('enrol_authorize_refunds', 'orderid', $orderno);
            if ($refunds) {
                foreach ($refunds as $rfnd) {
                    $substatus = get_order_status_desc($rfnd);
                    $subactions = '&nbsp;';
                    if (empty($substatus->actions)) {
                        $subactions .= $strs->none;
                    }
                    else {
                        foreach ($substatus->actions as $value) {
                            $subactions .= "<a href='index.php?$value=yes&amp;order=$orderno&amp;suborder=$rfnd->id'>{$authstrs->$value}</a> ";
                        }
                    }
                    $table2->data[] = array($rfnd->transid, $rfnd->amount, $authstrs->{$substatus->status}, userdate($rfnd->settletime), $subactions);
                }
            }
            else {
                $table2->data[] = array(get_string('noreturns', 'enrol_authorize'));
            }
            print_table($table2);
        }
    }
    echo '</form>';
}

function authorize_csv()
{
    return;
}

function get_order_status_desc($order)
{
    global $CFG;
    static $timediff30;

    $ret = new stdClass();

    if (intval($order->transid) == 0) { // test transaction
        $ret->actions = array(ORDER_DELETE);
        $ret->status = 'tested';
        return $ret;
    }

    switch ($order->status) {
    case AN_STATUS_AUTH:
        if (empty($timediff30)) {
            $timediff30 = getsettletime(time()) - (30 * 3600 * 24);
        }

        if (getsettletime($order->timecreated) < $timediff30) {
            $ret->actions = array(ORDER_DELETE);
            $ret->status = 'expired';
        }
        else {
            $ret->actions = array(ORDER_CAPTURE, ORDER_VOID);
            $ret->status = 'authorizedpendingcapture';
        }
        return $ret;

    case AN_STATUS_AUTHCAPTURE:
        if (settled($order)) {
            $ret->actions = array(ORDER_REFUND);
            $ret->status = 'capturedsettled';
        }
        else {
            $ret->actions = array(ORDER_VOID);
            $ret->status = 'capturedpendingsettle';
        }
        return $ret;

        case AN_STATUS_CREDIT:
        if (settled($order)) {
            $ret->actions = array();
            $ret->status = 'settled';
        }
        else {
            $ret->actions = array(ORDER_VOID);
            $ret->status = 'refunded';
        }
        return $ret;

    case AN_STATUS_VOID:
        $ret->actions = array();
        $ret->status = 'cancelled';
        return $ret;

    case AN_STATUS_EXPIRE:
        $ret->actions = array(ORDER_DELETE);
        $ret->status = 'expired';
        return $ret;

    default:
        return $ret;
    }

}
?>