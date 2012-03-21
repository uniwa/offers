Ωράριο λειτουργίας: <a class ="btn" id="create">Προσθήκη <b class="caret"></b></a>

<?php 

$c = $work_hour_count;

echo '<input type="hidden" name="workcount" class="workcount" value="'.$c.'"/>';?>
<!--geneartes table when table not set-->
<div id="table"></div>
<?php
if( $c != 0  ) {
?>

    <table class="<?php echo $table_class?>">
    <thead>
        <tr>
        <?php for( $j = 0; $j < count( $header ); $j++ ) {

            echo '<th>'.$header[$j].'</th>';
        }?>
        </tr>
    </thead>
    <tbody>

<?php
    for ($i = 0; $i < $c; $i++) {
        echo '<tr id="row'.$i.'">';

        echo '<td>'.
             $this->Form->input('WorkHour.'.$i.'.day_id',
                                array('label' => empty( $input_label )?null:$input_label )).
             '</td>';

        echo '<td>'.
             $this->Form->input('WorkHour.'.$i.'.starting',
                                array(
                                    'label' => empty( $startrting_time_label )?null:$starting_time_label ,
                                    'type'=>'time',
                                    'timeFormat'=>$timeFormat,
                                    'interval'=>empty( $interval )?15:$interval,
                                    'class'=>empty( $input_class )?'span3':$input_class
                                )).
             '</td>';

        echo '<td>'.
             $this->Form->input('WorkHour.'.$i.'.ending',
                                array(
                                    'label' => empty( $ending_time_label )?null:$ending_time_label,
                                    'type'=>'time',
                                    'timeFormat'=>$timeFormat,
                                    'interval'=>empty( $interval )?15:$interval,
                                    'class'=>empty( $input_class )?'span3':$input_class
                                )).
            '</td>';

        echo '<td><div class="'.$i.'"><label for="remove"></label><a class = "btn" id="remove">Αφαίρεση</i></a></td>';


        echo '</tr>';
    }

?>
    </tbody>
</table>
<?php }?>

