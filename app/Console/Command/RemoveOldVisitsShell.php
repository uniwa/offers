<?php

// Example, running:
//
// ./Console/cake remove_old_visits
//
// will remove all records from StatsToday
// with dates earlier than yesterday

class RemoveOldVisitsShell extends AppShell {

    public $uses = array('StatsToday');

    public function main() {
        $this->StatsToday->delete_old_visits();
    }

}
