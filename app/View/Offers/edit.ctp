<?php

echo $this->Form->create('Offer', array(
                        'type' => 'POST',
                        'enctype' => 'multipart/form-data'
                        ));

echo $this->Form->input('title', array(
                        'label' => 'Τίτλος',
                        'type' => 'text'
                       ));
echo $this->Form->input('description', array(
                        'label' => 'Περιγραφή',
                        'type' => 'text'
                       ));
echo $this->Form->input('offer_category_id', array(
                        'label' => 'Κατηγορία Προσφοράς'
                       ));
echo $this->Form->input('offer_type_id', array(
                        'label' => 'Είδος Προσοράς'
                       ));
echo $this->Form->input('starting', array(
                        'label' => 'Ημερ/νία & Ώρα έναρξης προσφοράς',
                        'separator' => ' ',
                        'dateFormat' => 'DMY',
                        'minYear' => date('Y'),
                        'maxYear' => date('Y') + 1,
                        'orderYear' => 'asc',
                        'timeFormat' => 24,
                        'interval' => 15
                       ));
echo $this->Form->input('ending', array(
                        'label' => 'Ημερ/νία & Ώρα λήξης προσφοράς',
                        'separator' => ' ',
                        'dateFormat' => 'DMY',
                        'minYear' => date('Y'),
                        'maxYear' => date('Y') + 1,
                        'orderYear' => 'asc',
                        'timeFormat' => 24,
                        'interval' => 15
                       ));

// this field should be available to the user only when
// the offer_category is "happyhour"
echo 'Happy Hour only<br/>';?>


Ωράριο λειτουργίας: <a class ="btn" id="create">Προσθήκη <b class="caret"></b></a>

<?php 
$c = $work_hour_count;

echo '<input type="hidden" name="workcount" class="workcount" value="'.$c.'"/>';?>
<!--geneartes table when table not set-->
<div id="table"></div>
<?php
if( $c != 0  ) {
?>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ΗΜΕΡΑ</th>
            <th>Ώρα έναρξης</th>
            <th>Ώρα λήξης</th>
        </tr>
    </thead>
    <tbody>

<?php
    for ($i = 0; $i < $c; $i++) {
        echo '<tr id="row'.$i.'">';

        echo '<td>'.
             $this->Form->input('WorkHour.'.$i.'.day_id',
                                array('label' => 'Ημέρα')).
             '</td>';

        echo '<td>'.
             $this->Form->input('WorkHour.'.$i.'.starting',
                                array(
                                    'label' => null,
                                    'type'=>'time',
                                    'timeFormat'=>24,
                                    'interval'=>15,
                                    'class'=>'span3'
                                )).
             '</td>';

        echo '<td>'.
             $this->Form->input('WorkHour.'.$i.'.ending',
                                array(
                                    'label' => null,
                                    'type'=>'time',
                                    'timeFormat'=>24,
                                    'interval'=>15,
                                    'class'=>'span3'
                                )).
            '</td>';

            echo '<td><div class="'.$i.'"><label for="remove"></label><a class = "btn" id="remove">Αφαίρεση</a></td>';


        echo '</tr>';
    }

?>
    </tbody>
</table>
<?php }?>









<?php echo 'Happy Hour only end<br/>';

echo $this->Form->input('expiration_date', array(
                        'label' => 'Η προσφορά ισχύει μέχρι',
                        'type' => 'date',
                        'separator' => ' ',
                        'dateFormat' => 'DMY',
                        'minYear' => date('Y'),
                        'maxYear' => date('Y') + 5,
                        'orderYear' => 'asc'
                       ));
echo $this->Form->input('total_quantity', array(
                        'label' => 'Συνολικός αριθμός διαθέσιμων κουπονιών',
                        'empty' => 'Δεν έχει κουπόνια'
                       ));
echo $this->Form->input('tags', array('label' => 'Λέξεις κλειδιά'));
echo $this->Form->input('Image.0', array(
                        'label' => 'Φωτογραφία',
                        'type' => 'file'
                       ));

echo $this->Form->input('Image.1', array(
                        'label' => 'Φωτογραφία',
                        'type' => 'file'
                       ));
echo $this->Form->input('id', array('type' => 'hidden'));
echo $this->Form->end('Ανάρτηση');
