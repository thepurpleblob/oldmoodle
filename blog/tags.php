<?php
require_once('../config.php');
require_login();

if (isguest()) {
    error ('Guests can not modify tags!');
}
//form process
$mode = optional_param('mode','',PARAM_ALPHA);

switch ($mode) {
    case 'addofficial':    /// adding official tags
    
        if (!isadmin() || !confirm_sesskey()) {
            die('you can not add official tags');
        }
        
        if (($otag = optional_param('otag', '', PARAM_NOTAGS)) && (!get_record('tags','text',$otag))) {
            $tag->userid = $USER->id;
            $tag->text = $otag;
            $tag->type = 'official';
            $tagid = insert_record('tags', $tag);
            
            /// write newly added tags back into window opener
            echo '<script language="JavaScript" type="text/javascript">
            var o = opener.document.createElement("option");
            o.innerHTML = "<option>'.$tag->text.'</option>";
            o.value = '.$tagid.';
            opener.document.entry[\'otags[]\'].insertBefore(o, null);
            </script>';
        } else {  // tag already exist
            notify(get_string('tagalready'));
        }

    break;
    
    case 'addpersonal':    /// everyone can add personal tags
        if (!confirm_sesskey() || isguest() || !isset($USER->id)) {
            error ('you can not add tags');
        }
        
        if (($ptag = optional_param('ptag', '', PARAM_NOTAGS)) && (!get_record('tags','text',$ptag))) {
            $tag->userid = $USER->id;
            $tag->text = $ptag;
            $tag->type = 'personal';
            $tagid = insert_record('tags', $tag);

            /// write newly added tags back into window opener
            echo '<script language="JavaScript" type="text/javascript">
            var o = opener.document.createElement("option");
            o.innerHTML = "<option>'.$tag->text.'</option>";
            o.value = '.$tagid.';
            opener.document.entry[\'ptags[]\'].insertBefore(o, null);
            </script>';
        } else {  //tag already exist
            notify(get_string('tagalready'));
        }
        
    break;
    
    case 'delete':  /// delete a tag
        if (!confirm_sesskey()) {
            error('you can not delete tags');
        }
        
        if ($tags = optional_param('tags', 0, PARAM_INT)) {
        
            foreach ($tags as $tag) {

                $blogtag = get_record('tags','id',$tag);

                // you can only delete your own tags, or you have to be an admin
                if (!isadmin() and $USER->id != $blogtag->userid) {
                    notify(get_string('norighttodeletetag','blog', $blogtag->text));
                    continue;
                }

                /// Only admin can delete tags that are referenced
                if (!isadmin() && get_records('blog_tag_instance','tagid', $tag)) {
                    notify('tag is used by other users, can not delete!');
                    continue;
                }

                delete_records('tags','id',$tag);
                delete_records('blog_tag_instance', 'tagid', $tag);

                /// remove parent window option via javascript
                echo '<script>
                var i=0;
                while (i < window.opener.document.entry[\'otags[]\'].length) {
                    if (window.opener.document.entry[\'otags[]\'].options[i].value == '.$tag.') {
                        window.opener.document.entry[\'otags[]\'].removeChild(opener.document.entry[\'otags[]\'].options[i]);
                    }
                    i++;
                }

                var i=0;
                while (i < window.opener.document.entry[\'ptags[]\'].length) {
                    if (window.opener.document.entry[\'ptags[]\'].options[i].value == '.$tag.') {
                        window.opener.document.entry[\'ptags[]\'].removeChild(opener.document.entry[\'ptags[]\'].options[i]);
                    }
                    i++;
                }

                </script>';
            }
        }
    break;
    
    default:  // just displaying tags form
    break;
}

//print the table

print_header();

include_once('tags.html');

print_footer();


?>
