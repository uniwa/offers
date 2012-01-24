<?php

class WorkingHour extends AppModel{
    $name = "WorkingHour";
    $belongsTo = array( 'Company', 'Day', 'Hour');
}
