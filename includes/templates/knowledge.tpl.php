<?php
$isLoaded = isset($_POST['knowledgeID'])? $knowledgeEntry->load($_POST['knowledgeID']) : $knowledgeEntry->isLoaded();

$title = isset($_POST['knowledgeTitle']) ?
    $_POST['knowledgeTitle'] :
    $isLoaded ? $knowledgeEntry->getProperty('knowledgeTitle') : '';

$content = isset($_POST['knowledgeBody']) ?
    $_POST['knowledgeBody'] :
    $isLoaded ? $knowledgeEntry->getProperty('knowledgeBody') : '';


$tags = isset($_POST['tags']) ?
    $_POST['tags'] :
    $isLoaded ? $knowledgeEntry->getTagCSV() : '';

$userID = $isLoaded ? $knowledgeEntry->getProperty('knowledgeAuthor') : $user->get_property('userID');
?>
<div class="box twentyfour columns">
    <h1>
        <?php echo $isLoaded ? 'Edit Entry' : 'Create Entry'; ?>
    </h1>

    <div class="content">
        <?php
        if ((array_key_exists('saveKnowledge', $_POST) || array_key_exists('saveEditKnowledge', $_POST)) && count($formErrors) > 0) {
            echo sprintf('<p class="error">%s</p><ul>', 'There were error(s) in the form.  Please correct the following and try again:');
            foreach ($formErrors as $field => $issues) {
                echo sprintf('<li><strong>%s</strong>: %s</li>', $field, implode(', ', $issues));
            }
            echo sprintf('</ul>');
        }
        ?>
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" class="full">
            <input type="text" placeholder="Title" class="text full required" name="knowledgeTitle" id="knowledgeTitle"
                   value="<?php echo $title; ?>">
            <i class="icon-edit" title="Required"></i>

            <input type="text" placeholder="Tags, Comma Separated" class="text full required" name="tags" id="tags"
                   value="<?php echo $tags; ?>">
            <i class="icon-edit" title="Required"></i>

            <textarea id="knowledgeBody" name="knowledgeBody"
                      class="full required"><?php echo $content; ?></textarea>
            <i class="icon-edit" title="Required"></i>
            <?php echo sprintf('<input type="hidden" name="userID" id="userID" value="%s" />', $userID); ?>
            <?php if ($isLoaded): ?>
                <input type="hidden" name="knowledgeID" id="knowledgeID" value="<?php echo $knowledgeEntry->getProperty('knowledgeID')?>"/>
                <button class="button" type="sumbit" name="saveEditKnowledge">Save</button>
                <a class="button"
                   href="<?php echo sprintf("http://%s/kb/%s", $_SERVER['SERVER_NAME'], $knowledgeEntry->getProperty('knowledgeID')); ?>">Cancel</a>
            <?php else: ?>
                <button class="button" type="sumbit" name="saveKnowledge">Save</button>
                <a class="button"
                   href="<?php echo sprintf("http://%s/", $_SERVER['SERVER_NAME']); ?>">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>