<?php
echo 'Προσφορές<br/><br/>';

echo $this->Paginator->numbers(array('first' => 2, 'last' => 2));

echo '<div class="span9">';
//seperate in blocks of $sep
$sep = 3;
$count = count( $offers )/$sep;



foreach( $offers as $key => $offer ) {

    if( $key%($sep) == 0  && $key > 0) {
        echo '</div>';
        echo '<div class="row-fluid">';
    } else if( $key%($sep ) == 0 )  {
        echo '<div class="row-fluid">';

    }
    //offer start
    echo '<div class="span4">';
    echo "<h2>{$offers[$key]['Offer']['title']}</h2>";
    echo "<p>{$offers[$key]['Offer']['description']}</p>";
    echo "<p><a class=\"btn\" href=\"#\">View details &raquo;</a></p>";
    echo '</div>';
    //offer ends
}

echo '</div>';
echo '<br>';
