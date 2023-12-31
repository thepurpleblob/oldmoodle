<?php // $Id$
    // filters.php
    // Edit list of available text filters

    require_once('../config.php');
    require_once($CFG->libdir.'/tablelib.php');

    // defines
    define('FILTER_TABLE','filter_administration_table');

    // check for allowed access
    require_login();

    if (!isadmin()) {
        error('Only administrators can use the filters administration page');
    }
    if (!$site = get_site()) {
        error('Site is not defined in filters administration page');
    }

    // get values from page
    $params = new object();
    $params->action     = optional_param('action', '', PARAM_ACTION);
    $params->filterpath = optional_param('filterpath', '', PARAM_PATH);
    $params->cachetext  = optional_param('cachetext', 0, PARAM_INT);
    $params->filterall  = optional_param('filterall', 0, PARAM_BOOL);
    $params->filteruploadedfiles   = optional_param('filteruploadedfiles', 0, PARAM_INT);
    $params->filtermatchoneperpage = optional_param('filtermatchoneperpage', 0, PARAM_BOOL);
    $params->filtermatchonepertext = optional_param('filtermatchonepertext', 0, PARAM_BOOL);

    // some basic information
    $url = 'filters.php';
    $myurl = "$url?sesskey=" . sesskey();
    $img = "$CFG->pixpath/t";

    // get translated strings for use on page
    $txt = new object();
    $txt->managefilters = get_string('managefilters');
    $txt->administration = get_string('administration');
    $txt->configuration = get_string('configuration');
    $txt->name = get_string('name');
    $txt->hide = get_string('hide');
    $txt->show = get_string('show');
    $txt->hideshow = "$txt->hide/$txt->show";
    $txt->settings = get_string('settings');
    $txt->up = get_string('up');
    $txt->down = get_string('down');
    $txt->updown = "$txt->up/$txt->down";
    $txt->cachetext = get_string('cachetext', 'admin');
    $txt->configcachetext = get_string('configcachetext', 'admin');
    $txt->filteruploadedfiles = get_string('filteruploadedfiles','admin');
    $txt->configfilteruploadedfiles = get_string('configfilteruploadedfiles','admin');
    $txt->filterall = get_string('filterall','admin');
    $txt->filtermatchoneperpage = get_string('filtermatchoneperpage','admin');
    $txt->filtermatchonepertext = get_string('filtermatchonepertext','admin');
    $txt->configfilterall = get_string('configfilterall','admin');
    $txt->configfiltermatchoneperpage = get_string('configfiltermatchoneperpage','admin');
    $txt->configfiltermatchonepertext = get_string('configfiltermatchonepertext','admin');
    $txt->cachecontrols = get_string('cachecontrols');
    $txt->yes = get_string('yes');
    $txt->no = get_string('no');
    $txt->none = get_string('none');
    $txt->allfiles = get_string('allfiles');
    $txt->htmlfilesonly = get_string('htmlfilesonly');

    // get a list of possible filters (and translate name if possible)
    // note filters can be in the dedicated filters area OR in their
    // associated modules
    $installedfilters = array();
    $filtersettings = array();
    $filterlocations = array('mod','filter');
    foreach ($filterlocations as $filterlocation) {
        $plugins = get_list_of_plugins($filterlocation);
        foreach ($plugins as $plugin) {
            $pluginpath = "$CFG->dirroot/$filterlocation/$plugin/filter.php";
            $settingspath = "$CFG->dirroot/$filterlocation/$plugin/filterconfig.html";
            if (is_readable($pluginpath)) {
                $name = trim(get_string("filtername", $plugin));
                if (empty($name) or ($name == '[[filtername]]')) {
                    $name = ucfirst($plugin);
                }
                $installedfilters["$filterlocation/$plugin"] = $name;
                if (is_readable($settingspath)) {
                    $filtersettings[] = "$filterlocation/$plugin";
                }
            }
        }
    }

    // get all the currently selected filters
    if (!empty($CFG->textfilters)) {
        $oldactivefilters = explode(',', $CFG->textfilters);
        $oldactivefilters = array_unique($oldactivefilters);
    } else {
        $oldactivefilters = array();
    }

    // take this opportunity to clean up filters
    $activefilters = array();
    foreach ($oldactivefilters as $oldactivefilter) {
        if (!empty($oldactivefilter) and array_key_exists($oldactivefilter, $installedfilters)) {
            $activefilters[] = $oldactivefilter;
        }
    }

    //======================
    // Process Actions
    //======================

    if ($params->action=="") {
        // store cleaned active filers in db
        set_config('textfilters', implode(',', $activefilters));
    } elseif (($params->action=="hide") and confirm_sesskey()) {
        $key=array_search($params->filterpath, $activefilters);
        // check filterpath is valid
        if ($key===false) {
            // ignore it - doubleclick??
        } else {
            // just delete it
            unset($activefilters[$key]);
            set_config('textfilters', implode(',', $activefilters));
        }
    } elseif (($params->action=="show") and confirm_sesskey()) {
        // check filterpath is valid
        if (!array_key_exists($params->filterpath, $installedfilters)) {
            error("Filter $params->filterpath is not currently installed", $url);
        } elseif (array_search($params->filterpath,$activefilters)) {
            // filterpath is already active - doubleclick??
        } else {
            // add it to installed filters
            $activefilters[] = $params->filterpath;
            $activefilters = array_unique($activefilters);
            set_config('textfilters', implode(',', $activefilters));
        }
    } elseif (($params->action=="down") and confirm_sesskey()) {
        $key=array_search($params->filterpath, $activefilters);
        // check filterpath is valid
        if ($key===false) {
            error("Filter $params->filterpath is not currently active", $url);
        } elseif ($key>=(count($activefilters)-1)) {
            // cannot be moved any further down - doubleclick??
        } else {
            // swap with $key+1
            $fsave = $activefilters[$key];
            $activefilters[$key] = $activefilters[$key+1];
            $activefilters[$key+1] = $fsave;
            set_config('textfilters', implode(',', $activefilters));
        }
    } elseif (($params->action=="up") and confirm_sesskey()) {
        $key=array_search($params->filterpath, $activefilters);
        // check filterpath is valid
        if ($key===false) {
            error("Filter $params->filterpath is not currently active", $url);
        } elseif ($key<1) {
            //cannot be moved any further up - doubleclick??
        } else {
            // swap with $key-1
            $fsave = $activefilters[$key];
            $activefilters[$key] = $activefilters[$key-1];
            $activefilters[$key-1] = $fsave;
            set_config('textfilters', implode(',', $activefilters));
        }
    } elseif (($params->action=="config") and confirm_sesskey()) {
        set_config('cachetext', $params->cachetext);
        set_config('filteruploadedfiles', $params->filteruploadedfiles);
        set_config('filterall', $params->filterall);
        set_config('filtermatchoneperpage', $params->filtermatchoneperpage);
        set_config('filtermatchonepertext', $params->filtermatchonepertext);
    }

    //======================
    // Build Display Objects
    //======================

    // construct the display array with installed filters
    // at the top in the right order
    $displayfilters = array();
    foreach ($activefilters as $activefilter) {
        $name = $installedfilters[$activefilter];
        $displayfilters[$activefilter] = $name;
    }
    foreach ($installedfilters as $key => $filter) {
        if (!array_key_exists($key, $displayfilters)) {
            $displayfilters[$key] = $filter;
        }
    }

    // construct the flexible table ready to display
    $table = new flexible_table(FILTER_TABLE);
    $table->define_columns(array('name', 'hideshow', 'order', 'settings'));
    $table->column_style('hideshow', 'text-align', 'center');
    $table->column_style('order', 'text-align', 'center');
    $table->column_style('settings', 'text-align', 'center');
    $table->define_headers(array($txt->name, $txt->hideshow, $txt->updown, $txt->settings));
    $table->define_baseurl("$CFG->wwwroot/$CFG->admin/filters.php");
    $table->set_attribute('id', 'blocks');
    $table->set_attribute('class', 'flexible generaltable generalbox');
    $table->set_attribute('style', 'margin:auto;');
    $table->set_attribute('cellpadding', '5');
    $table->setup();

    // iterate through filters adding to display table
    $updowncount = 1;
    $activefilterscount = count($activefilters);
    foreach ($displayfilters as $path => $name) {
        $upath = urlencode($path);
        // get hide/show link
        if (in_array($path, $activefilters)) {
            $hideshow = "<a href=\"$myurl&amp;action=hide&amp;filterpath=$upath\">";
            $hideshow .= "<img src=\"{$CFG->pixpath}/i/hide.gif\" height=\"16\" width=\"16\" alt=\"hide\" /></a>";
            $hidden = false;
            $displayname = "<span>$name</span>";
        }
        else {
            $hideshow = "<a href=\"$myurl&amp;action=show&amp;filterpath=$upath\">";
            $hideshow .= "<img src=\"{$CFG->pixpath}/i/show.gif\" height=\"16\" width=\"16\" alt=\"show\" /></a>";
            $hidden = true;
            $displayname = "<span class=\"dimmed_text\">$name</span>";
        }

        // get up/down link (only if not hidden)
        $updown = '';
        if (!$hidden) {
            if ($updowncount>1) {
                $updown .= "<a href=\"$myurl&amp;action=up&amp;filterpath=$upath\">";
                $updown .= "<img src=\"$img/up.gif\" alt=\"up\" /></a>&nbsp;";
            }
            else {
                $updown .= "<img src=\"$CFG->pixpath/spacer.gif\" height=\"16\" width=\"16\" alt=\"\" />&nbsp;";
            }
            if ($updowncount<$activefilterscount) {
                $updown .= "<a href=\"$myurl&amp;action=down&amp;filterpath=$upath\">";
                $updown .= "<img src=\"$img/down.gif\" alt=\"down\" /></a>";
            }
            else {
                $updown .= "<img src=\"$CFG->pixpath/spacer.gif\" height=\"16\" width=\"16\" alt=\"\" />";
            }
            ++$updowncount;
        }

        // settings link (if defined)
        $settings = '';
        if (in_array($path, $filtersettings)) {
            $settings = "<a href=\"filter.php?filter=" . urlencode($path) . "\">";
            $settings .= "{$txt->settings}</a>";
        }

        // write data into the table object
        $table->add_data(array($displayname, $hideshow, $updown, $settings));
    }

    // build options list for cache lifetime
    $seconds = array(604800,86400,43200,10800,7200,3600,2700,1800,900,600,540,480,420,360,300,240,180,120,60,30,0);
    unset($lifetimeoptions);
    foreach ($seconds as $second) {
        if ($second>=86400) {
            $options[$second] = get_string('numdays','',$second/86400);
        }
        elseif ($second>=3600) {
            $options[$second] = get_string('numhours','',$second/3600);
        }
        elseif ($second>=60) {
            $options[$second] = get_string('numminutes','',$second/60);
        }
        elseif ($second>=1) {
            $options[$second] = get_string('numseconds','',$second);
        }
        else {
            $options[$second] = get_string('no');
        }
    }

    //==============================
    // Display logic
    //==============================

    print_header("$site->shortname: $txt->managefilters", "$site->fullname",
        "<a href=\"index.php\">$txt->administration</a> -> <a href=\"configure.php\">$txt->configuration</a> " .
        "-> $txt->managefilters");

    print_heading_with_help($txt->managefilters, 'filters');

    // print the table of all the filters
    $table->print_html();

    // print the table for the cache controls
    print_heading($txt->cachecontrols);
    print_simple_box_start('center');
    ?>

    <form name="options" id="options" method="post" action="<?php echo $url; ?>" >
        <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>" />
        <input type="hidden" name="action" value="config" />
        <table cellpadding="20">
            <tr valign="top">
                <td nowrap="nowrap" align="right"><?php echo $txt->cachetext; ?></td>
                <td><?php choose_from_menu($options, "cachetext", $CFG->cachetext, "", "", ""); ?></td>
                <td><?php echo $txt->configcachetext; ?></td>
            </tr>
            <tr valign="top">
                <td nowrap="nowrap" align="right"><?php echo $txt->filteruploadedfiles; ?></td>
                <td><?php choose_from_menu(array($txt->none,$txt->allfiles,$txt->htmlfilesonly),
                    "filteruploadedfiles", $CFG->filteruploadedfiles,"","",""); ?></td>
                <td><?php echo $txt->configfilteruploadedfiles; ?></td>
            </tr>
            <tr valign="top">
                <td nowrap="nowrap" align="right"><?php echo $txt->filtermatchoneperpage; ?></td>
                <td><?php choose_from_menu(array($txt->no,$txt->yes), "filtermatchoneperpage", $CFG->filtermatchoneperpage,"","",""); ?></td>
                <td><?php echo $txt->configfiltermatchoneperpage; ?></td>
        <tr valign="top">
        <td nowrap="nowrap" align="right"><?php echo $txt->filtermatchonepertext; ?></td>
        <td><?php choose_from_menu(array($txt->no,$txt->yes), "filtermatchonepertext", $CFG->filtermatchonepertext,"","",""); ?></td>
        <td><?php echo $txt->configfiltermatchonepertext; ?></td>
        </tr>
            </tr>
            <tr valign="top">
                <td nowrap="nowrap" align="right"><?php echo $txt->filterall; ?></td>
                <td><?php choose_from_menu(array($txt->no,$txt->yes), "filterall", $CFG->filterall,"","",""); ?></td>
                <td><?php echo $txt->configfilterall; ?></td>
            </tr>
            <tr valign="top">
                <td>&nbsp;</td>
                <td><input type="submit" value="<?php print_string('savechanges'); ?>" /></td>
                <td>&nbsp;</td>
            </tr>
        </table>
    </form>

    <?php
    print_simple_box_end();

    print_footer();

?>
