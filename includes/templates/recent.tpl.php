<div class="box twentyfour columns">
    <h2>Recently Added</h2>

    <div class="content">
        <?php foreach (KnowledgeManager::GetRecentArticles(5) as $result): ?>
            <?php echo sprintf('<h3><a href="%s" title="%s">%s</a></h3>', $result->getURI() , $result->getTitle(), $result->getTitle()); ?>
            <p><?php echo $result->getSummary(200); ?></p>
            <div class="toolbox clean">
                <span class="details right"><i class="icon-tag"></i>
                    <?php echo $result->getTagLinks();?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>

</div>
