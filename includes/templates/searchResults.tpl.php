<?php

//Ensure there is a reasony to search, else exit
if (
    (!isset($_GET['search']) && !isset($_GET['tags'])) ||
    (empty($_GET['search']) && empty($_GET['tags']))
) {
    return;
}

$tags = (isset($_GET['tags']) && !empty($_GET['tags'])) ? strtoupper(urldecode(trim($_GET['tags']))) : '';
$search = (isset($_GET['search']) && !empty($_GET['search'])) ? urldecode(trim($_GET['search'])) : '';
//Tag Restricted Results
$entries = array();
if (!empty($tags) && !empty($search)) {

} else if (!empty($tags)) {
    $entries = KnowledgeManager::SearchTags($tags);
} else if (!empty($search)) {
    $entries = KnowledgeManager::SearchExtents($search);
} else {
    return;
}

$returnResults = array();
$i = 1;
?>

<?php if (count($entries) == 0): ?>
    <div class="box twentyfour columns">

        <h1>NO RESULTS</h1>

        <div class="content">
            <p>No results could be found for the terms <em>'<?php echo $search; ?>'</em>
                <?php echo (isset($_GET['tags']) && !empty($_GET['tags'])) ? sprintf("with tag filters <em>'%s'</em>", urldecode($_GET['tags'])) : ''; ?>
                .</p>
        </div>
    </div>
<?php elseif (count($entries) == 1): ?>
    <?php
    $knowledgeEntry = $entries[0];
    include_once('article.tpl.php');
    ?>
<?php else: ?>
    <div class="box twentyfour columns">

        <h1>RESULTS</h1>

        <div class="content">
            <?php foreach ($entries as $article): ?>
                <h2><?php echo $i;
                    $i++; ?>. <?php echo $article->getLink(); ?></h2>
                <p><?php echo $article->getSummary(300); ?></p>
                <div class="toolbox clean">
                <span class="details right"><i class="icon-tag"></i>
                    <?php echo $article->getTagLinks();;?>
                </span>
                </div>
            <?php endforeach ?>
        </div>
    </div>

<?php endif; ?>
