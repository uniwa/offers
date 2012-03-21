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
<?php echo $this->Html->script('modal'); ?>
<?php echo $this->Html->script('transition'); ?>
</head>
<body>

    <?php  //echo $this->element( 'terms' ); ?>
   <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <?php echo  $this->Html->link(__('Coupons'), array( 'controller'=>'offers', 'action'=>'index'), array( 'class'=>'brand'));?>
          <div class="nav-collapse">
            <ul class="nav">

            <li><?php echo $this->Html->link(__('Εγγραφή'), array( 'controller'=>'users', 'action'=>'register'));?></li>
            <li><?php echo $this->Html->link(__('Όροι χρήσης'), "#termsModal", array(  'data-toggle'=>'modal'));?></li>
            <li><?php echo $this->Html->link(__('Συχνές Ερωτήσεις'), array( 'controller'=>'users', 'action'=>'faq'));?></li>
            </ul>

            <!--Block dropdown form when user is inside login action-->
            <?php if( $this->here != '/coupons/users/login' ) { ?>
            <!-- Login functionality with dropdown -->
			<?php 
                
                //if user is logged in take his profile link and logout link
                if( $this->Session->check( 'Auth.User' ) ) {

                    $username = $this->Session->read( 'Auth.User.username' );
                    $role = $this->Session->read( 'Auth.User.role' );
                    $id = $this->Session->read( 'Auth.User.role_id' );

                    $profile = $this->Html->link( __('Το προφίλ μου'), 
                        ($role=='company')?"/companies/view/".$id:"/students/view/".$id);

                    $logout = $this->Html->link( __('Αποσύνδεση '), array( 'controller' => 'users', 'action' => 'logout') );

                    echo "<p class=\"navbar-text pull-right\">$profile&nbsp&nbsp&nbsp$logout( $username )</p>";
                } else {?>
                   <!--TODO all inside element -->
                    <ul class="nav pull-right">
                    <li class="dropdown" id="login">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#login">
                        Σύνδεση
                        <b class="caret"></b>
                    </a>

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
                   
        <?php	}

            }?>

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
    	   	<p>&copy; Τ.Ε.Ι Αθήνας  2012</p>
        </footer>
<!--will allow all scripts generated in layout elements to be output in one place-->
<?php echo $this->Js->writeBuffer(); ?>
</body>
</html>
