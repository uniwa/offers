<!DOCTYPE html>
<html>
<head>
<title><?php echo $title_for_layout?></title>
<?php
    echo $this->Html->charset();
    echo $this->Html->css( 'bootstrap' );
    echo $this->Html->css( 'global' );
?>
<style type="text/css">
  body {
    padding-top: 60px;
    padding-bottom: 40px;
  }
  .sidebar-nav {
    padding: 9px 0;
  }

  .dropdown-menu {

    padding: 0 6px;
  }
</style>
<?php
    echo $this->Html->script('jquery');
    echo $this->Html->script('dropdown');
    echo $this->Html->script('global');
    echo $this->Html->script('modal');
    echo $this->Html->script('transition');
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
                            __('Εγγραφή'),
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
            </ul>

            <!--Block dropdown form when user is inside login action-->
            <?php
                // Login functionality with dropdown
                //if user is logged in take his profile link and logout link
                if( $this->Session->check( 'Auth.User' ) ) {
                    $username = $this->Session->read( 'Auth.User.username' );
                    $role = $this->Session->read( 'Auth.User.role' );

                    if ($role == ROLE_STUDENT) {
                        $controller = 'students';
                    } else if ($role == ROLE_COMPANY) {
                        $controller = 'companies';
                    } else if ($role == ROLE_ADMIN) {
                        $controller = 'admins';
                    }

                    $profile = $this->Html->link('Το προφίλ μου', array(
                                                    'controller' => $controller,
                                                    'action' => 'view'));

                    $logout = $this->Html->link( __('Αποσύνδεση '), array( 'controller' => 'users', 'action' => 'logout') );

                    echo "<p class=\"navbar-text pull-right\">$profile&nbsp&nbsp&nbsp$logout( $username )</p>";
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
        <?php echo $this->Session->flash(); ?>
        <div class="row-fluid">
            <?php echo $content_for_layout;?>
            <!--modal snipet-->
        </div><!--/row-->


    </div><!--/.fluid-container-->


        <hr id = "footer">

        <footer>
            <p>&copy; Τ.Ε.Ι Αθήνας <?php echo date('Y'); ?></p>
            <p>
            <?php echo $this->Html->link(
                '',
                array('controller' => 'offers', 'action' => 'index.rss'),
                array('class' => 'footer-rss', 'title' => 'rss προσφορών')
            );?>
            </p>
        </footer>
<!--will allow all scripts generated in layout elements to be output in one place-->
<?php echo $this->Js->writeBuffer(); ?>
</body>
</html>
