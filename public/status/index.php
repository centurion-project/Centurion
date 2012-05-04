<?php

defined('__DIR__') || define('__DIR__', dirname(__FILE__));

if (isset($_GET['removeMe'])) {
    unlink('../index.php');
    rename('../index.php_next', '../index.php');

    include_once '../../library/Centurion/File/System.php';
    Centurion_File_System::rmdir('.');
    header('Location: ../index/installation-complete');
    die();
}

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(__DIR__ . '/../../application'));

$checklist = array();

include_once __DIR__ . '/Check.php';

$check = new Check();
$check->check();

/*
TODO:
TO check :
- version of php, apache, mysql
- extension
- database installed
- droits d'écriture/lecture

*/

include '_head.php';
?>

    <section>
        <div class="left">

            <h2>Modules list</h2>
            <ul class="module-list">
                <li>
                    <h3>Auth</h3>
                    <?php /*
                    <p>Proin tempor congue tellus, nec fringilla felis ornare sed. Proin vel ligula nibh.
                    Nulla in ligula velit. Suspendisse rutrum blandit pretium.</p>
                    <p><a href="#">Docs</a></p>*/ ?>
                </li>
                <li>
                    <h3>Media</h3>
                    <?php /*
                    <p>Proin tempor congue tellus, nec fringilla felis ornare sed. Proin vel ligula nibh.
                    Nulla in ligula velit. Suspendisse rutrum blandit pretium.</p>
                    <p><a href="#">Docs</a></p>*/ ?>
                </li>
                <li>
                    <h3>Translation</h3>
                    <?php /*
                    <p>Proin tempor congue tellus, nec fringilla felis ornare sed. Proin vel ligula nibh.
                    Nulla in ligula velit. Suspendisse rutrum blandit pretium.</p>
                    <p><a href="#">Docs</a></p>*/ ?>
                </li>
                <li>
                    <h3>Cms</h3>
                    <?php /*
                    <p>Proin tempor congue tellus, nec fringilla felis ornare sed. Proin vel ligula nibh.
                    Nulla in ligula velit. Suspendisse rutrum blandit pretium.</p>
                    <p><a href="#">Docs</a></p>*/ ?>
                </li>
                <li>
                    <h3>Users</h3>
                    <?php /*
                    <p>In in erat in leo pharetra fermentum. Praesent eu sem diam. Ut in risus sed magna eleifend
                    rutrum at id massa. Pellentesque metus tortor.</p>
                    <p><a href="#">Docs</a></p>*/ ?>
                </li>
            </ul>
        </div>
        <div class="right">
            <h2>Checklist</h2>
            <ul class="checklist">
                <?php
                    foreach ($check->getCheckList() as $checkItem) :

                    if ($checkItem['code'] == -1) {
                        $spanClass = 'ui-icon ui-icon-red ui-icon-alert';
                        $liClass = 'red';
                    } else if ($checkItem['code'] == 0) {
                        $spanClass = 'ui-icon ui-icon-red ui-icon-notice';
                        $liClass = 'orange';
                    } else {
                        if ($checkItem['canBeBetter'] == 1) {
                            $liClass = 'orange';
                            $spanClass = 'ui-icon ui-icon-red ui-icon-notice';
                        } else {
                            $spanClass = 'ui-icon ui-icon-bluelight ui-icon-check';
                            $liClass = '';
                        }
                    }

                    if ($checkItem['alt'] != '') {
                        $liClass .= ' tipsyauto';
                    }
                ?>
                <li class="<?php echo $liClass ?>" <?php echo ($checkItem['alt'] != '')?' title="' . htmlentities($checkItem['alt']) . '"':''; ?>>
                    <span class="<?php echo $spanClass; ?>"></span>
                    <?php echo $checkItem['text']; ?>
                </li>
                <?php
                    endforeach;
                ?>
            </ul>
        </div>
        <div class="clear"></div>
        <?php
            if (!$check->hasError()): ?>
            <div class="middle">
                <h3>You Centurion installation seems to be good</h3>
                <p>Next step is to remove all this installation page.</p>
                <a class="ui-button ui-button-text-only ui-button-bg-white" href="?removeMe=true">
                    <span class="ui-button-text">Remove installation file.</span>
                </a>
            </div>
        <?php else: ?>
            <div class="middle">
                <h3>You Centurion installation is not good</h3>
                <p>What sould you do ?</p>
                <a class="ui-button ui-button-text-only ui-button-bg-white" href="http://wiki.centurion-project.org/">
                    <span class="ui-button-text">Check the wiki</span>
                </a>
                <a class="ui-button ui-button-text-only ui-button-bg-white" href="http://groups.google.com/group/centurion-project"">
                    <span class="ui-button-text">Use the Centurion Google Group to find help</span>
                </a>
            </div>
        <?php endif; ?>
        
        <div class="bottom">
            <h2>Documentations</h2>
            <ul class="doc-list">
                <li class="doc-list-1">
                    <ul>
                        <li class="doc-list-2">
                            <a href="http://wiki.centurion-project.org/Develop_with_Centurion/The_model_layer">The model layer</a>
                            <p>Centurion_Db expose an API over the Zend Framework database API. it keeps the main philosophy but add features to make Db usage/querying easier.</p>
                        </li>
                        <li class="doc-list-2">
                            <a href="http://wiki.centurion-project.org/Develop_with_Centurion/The_controller_layer">The Controller layer</a>
                            <p>Centurion provides, in addition to normal feature from Zend Framework, some method to do conditional redirect/forwarding</p>
                        </li>
                    </ul>
                </li>
                <li class="doc-list-1">
                    <ul>
                        <li class="doc-list-2">
                            <a href="http://wiki.centurion-project.org/Develop_with_Centurion/Forms">Forms</a>
                            <p>Centurion_Form inherits from Zend_Form, the main feature it override is the rendering process</p>
                        </li>
                        <li class="doc-list-2">
                            <a href="http://wiki.centurion-project.org/Develop_with_Centurion/Main_apis">Auth</a>
                            <p>Centurion provides by default an authentication API that helps you manage users. in fact this is a built-in module that you can find in library/Centurion/Contrib/auth folder.</p>
                        </li>
                    </ul>
                </li>
                <li class="doc-list-1">
                    <ul>
                        <li class="doc-list-2">
                            <a href="http://wiki.centurion-project.org/Develop_with_Centurion/Translation_trait">Translation trait</a>
                            <p>A little "How To" to implement the translation module in your module</p>
                        </li>
                        <li class="doc-list-2">
                            <a href="http://wiki.centurion-project.org/Develop_with_Centurion/The_view_layer">The View layer</a>
                            <p>As in a normal Zend Framework application, the view files are .pthml files they are placed into the a view folder inside the scripts sub-folder.</p>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="clear"></div>
        </div>
<?php
include '_footer.php';
?>