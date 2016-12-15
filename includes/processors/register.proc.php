<?php

if (isset($_POST['registerUser'])){
    $formErrors = validateUserForm($user);
    if (count($formErrors) == 0){
        $userDetails = array(
            'userName' => strtolower(sqlite_escape_string($_POST['username'])),
            'userFirstName' => sqlite_escape_string($_POST['first']),
            'userSurname' => sqlite_escape_string($_POST['last']),
            'userPassword' => sqlite_escape_string($_POST['password1']),
            'userEmail' => sqlite_escape_string($_POST['email']),
            'userIsActive' => 0,
            'userIsAdmin' => 0,
        );
        $_POST['username'] = '';
        $_POST['first'] = '';
        $_POST['last'] = '';
        $_POST['email'] = '';

        $error = !$user->register($userDetails);
        if ($error)$user->add_notification('There was an error processing the registration form. Please fix any listed errors and try again.',!$error);
        else $user->add_notification(sprintf('Successfully registered new user.'),$error);
    }
}

/**Verifies the registration form
 * @param UserManager $user User Manager Reference
 * @return array Error Array
 */
function validateUserForm($user){
    $errors = array();
    //Description, Type, Quest Location
    if(!array_key_exists('username',$_POST) || empty($_POST['username']))  $errors['Username'] = array("No username specified");
    //Verify unique username
    if (!$user->verifyUniqueUsername($_POST['username'])){
        if (!empty($errors['Username'])) array_push($errors['Username'],array("The username already exists."));
        else $errors['Username'] = array("The username already exists.");
    }

    if(!array_key_exists('email',$_POST) || empty($_POST['email']))  $errors['Email'] = array("No email address specified.");

    //Verify valid email address
    if (array_key_exists('email',$_POST) && !empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
        if (!empty($errors['Email'])) array_push($errors['Email'],array("Invalid email address."));
        else $errors['Email'] = array("Invalid email address.");
    }

    if(!array_key_exists('password1',$_POST) || empty($_POST['password1'])) $errors['Password'] = array("No password specified.");
    if(!array_key_exists('password2',$_POST) || empty($_POST['password2'])) $errors['Repeat Password'] = array("No validation password specified.");

    //Verify Password
    if (!empty($_POST['Password']) && !empty($_POST['password2']) && $_POST['password1'] != $_POST['password2'])
        $errors['Repeat Password'] = array("Passwords do not match.");

    if(!array_key_exists('first',$_POST) || empty($_POST['first']))  $errors['Name'] = array("No first name specified.");
    if(!array_key_exists('last',$_POST) || empty($_POST['last']))  $errors['Surname'] = array("No surname name specified.");

    return $errors;
}