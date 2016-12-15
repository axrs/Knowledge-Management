<div class="section">
    <div class="box five columns right">
        <h1>Actions</h1>

        <div class="content">
            <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" class="packed">
                <a href="/logout" title="Logout" class="button"><i class="icon-off"></i></a>
                <a href="/createKnowledge" title="Create Entry" class="button"><i class="icon-edit"></i></a>
                <a href="/" title="Home" class="button"><i class="icon-home"></i></a>
            </form>
        </div>
    </div>

    <div class="box nineteen columns">
        <h1>Search</h1>

        <div class="content">
            <form method="get" action="/search">
                <input type="required text" placeholder="Search..." class="text full" name="search" id="liveSearch"
                       onkeyup="timedSearch()"
                       value="<?php echo isset($_POST['search']) ? $_POST['search'] : ''; ?>">
                <input type="hidden" name="searchKnowledge"/>
                <input type="hidden" name="tags" value="<?php echo isset($_GET['tags']) ? $_GET['tags'] : ''; ?>"/>
            </form>
        </div>
    </div>
</div>
</div>

<div class="container overflow">

    <?php include_once('tagTree.tpl.php'); ?>
</div>
<div class="container">
    <div class="section" id="live">


