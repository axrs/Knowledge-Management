<div class="box twentyfour columns">
    <h1>Knowledge Management</h1>

    <div class="content">
        <div class="error center">
            <i class="cadcoder-icon-CADCoder"></i>

            <h3>User Account Required</h3>

            <p>
                The GlynnTucker Knowledge Management System, <strong>(KMS)</strong>, is a restricted system requiring
                valid user credentials.<br>
                You may register using the forms provided, however be aware that an ADMIN user will be required to
                manually authorise your account.
            </p>
        </div>
    </div>
</div>
<div class="box twelve columns">
    <h1>Register</h1>

    <div class="content">
        <?php
            if (array_key_exists('registerUser',$_POST) && count($formErrors) > 0){
                echo sprintf('<p class="error">%s</p><ul>','There were error(s) in the form.  Please correct the following and try again:');
                foreach ($formErrors as $field=>$issues){
                    echo sprintf('<li><strong>%s</strong>: %s</li>',$field,implode(', ',$issues));
                }
                echo sprintf('</ul>');
            }
        ?>
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <fieldset>
                <label for="username">Username</label>
                <input type="required text" placeholder="Username" class="text" name="username" id="username"
                       value="<?php echo isset($_POST['username']) ? $_POST['username'] : '';?>">
                <i class="icon-edit" title="Required"></i>

                <label for="email">Email</label>
                <input type="required text" placeholder="Email Address" class="text" name="email" id="email"
                       value="<?php echo isset($_POST['email']) ? $_POST['email'] : '';?>">

                <i class="icon-edit" title="Required"></i>
            </fieldset>
            <fieldset>
                <label for="password1">Password</label>
                <input type="password" placeholder="Password..." class="text" name="password1" id="password1">
                <i class="icon-edit" title="Required"></i>

                <label for="password2">Repeat</label>
                <input type="password" placeholder="Repeat Password..." class="text" name="password2" id="password2">
                <i class="icon-edit" title="Required"></i>
            </fieldset>
            <fieldset>
                <label for="first">Name</label>
                <input type="text" placeholder="First Name" class="text" name="first" id="first"
                       value="<?php echo isset($_POST['first']) ? $_POST['first'] : '';?>">

                <i class="icon-edit" title="Required"></i>

                <label for="last">Surname</label>
                <input type="text" placeholder="Last Name" class="text" name="last" id="last"
                       value="<?php echo isset($_POST['last']) ? $_POST['last'] : '';?>">

                <i class="icon-edit" title="Required"></i>
            </fieldset>
                <button class="button right" type="sumbit" name="registerUser">Register</button>
        </form>

    </div>
</div>
<?php if ($user->is_loaded()):?>
<div class="box twelve columns">
    <h1>Welcome <?php echo $user->get_property('userName');?></h1>

    <div class="content">
        <?php
        if (array_key_exists('loginUser',$_POST) && count($formErrors) > 0){
            echo sprintf('<p class="error">%s</p><ul>','There were error(s) in the form.  Please correct the following and try again:');
            foreach ($formErrors as $field=>$issues){
                echo sprintf('<li><strong>%s</strong>: %s</li>',$field,implode(', ',$issues));
            }
            echo sprintf('</ul>');
        }
        ?>
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <p>Your account is currently not activated, please wait for an administrator to activate the account.</p>
            <p>If you believe this to be a mistake, please contact the site administrator at your earliest convienence.</p>
            <a href="/logout" title="Logout" class="button"><i class="icon-off"></i> Logout</a>
        </form>
    </div>
</div>
    <?php else:?>
        <div class="box twelve columns">
            <h1>Login</h1>

            <div class="content">
                <?php
                if (array_key_exists('loginUser',$_POST) && count($formErrors) > 0){
                    echo sprintf('<p class="error">%s</p><ul>','There were error(s) in the form.  Please correct the following and try again:');
                    foreach ($formErrors as $field=>$issues){
                        echo sprintf('<li><strong>%s</strong>: %s</li>',$field,implode(', ',$issues));
                    }
                    echo sprintf('</ul>');
                }
                ?>
                <form method="post" action="/">
                    <fieldset>
                        <label for="username">Username</label>
                        <input type="required text" placeholder="Username" class="text" name="username" id="username"
                               value="<?php echo isset($_POST['username']) ? $_POST['username'] : '';?>">
                        <i class="icon-edit" title="Required"></i>

                        <label for="password">Password</label>
                        <input type="password" placeholder="Password" class="text" name="password" id="password">
                        <i class="icon-edit" title="Required"></i>
                    </fieldset>
                    <button class="button right" type="sumbit" name="loginUser">Login</button>

                </form>
            </div>
        </div>

<?php endif;?>
