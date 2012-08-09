<!DOCTYPE html>
<html>
<head>
<title><?php echo $title_for_layout?></title>
<?php
    echo $this->Html->charset();
    echo $this->Html->css( 'bootstrap' );
    echo $this->Html->css( 'global' );

    echo $this->Html->script('jquery');
    echo $this->Html->script('dropdown');
    echo $this->Html->script('global');
    echo $this->Html->script('modal');
    echo $this->Html->script('transition');
    echo $this->Html->script('bootstrap-alert');
    echo $this->Html->script('bootstrap-tab');
?>
</head>
<body>
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <?php
            echo $this->Html->link(
                __('Coupons'),
                array( 'controller'=>'offers', 'action'=>'index'),
                array( 'class'=>'brand'));
          ?>
          <div class="nav-collapse">
            <ul class="nav">

            <li>
                <?php
                    // show register link for guests
                    if (! $this->Session->check('Auth.User.id')) {
                        echo $this->Html->link(
                            __('Εγγραφή επιχείρησης'),
                            array('controller' => 'users', 'action' => 'register'));
                    }
                ?>
            </li>
            <li>
                <?php
                    echo $this->Html->link(
                        __('Όροι χρήσης'),
                        array('controller' => 'users', 'action' => 'terms'));
                ?>
            </li>
            <li>
                <?php
                    echo $this->Html->link(
                        __('Συχνές Ερωτήσεις'),
                        array('controller' => 'users', 'action' => 'faq'));
                ?>
            </li>
            <li>
                <?php
                    if ($this->Session->check('Auth.User.id')) {
                        echo $this->Html->link(
                            __('Αναφορά προβλήματος'),
                            array('controller' => 'users', 'action' => 'help'));
                    }
                ?>
            </li>
            </ul>

            <!--Block dropdown form when user is inside login action-->
            <?php
                // Login functionality with dropdown
                //if user is logged in take his profile link and logout link
                if( $this->Session->check( 'Auth.User' ) ) {
                    $username = $this->Session->read( 'Auth.User.username' );
                    $role = $this->Session->read( 'Auth.User.role' );

                    $category_admin = null;
                    if ($role !== ROLE_ADMIN) {
                        $title = __('Το προφίλ μου');
                        $action = 'view';

                        if ($role == ROLE_STUDENT) {
                            $controller = 'students';
                        } else if ($role == ROLE_COMPANY) {
                            $controller = 'companies';
                        }
                        $profile = $this->Html->link($title, array(
                                                        'controller' => $controller,
                                                        'action' => $action));
                    } else {
                        $category_admin = $this->Html->link(
                                'Κατηγορίες',
                                array('controller' => 'offercategories'));
                        $controller = 'admins';
                        $title = __('Σπουδαστές');
                        $action = 'students';
                        $profile = ' '.$this->Html->link($title, array(
                                                        'controller' => $controller,
                                                        'action' => $action));
                        $title = __('Εταιρείες');
                        $action = 'companies';
                        $profile .= ' '.$this->Html->link($title, array(
                                                        'controller' => $controller,
                                                        'action' => $action));
                    }

                    $logout = $this->Html->link(__('Αποσύνδεση '),
                        array('controller' => 'users', 'action' => 'logout'));

                    echo "<p class='navbar-text pull-right navbar-elements'>$category_admin $profile $logout( $username )</p>";
                } else {
                    if (!isset($hide_dropdown) || !$hide_dropdown) {
            ?>
                   <!--TODO all inside element -->
                    <ul class="nav pull-right">
                        <li class="dropdown" id="login">
                            <?php
                                echo $this->Html->link(
                                    'Σύνδεση<span class="caret"></span>',
                                    array(
                                        'controller' => 'users',
                                        'action' => 'login'),
                                    array(
                                        'class' => 'dropdown-toggle',
                                        'data-toggle' => 'dropdown',
                                        'data-target' => '#',
                                        'escape' => false)
                                    );
                            ?>
                            <ul class="dropdown-menu">
                                <?php echo $this->Form->create('User', array(
                                    'action'=>'post', 'url'=>array(
                                                        'controller'=>'users', 'action'=>'login') ));?>
                                <fieldset>
                                <?php
                                    echo $this->Form->input('username', array( 'label' => 'Όνομα χρήστη', 'type'=>'text'));
                                    echo $this->Form->input('password', array( 'label' => 'Κωδικός χρήστη'));
                                ?>
                                </fieldset>
                                <?php echo $this->Form->end(array( 'label' =>__('Είσοδος'), 'class'=>'btn-primary'));?>
                                <li class="divider"></li>
                           </ul>
                       </li>
                    </ul>
          <?php
                    }
                }
          ?>
          </div><!--/.nav-collapse -->
         </div>
      </div>
    </div>

    <div class="container-fluid">
    <!--renders notification message-->
        <?php echo $this->Tb->flashes(array('closable' => true, 'auth' => true)); ?>
        <div class="row-fluid">
            <?php echo $content_for_layout;?>
            <!--modal snipet-->
        </div><!--/row-->
    </div><!--/.fluid-container-->

    <footer>
    <ul>
        <li>&copy; Τ.Ε.Ι Αθήνας <?php echo date('Y'); ?></li>
        <li>
        <?php echo $this->Html->link(
            '',
            array('controller' => 'offers', 'action' => 'index.rss'),
            array('class' => 'footer-rss', 'title' => 'rss προσφορών')
        );?>
        </li>
        <li>
            <a href="http://umbra.edu.teiath.gr/coupons-docs/api.html">API</a>
        </li>
        <li>
            <a href="http://umbra.edu.teiath.gr/coupons-docs/schema.html#xsd">XSD</a>
        </li>
    </ul>
    <div id='espa'>
        <p><?php echo ESPA_TEXT; ?></p>
        <img src='
        <?php echo APP_URL; ?>/img/footer_logo.png' class='espa-logo' />
    </div>
    </footer>
<!--will allow all scripts generated in layout elements to be output in one place-->
<?php echo $this->Js->writeBuffer(); ?>
</body>
</html>
