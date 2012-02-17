<?php
echo '<div class="span9">';

//Happy hour
echo '<div class="hero-unit">';
echo '<div class="row-fluid">';

foreach( $happyOffers as $key=>$hr ) { 

    echo '<div class="span4 offset1">';
    echo "<h2>{$happyOffers[$key]['Offer']['title']}  <span class=\"label label-info\">Happy Hour</span></h2>";
    echo "<p>{$happyOffers[$key]['Offer']['description']}</p>";
    echo "<p><a class=\"btn\" href=\"#\">περισσότερα &raquo;</a></p>";
    echo '</div>';

}

echo '</div>';//row
echo '</div>';//hero unit

?>
<!-- pagination -->
<div class = "pagination">
<ul>
<?php echo $this->Paginator->numbers(array('first' => 2, 'last' => 2, 'modulus'=>3,'separator' => ' ','tag'=>'li', 'ellipsis'=>
'<a href=" ">...</a>') );?>
</ul>
</div>

<!-- Offers -->
<?php
//seperate in blocks of 3
$sep = 3;
$count = count( $offers )/$sep;
//offers 
foreach( $offers as $key => $offer ) {

    if( $key%$sep == 0  && $key > 0) {
        echo '</div>';
        echo '<div class="row-fluid">';
    } else if( $key%$sep == 0 )  {
        echo '<div class="row-fluid">';

    }
    //offer start
    echo '<div class="span4">';
    if( $offer['Offer']['offer_category_id'] == 2 ) {

        echo "<h2>{$offers[$key]['Offer']['title']} <span class=\"label label-warning\">limited</span></h2>";
    } else {

        echo "<h2>{$offers[$key]['Offer']['title']} <span class=\"label label-success\">unlimited</span></h2>";
    }
    echo "<p>{$offers[$key]['Offer']['description']}</p>";
    echo "<p><a class=\"btn\" href=\"#\">περισσότερα &raquo;</a></p>";


    echo '</div>';
    //offer ends
}

echo '</div>';//span 9
?>
