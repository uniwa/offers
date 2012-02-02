<?php

class WorkingHour extends AppModel{
    public $name = "WorkingHour";
    public $belongsTo = array('Company', 'Day', 'Offer');

}
