<?php

class Municipality extends AppModel {

    public $name = 'Municipality';
    public $belongsTo = array('County');

    // Returns an array with County.name as its keys. An array of municipalities
    // hangs from each such key. Each entry in the municipalities array is a
    // pair of [Municipality.id] => Municipality.name, ie:
    // [County#1] => array(
    //      [Mun.id#1] => Mun.name#1,
    //      [Mun.id#2] => Mun.name#2,
    //      [Mun.id#3] => Mun.name#3,
    // ),
    // [County#2] => …
    //
    // This accommodates the creation of hierachical select boxes.
    //
    // By default, the results are sorted by County.name in ascending order.
    // This behaviour may be overriden by passing the appropriate options via
    // the $options parameter. Through this parameter, additional options may be
    // passed in, as well, such as conditions, limit…
    public function getHierarchy($options = null) {
        $opts = array('order' => 'County.name ASC');
        if (! empty($options)) {
            $opts = array_merge($opts, $options);
        }

        $municipalities = $this->find('all', $opts);
        return Set::combine($municipalities,
                            '{n}.Municipality.id',
                            '{n}.Municipality.name',
                            '{n}.County.name');
    }
}
