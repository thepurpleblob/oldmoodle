<?php
/**
 * help.php - Displays help page.
 *
 * Prints a very simple page and includes
 * page content or a string from elsewhere.
 * Usually this will appear in a popup
 * See {@link helpbutton()} in {@link lib/moodlelib.php}
 *
 * @author Martin Dougiamas
 * @version $Id$
 * @package moodlecore
 */
require_once('config.php');

// Get URL parameters.
$file   = optional_param('file', '', PARAM_PATH);
$text   = optional_param('text', 'No text to display', PARAM_CLEAN);
$module = optional_param('module', 'moodle', PARAM_ALPHAEXT);
$forcelang = optional_param('forcelang', '', PARAM_SAFEDIR);

// Start the output.
print_header();
print_simple_box_start('center', '96%');

// We look for the help to display in lots of different places, and
// only display an error at the end if we can't find the help file
// anywhere. This variable tracks that.
$helpfound = false;

if (!empty($file)) {
    // The help to display is from a help file.

    // Get the list of parent languages.
    if (empty($forcelang)) {
        $langs = array(current_language(), get_string('parentlanguage'), 'en_utf8');  // Fallback
    } else {
        $langs = array($forcelang);
    }

    // Work through the possible languages, starting with the most specific.
    foreach ($langs as $lang) {
        if (empty($lang)) {
            continue;
        }

        // Work out which directory the help files live in.
        if ($lang == 'en_utf8') {
            $helpdir = $CFG->dirroot;
        } else {
            $helpdir = $CFG->dataroot;
        }
        $helpdir .= "/lang/$lang/help";

        // Then which file in there we should be serving.
        if ($module == 'moodle') {
            $filepath = "$helpdir/$file";
        } else {
            $filepath = "$helpdir/$module/$file";

            // If that does not exist, try a fallback into the module code folder.
            if (!file_exists($filepath)) {
                $filepath = "$CFG->dirroot/mod/$module/lang/$lang/help/$module/$file";
            }
        }

        // Now, try to include the help text from this file, if we can.
        if (file_exists_and_readable($filepath)) {
            $helpfound = true;
            @include($filepath);   // The actual helpfile

            // Now, we process some special cases.
            if ($module == 'moodle' and ($file == 'index.html' or $file == 'mods.html')) {
                include_help_for_each_module($file, $langs, $helpdir);
            }

            // The remaining horrible hardcoded special cases should be delegated to modules somehow.
            if ($module == 'moodle' and ($file == 'resource/types.html')) {  // RESOURCES
                include_help_for_each_resource($file, $langs, $helpdir);
            }
            if ($module == 'moodle' and ($file == 'assignment/types.html')) {  // ASSIGNMENTS
                include_help_for_each_assignment_type();
            }

            // Having found some help, we break out of the loop over languages.
            break;
        }
    }
} else {
    // The help to display was given as an argument to this function.
    echo '<p>'.s($text).'</p>';   // This param was already cleaned
    $helpfound = true;
}

print_simple_box_end();

// Display an error if necessary.
if (!$helpfound) {
    notify('Help file "'. $file .'" could not be found!');
}

// End of page.
close_window_button();
echo '<p align="center"><a href="help.php?file=index.html">'. get_string('helpindex') .'</a></p>';

$CFG->docroot = '';   // We don't want a doc link here
print_footer('none');

// Utility function =================================================================

function file_exists_and_readable($filepath) {
    return file_exists($filepath) and is_file($filepath) and is_readable($filepath);
}

// Some functions for handling special cases ========================================

function include_help_for_each_module($file, $langs, $helpdir) {
    global $CFG;

    if (!$modules = get_records('modules', 'visible', 1)) {
        error('No modules found!!');        // Should never happen
    }

    foreach ($modules as $mod) {
        $strmodulename = get_string('modulename', $mod->name);
        $modulebyname[$strmodulename] = $mod;
    }
    ksort($modulebyname);

    foreach ($modulebyname as $mod) {
        foreach ($langs as $lang) {
            if (empty($lang)) {
                continue;
            }

            $filepath = "$helpdir/$mod->name/$file";

            // If that does not exist, try a fallback into the module code folder.
            if (!file_exists($filepath)) {
                $filepath = "$CFG->dirroot/mod/$mod->name/lang/$lang/help/$mod->name/$file";
            }

            if (file_exists_and_readable($filepath)) {
                echo '<hr size="1" />';
                @include($filepath); // The actual helpfile
                break; // Out of loop over languages.
            }
        }
    }
}

function include_help_for_each_resource($file, $langs, $helpdir) {
    global $CFG;

    require_once($CFG->dirroot .'/mod/resource/lib.php');
    $typelist = resource_get_resource_types();
    $typelist['label'] = get_string('resourcetypelabel', 'resource');

    foreach ($typelist as $type => $name) {
        foreach ($langs as $lang) {
            if (empty($lang)) {
                continue;
            }

            $filepath = "$helpdir/resource/type/$type.html";

            if (file_exists_and_readable($filepath)) {
                echo '<hr size="1" />';
                @include($filepath); // The actual helpfile
                break; // Out of loop over languages.
            }
        }
    }
}

function include_help_for_each_assignment_type() {
    global $CFG;

    require_once($CFG->dirroot .'/mod/assignment/lib.php');
    $typelist = assignment_types();

    foreach ($typelist as $type => $name) {
        echo '<p><b>'.$name.'</b></p>';
        echo get_string('help'.$type, 'assignment');
        echo '<hr size="1" />';
    }
}
?>
