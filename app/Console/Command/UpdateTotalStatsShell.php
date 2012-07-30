<?php

// Example, running:
//
// ./Console/cake update_total_stats
//
// will retrieve all stats from StatsToday for yesterday's visits
// and add records in StatsTotal for every offer
// with the sums for total visits and unique visitors
// with yesterday's date.

class UpdateTotalStatsShell extends AppShell {

    public $uses = array('Offer', 'StatsToday', 'StatsTotal');

    public function main() {
        $this->update_total_stats();
    }

    // Retrieve yesterday's stats and add records in total stats
    private function update_total_stats() {
            $yesterday = array(
                'd' => date("d", strtotime("-1 day")),
                'm' => date("m", strtotime("-1 day")),
                'y' => date("Y", strtotime("-1 day")));

            $str_yesterday = "{$yesterday['y']}-{$yesterday['m']}-{$yesterday['d']}";

            $visits = $this->StatsToday->get_all_visits($yesterday);

            foreach ($visits as $oid => $visits) {
                $data = array(
                    'offer_id' => $oid,
                    'company_id' => (int)$visits['cid'],
                    'visit_date' => $str_yesterday,
                    'visits_total' => $visits['total'],
                    'visits_unique' => $visits['unique']
                    );

                $this->StatsTotal->add_daily_stats($data);
            }
    }
}
