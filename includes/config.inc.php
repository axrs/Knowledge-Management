<?php
include('PDOX.class.php');
include('User.class.php');
include('Knowledge.class.php');
include('Purification.class.php');
echo $_SERVER['DOCUMENT_ROOT'];
exit(0);
define('DB_FILE', $_SERVER['DOCUMENT_ROOT'] . "/database/knowledge.sqlite");
define('INTERNAL_DEBUG', true);
error_reporting(E_ALL);

//DATABASE ESTABLISHMENT AND CONNECTIONS
$database = new PDOX('sqlite:' . DB_FILE) or die(sprintf('Unable to connect to Database: %s', DB_FILE));
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$user = new UserManager($database);
KnowledgeManager::SetDatabase($database);

$formErrors = array();
if ($user->is_loaded() && isset($_POST['logoutUser'])) logoutUser($user);
if (!$user->is_loaded() && isset($_POST['loginUser'])) {
    $formErrors = loginUser($user);
}

/**Logins a user
 * @param UserManager $user User Manager Reference
 */
function loginUser($user)
{

    $errors = array();

    if (!array_key_exists('username', $_POST) || empty($_POST['username'])) $errors['Username'] = array("No username specified.");
    if (!array_key_exists('password', $_POST) || empty($_POST['password'])) $errors['Password'] = array("No password specified.");

    if (count($errors) == 0) {
        if ($user->login($_POST['username'], $_POST['password'])) {
            if ($user->is_active()) {
                $user->add_notification('Successfully logged in.');
            } else {
                $user->add_notification('Account is currently inactive. Please wait for an administrator to activate.');
            }
        } else {
            $user->add_notification('Unable to login user. Please check credentials and try again.', false);

        }
        header("refresh:0;");
        exit(0);
    }
    return $errors;
}

/**Logout the user
 * @param UserManager $user User Manager Reference
 */
function logoutUser($user)
{
    $user->logout();
    $user->add_notification('Successfully logged out.');
}