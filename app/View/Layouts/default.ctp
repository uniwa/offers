<!DOCTYPE html>
<html>
<head>
<title><?php echo $title_for_layout?></title>
<?php 
    echo $this->Html->charset();
    echo $this->Html->css( 'bootstrap' );
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
<?php echo $this->Html->script('jquery'); ?>
<?php echo $this->Html->script('dropdown'); ?>
<?php echo $this->Html->script('global'); ?>
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
          <a class="brand" href="#">Coupons</a>
          <div class="nav-collapse">
            <ul class="nav">
            <li class="active"><?php echo $this->Html->link('Εγγραφή', array( 'controller'=>'users', 'action'=>'register'));?></li>
			  <li><a href="#">Όροι χρήσης</a></li>
              <li><a href="#about">Συχνές Ερωτήσεις</a></li>
            </ul>
            <!-- Login functionality with dropdown -->
			<?php 

                if( $this->Session->check( 'Auth.User' ) ) {

                    $username = $this->Session->read( 'Auth.User.username' );
                    $logout = $this->Html->link( 'Αποσύνδεση ', array( 'controller' => 'users', 'action' => 'logout') );
                    echo "<p class=\"navbar-text pull-right\">$logout( $username )</p>";
                } else {?>
                    
                    <ul class="nav pull-right">
                    <li class="dropdown" id="login">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#login">
                        Σύνδεση
                        <b class="caret"></b>
                    </a>

                    <ul class="dropdown-menu">

                        <?php echo $this->Session->flash('auth'); ?>
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
                   
        <?php	}?>

          </div><!--/.nav-collapse -->
		 </div>
      </div>
    </div>

	<div class="container-fluid">

      	<div class="row-fluid">
			<?php echo $content_for_layout;?>						
		</div><!--/row-->
	
   		
	</div><!--/.fluid-container-->
       

        <hr>

    	<footer>
    	   	<p>&copy; Τ.Ε.Ι Αθήνας  2012</p>
        </footer>
<!--will allow all scripts generated in layout elements to be output in one place-->
<?php echo $this->Js->writeBuffer(); ?>
</body>
</html>
