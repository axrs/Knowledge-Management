<?php
include_once("./includes/config.inc.php");
$knowledgeEntry = new KnowledgeManager();
$url = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$loadAttempted = false;
$searchAttempt = false;
if (strtolower($url[0]) == "logout") {
    logoutUser($user);
}
$searchAttempt = preg_match("#^search?(.*)$#i", strtolower($url[0]));

if (strtolower($url[0]) == "kb" && isset($url[1]) && is_numeric($url[1])) {
    $knowledgeEntry = new KnowledgeManager($url[1]);
    $loadAttempted = true;
}

$includes = array();
$processorIncludes = array();

if (isset($_POST['registerUser']))
    array_push($processorIncludes, "includes/processors/register.proc.php");
if (isset($_POST['saveKnowledge']) || isset($_POST['saveEditKnowledge']))
    array_push($processorIncludes, "includes/processors/knowledge.proc.php");

if ($user->is_loaded() && $user->is_active()) {
    array_push($includes, "includes/templates/index.tpl.php");

    if ($loadAttempted && !$knowledgeEntry->isLoaded()) {
        array_push($includes, 'includes/templates/unknownResource.tpl.php');
    } else if (strtolower($url[0]) == "createknowledge" || isset($_POST['createKnowledge']) || isset($_POST['saveKnowledge'])|| isset($_POST['editKnowledge']) || isset($_POST['saveEditKnowledge'])) {
        array_push($includes, "includes/templates/knowledge.tpl.php");
    } else if ($searchAttempt){
        array_push($includes, "includes/templates/searchResults.tpl.php");
    } else if ($loadAttempted && $knowledgeEntry->isLoaded()) {
        array_push($includes, 'includes/templates/article.tpl.php');
    } else if (!$knowledgeEntry->isLoaded()) {
        array_push($includes, "includes/templates/recent.tpl.php");
    }
} else {
    array_push($includes, "includes/templates/register.tpl.php");
}

foreach ($processorIncludes as $include) {
    include($include);
}

include_once("./includes/templates/header.tpl.php");
$user->displayNotifications();
foreach ($includes as $include) {
    include($include);
}
include_once("./includes/templates/footer.tpl.php");