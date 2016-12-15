<div class="box collapse twentyfour columns">
    <input id="details_btn" type="checkbox" name="details_btn" class="details_btn">
    <label for="details_btn" class="toggler"><i class="icon-double-angle-down"></i></label>
    <h2>System Structure</h2>

        <div class="tree">
            <?php KnowledgeManager::PrintTagTreeHTML(); ?>
        </div>
</div>