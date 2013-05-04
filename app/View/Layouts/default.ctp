<!DOCTYPE html>
<html>
<head>
<title><?php echo APP_NAME.' - '.$title_for_layout ?></title>
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
    <div class="navbar navbar-static-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <?php
            $offersLogo = $this->Html->image('/img/offers-icon.png',
                array('class' => 'offers-logo', 'alt' => 'offers-logo'));
            $linkTitle = $offersLogo." Offers";
            echo $this->Html->link($linkTitle, '/',
                array('class'=>'brand', 'escape' => false));

            echo $this->Html->image('/img/teiath-icon.png',
                array('alt' => 'ΤΕΙ Αθήνας',
                      'url' => 'http://www.teiath.gr/',
                      'escape' => false,
                      'class' => 'brand'));
          ?>
          <div>
            <ul class="nav">
            <li>
                <?php
                    // show offers link for everybody
                    $title = __('Αναζήτηση');
                    $controller = 'offers';
                    $action = 'index';
                    $link_div = "<div class='nav-link'>{$title}</div>";
                    $link = $this->Html->link($link_div, array(
                        'controller' => $controller, 'action' => $action),
                        array('escape' => false));
                    echo  "$link";
                ?>
            </li>
            <?php
                // show register link for guests
                if (! $this->Session->check('Auth.User.id')) {
                    $title = __('Εγγραφή');
                    $controller = 'users';
                    $action = 'register';
                    $link_div = "<div class='nav-link'>{$title}</div>";
                    $link = $this->Html->link($link_div, array(
                        'controller' => $controller, 'action' => $action),
                        array('escape' => false));
                    echo  "<li>$link</li>";
                }
            ?>
            <li>
                <?php
                        $title = __('Όροι χρήσης');

                        $controller = 'users';
                        $action = 'terms';
                        $link_div = "<div class='nav-link'>{$title}</div>";
                        $link = $this->Html->link($link_div, array(
                            'controller' => $controller, 'action' => $action),
                            array('escape' => false));
                        echo  "$link";
                ?>
            </li>
            <li>
                <?php
                        $title = __('Συχνές ερωτήσεις');
                        $controller = 'users';
                        $action = 'faq';
                        $link_div = "<div class='nav-link'>{$title}</div>";
                        $link = $this->Html->link($link_div, array(
                            'controller' => $controller, 'action' => $action),
                            array('escape' => false));
                        echo  "$link";
                ?>
            </li>
            <?php
                if ($this->Session->check('Auth.User.id')) {
                    if ($this->Session->read('Auth.User.role') !== ROLE_ADMIN) {
                        $title = __('Αναφορά προβλήματος');
                        $controller = 'users';
                        $action = 'help';
                        $link_div = "<div class='nav-link'>{$title}</div>";
                        $link = $this->Html->link($link_div, array(
                            'controller' => $controller, 'action' => $action),
                            array('escape' => false));
                        echo "<li>$link</li>";
                    }
                }
            ?>
            </ul>

            <!--Block dropdown form when user is inside login action-->
            <?php
                // Login functionality with dropdown
                //if user is logged in take his profile link and logout link
                if( $this->Session->check( 'Auth.User' ) ) {
                    $username = $this->Session->read( 'Auth.User.username' );
                    $role = $this->Session->read( 'Auth.User.role' );

                    $html = "<ul class='nav pull-right'>";
                    if ($role !== ROLE_ADMIN) {
                        $title = __('Το προφίλ μου');
                        $action = 'view';

                        if ($role == ROLE_STUDENT) {
                            $controller = 'students';
                        } else if ($role == ROLE_COMPANY) {
                            $controller = 'companies';
                        }
                        $link_div = "<div class='nav-link'>{$title}</div>";
                        $link = $this->Html->link($link_div, array(
                                                        'controller' => $controller,
                                                        'action' => $action),
                                                        array('escape' => false));
                        $html .= "<li>$link</li>";
                    } else {
                        $title = __('Κατηγορίες');
                        $link_div = "<div class='nav-link'>{$title}</div>";
                        $link = $this->Html->link($link_div,
                                array('controller' => 'offercategories'),
                                array('escape' => false));
                        $html .= "<li>$link</li>";

                        $controller = 'admins';
                        $title = __('Σπουδαστές');
                        $action = 'students';
                        $link_div = "<div class='nav-link'>{$title}</div>";
                        $link = $this->Html->link($link_div, array(
                                                        'controller' => $controller,
                                                        'action' => $action),
                                                        array('escape' => false));
                        $html .= "<li>$link</li>";

                        $title = __('Εταιρείες');
                        $action = 'companies';
                        $link_div = "<div class='nav-link'>{$title}</div>";
                        $link = $this->Html->link($link_div, array(
                                                        'controller' => $controller,
                                                        'action' => $action),
                                                        array('escape' => false));
                        $html .= "<li>$link</li>";
                    }

                    $title = __('Αποσύνδεση');
                    $link_div = "<div class='nav-link'>{$title}(&nbsp;{$username}&nbsp;)</div>";
                    $link = $this->Html->link($link_div,
                        array('controller' => 'users', 'action' => 'logout'),
                        array('escape' => false));
                    $html .= "<li>$link</li>";
                    $html .= "</ul>";
                    echo $html;
                } else {
                    if (!isset($hide_dropdown) || !$hide_dropdown) {
            ?>
                   <!--TODO all inside element -->
                    <ul class="nav pull-right">
                        <li class="dropdown nav-link" id="login">
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
            <a href="https://offers.readthedocs.org/en/latest/api.html">API</a>
        </li>
        <li>
            <a href="https://offers.readthedocs.org/en/latest/schema.html">XSD</a>
        </li>
        <?php
            // only show user manual link for students and company
            if (false) { // remove line to show user manual link
            if (isset($role) && (!is_null($role)) && ($role !== ROLE_ADMIN)) {
                if ($role === ROLE_STUDENT)
                    $manual_path = APP_URL.MANUAL_STUDENT;
                if ($role === ROLE_COMPANY)
                    $manual_path = APP_URL.MANUAL_COMPANY;
        ?>
        <li>
            <a href="<?php echo $manual_path ?>">Εγχειρίδιο χρήσης</a>
        </li>
        <?php
            }
            } // remove line to show user manual link
        ?>
        <li>
            <?php
                $ios_link = $this->Html->image('/img/ios.png',
                    array('alt' => 'iOS', 'title' => 'iOS app'));
                echo $this->Html->link($ios_link, IOS_APP_URL,
                    array('escape' => false));
            ?>
        </li>
        <li>
            <?php
                $fb_link = $this->Html->image('/img/facebook.png',
                    array('alt' => 'Facebook', 'title' => 'Facebook'));
                echo $this->Html->link($fb_link, FACEBOOK_PAGE_URL,
                    array('escape' => false));
            ?>
        </li>
        <li>
            <?php
                $twitter_link = $this->Html->image('/img/twitter.png',
                    array('alt' => 'twitter', 'title' => 'twitter'));
                echo $this->Html->link($twitter_link,
                    "https://twitter.com/".TWITTER_SCREEN_NAME,
                    array('escape' => false));
            ?>
        </li>
        <li>
            <?php
                $contact_link = $this->Html->image('/img/email_icon.png',
                    array('alt' => 'επικοινωνία', 'title' => 'επικοινωνία'));
                echo $this->Html->link($contact_link,
                    "mailto:".CONTACT_EMAIL."?Subject=".CONTACT_SUBJECT,
                    array('escape' => false));
            ?>
        </li>
    </ul>
    <div id='espa'>
        <?php
            $euflag = $this->Html->image('/img/euflag.png',
                array('class' => 'espa-logos', 'alt' => 'EU flag'));
            echo $this->Html->link($euflag,
                "http://europa.eu/", array('escape' => false));
            $digigrlogo = $this->Html->image('/img/digigrlogo.png',
                array('class' => 'espa-logos', 'alt' => 'Ψηφιακή Ελλάδα'));
            echo $this->Html->link($digigrlogo,
                "http://www.digitalplan.gov.gr/portal/", array('escape' => false));
            $espalogo = $this->Html->image('/img/espalogo.png',
                array('class' => 'espa-logos', 'alt' => 'Ε.Σ.Π.Α.'));
            echo $this->Html->link($espalogo,
                "http://www.espa.gr/el/Pages/Default.aspx", array('escape' => false));
            echo "<p>".ESPA_TEXT."</p>";
        ?>
    </div>
    <div>
        <?php echo $this->element('piwik'); ?>
    </div>
    </footer>
<!--will allow all scripts generated in layout elements to be output in one place-->
<?php echo $this->Js->writeBuffer(); ?>
</body>
</html>
