<?php
/**Verifies the registration form
 * @param UserManager $user User Manager Reference
 * @return array Error Array
 */
if (isset($_POST['saveKnowledge']) && !$knowledgeEntry->isLoaded()){
    $formErrors = validateNewEntry();
    if (count($formErrors) == 0){
        $knowledgeDetails = array(
            'knowledgeTitle' => $_POST['knowledgeTitle'],
            'knowledgeBody' => $_POST['knowledgeBody'],
            'knowledgeAuthor' => $_POST['userID'],
            'tags' => $_POST['tags'],
        );
        $article = KnowledgeManager::Create($knowledgeDetails);
        if (!$article->isLoaded())$user->add_notification('There was an error processing the form. Please fix any listed errors and try again.',false);
        else {
            $user->add_notification(sprintf('Successfully registered content.'),true);
            header("refresh:0;");
            header("Location: {$article->getURI()}");
            exit(0);
        }
    }
}

if (isset($_POST['saveEditKnowledge']) && $knowledgeEntry->isLoaded()){
    $formErrors = validateExistingEntry();
    if (count($formErrors) == 0){
        $knowledgeDetails = array(
            'knowledgeTitle' => $_POST['knowledgeTitle'],
            'knowledgeBody' => $_POST['knowledgeBody'],
            'tags' => $_POST['tags'],
        );
        $success = $knowledgeEntry->update($knowledgeDetails);
        if (!$success)$user->add_notification('There was an error processing the form. Please fix any listed errors and try again.',false);
        else {
            $user->add_notification(sprintf('Successfully updated content.'),true);
            header("refresh:0;");
            header("Location: {$knowledgeEntry->getURI()}");
            exit(0);
        }
    }
}


function validateExistingEntry(){
    $errors = array();

    if(!array_key_exists('knowledgeTitle',$_POST) || empty($_POST['knowledgeTitle']))  $errors['Title'] = array("No title specified.");
    if(!array_key_exists('tags',$_POST) || empty($_POST['tags']))  $errors['Tags'] = array("No tags specified.");
    if(!array_key_exists('knowledgeBody',$_POST) || empty($_POST['knowledgeBody']))  $errors['Content'] = array("No content specified.");

    return $errors;
}

function validateNewEntry(){
    $errors = array();

    if(!array_key_exists('userID',$_POST) || empty($_POST['userID']))  $errors['User'] = array("Unknown user.");
    if(!array_key_exists('knowledgeTitle',$_POST) || empty($_POST['knowledgeTitle']))  $errors['Title'] = array("No title specified.");
    if(!array_key_exists('tags',$_POST) || empty($_POST['tags']))  $errors['Tags'] = array("No tags specified.");
    if(!array_key_exists('knowledgeBody',$_POST) || empty($_POST['knowledgeBody']))  $errors['Content'] = array("No content specified.");

    return $errors;
}