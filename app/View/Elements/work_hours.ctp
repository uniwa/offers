<style>
    .workhour{
        clear: both;
        margin: 6px 0 0 0;
    }
    .day{
        float: left;
        margin: 6px 9px 0 0;
        width: 80px;
        text-align: right;
    }
    .hour select{
        float: left;
        width: 65px;
/*        text-align: center;*/
    }
    .between{
        float: left;
        margin: 3px 6px;
    }
</style>
<?php
    $html = "<div>Ωράρια προσφοράς<br />";

    $name = 'workcount';
    $options = array(
        'type' => 'hidden',
        'class' => 'workcount',
        'value' => $work_hour_count,
    );
    $html .= $this->Form->input($name, $options);

    // manually print potential errors on work hours
    if ($this->Form->isFieldError('WorkHour.error')) {
        $html .= $this->Form->error('WorkHour.error');
    }

    $wh = option_hours(true);
    for ($d = 1; $d <= 7; $d++) {
        $name = 'WorkHour.'.$d;
        $day_string = day($d).' ';
        $html .= "<div class='workhour'>";
        $html .= "<div class='day'>{$day_string}</div>";
        $html .= $this->Form->input($name.".starting", array(
            'type' => 'select',
            'label' => '',
            'div' => array('class' => 'hour'),
//            'default' => '09:00',
            'options' => $wh));
        $html .= "<div class='between'>-</div>";
        $html .= $this->Form->input($name.".ending", array(
            'type' => 'select',
            'label' => '',
            'div' => array('class' => 'hour'),
//            'default' => '17:00',
            'options' => $wh));
        $html .= "</div>";
    }
    $html .= "</div><div class='workhour'>&nbsp;</div>";

    echo $html;

    function option_hours($empty = null) {
        $wh = array();
        if ($empty) {
            $wh[''] = '';
        }
        for ($h = 0; $h < 24; $h++) {
            $h = str_pad($h,2,'0',STR_PAD_LEFT);
            $t = $h.":00";
            $wh[$t] = $t;
            $t = $h.":30";
            $wh[$t] = $t;
        }

        return $wh;
    }
