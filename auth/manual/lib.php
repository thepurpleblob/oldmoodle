<?php  // $Id$
       // manual method - Just does a simple check against the database

function auth_user_login ($username, $password) {
// Returns false if the username doesn't exist yet
// Returns true if the username and password work

    if ($user = get_record('user', 'username', $username)) {
        return validate_internal_user_password($user, $password);
    }

    return false;
}


?>
