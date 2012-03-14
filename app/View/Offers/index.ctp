<?php echo $this->element( 'sidebar' );?>
<div class="span9">
<div class="hero-unit">
<div class="row-fluid">

<?php      

if( empty( $happyOffers ) ) {

    echo $this->element('alert', array(
        'type' => 'info',
        'label' => '<span class="label label-info">Happy Hour</span>',
        'message' =>' Δεν υπάρχουν happy Hour προσφορές'
        ));
} else {

        foreach( $happyOffers as $key => $hr ) { 

            echo $this->element( 'grid', 
                array( 
                    'span'=>4, 
                    'offset'=>1, 
                    'title'=>"<h2>{$happyOffers[$key]['Offer']['title']}",
                    'label'=>"<span class=\"label label-info\">Happy Hour</span></h2>",
                    'description'=>"<p>{$happyOffers[$key]['Offer']['description']}</p>",
                    'btn' =>"<p><a class=\"btn\" href=\"#\">περισσότερα &raquo;</a></p>")
                );
        }
    }

echo '</div>';//row
echo '</div>';//hero unit

?>
<!-- pagination -->
<div class = "pagination">
<ul>
<?php echo $this->Paginator->numbers(array(
    'first' => 2, 
    'last' => 2, 
    'modulus'=>3,
    'separator' => ' ',
    'tag'=>'li', 
    'ellipsis'=> '<a href=" ">...</a>') );?>
</ul>
</div>

<!-- Offers -->
<?php

if( empty( $offers ) ) {

    echo $this->element('alert', array( 
            'type' => 'info',
            'label' => '<span class="label label-info">Offers</span>',
            'message' => ' Δεν υπάρχουν προσφορές' )
        );
} else {
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
        if( $offer['Offer']['offer_category_id'] == 2 ) {

             echo $this->element( 'grid', 
                array( 
                    'span'=>4, 
                    'offset'=>0, 
                    //h2 closes in label
                    'title'=>"<h2>{$offers[$key]['Offer']['title']}",
                    'label'=>"<span class=\"label label-warning\">limited</span></h2>",
                    'description'=>"<p>{$offers[$key]['Offer']['description']}</p>",
                    'btn' =>"<p><a class=\"btn\" href=\"#\">περισσότερα &raquo;</a></p>")
                );

        } else {
             echo $this->element( 'grid', 
                array( 
                    'span'=>4, 
                    'offset'=>0, 
                    //h2 closes in label
                    'title'=>"<h2>{$offers[$key]['Offer']['title']}",
                    'label'=>"<span class=\"label label-success\">unlimited</span></h2>",
                    'description'=>"<p>{$offers[$key]['Offer']['description']}</p>",
                    'btn' =>"<p><a class=\"btn\" href=\"#\">περισσότερα &raquo;</a></p>")
                );
        }

    }
}

echo '</div>';//span 9

