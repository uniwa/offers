<!DOCTYPE html>
<html>
<head>
<title><?php echo $title_for_layout?></title>
<?php echo $this->Html->charset();
echo $this->Html->css( 'bootstrap');
?>
<style type="text/css">
  body {
    padding-top: 60px;
    padding-bottom: 40px;
  }
  .sidebar-nav {
    padding: 9px 0;
  }
</style>
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
              <li class="active"><a href="#contact">Εγγραφή</a></li>
			  <li><a href="#">Όροι χρήσης</a></li>
              <li><a href="#about">Συχνές Ερωτήσεις</a></li>
            </ul>

			<?php 

                if( $this->Session->check( 'Auth.User' ) ) {

					echo "<p class=\"navbar-text pull-right\">Συνδεδεμένος ως <a href=\"#\">{$this->Session->read('Auth.User.username')}</a></p>";
				} else {
					echo '<p class="navbar-text pull-right"><a href ="#">Σύνδεση</a></p>';

				}
			?>

          </div><!--/.nav-collapse -->
		 </div>
      </div>
    </div>

	<div class="container-fluid">

      	<div class="row-fluid">
			<?php echo $this->element( 'sidebar' );?>
			<?php echo $content_for_layout;?>						
		</div><!--/row-->
	
   		
	</div><!--/.fluid-container-->
       

        <hr>

    	<footer>
    	   	<p>&copy; Τ.Ε.Ι Αθήνας  2012</p>
  		</footer>

</body>
</html>
