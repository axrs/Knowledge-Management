<div class="box twentyfour columns">
    <?php if ($knowledgeEntry->isLoaded()): ?>
        <form method="post" action="<?php echo $knowledgeEntry->getURI();?>" class="right" >
            <input type="hidden" name="knowledgeID" value="<?php echo $knowledgeEntry->getProperty('knowledgeID');?>" />
            <button class="button" type="sumbit" name="editKnowledge"><i class="icon-edit"></i></button>
        </form>
    <?php endif; ?>
    <h2>
        <?php echo $knowledgeEntry->getTitle();?>
    </h2>

    <div class="content">
        <?php echo $knowledgeEntry->getBody(); ?>
    </div>
    <div class="toolbox">
        <span class="details"><i class="icon-tag"></i>
            <?php echo $knowledgeEntry->getTagLinks();?>
        </span>
        <span class="details right"><i class="icon-user"></i> <?php echo $knowledgeEntry->getAuthor();?></span>
        <span class="details right"><i class="icon-calendar"></i> <?php echo $knowledgeEntry->getPublishedDate(); ?></span>
    </div>
</div>