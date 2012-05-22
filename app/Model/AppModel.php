<?php
App::uses('Model', 'Model');
class AppModel extends Model {


    // Provisional solution to pagination count error
    // Can be safely removed for CakePHP version 2.2+
    // Ignored in versions 2.2+
    protected function _findCount($state, $query, $results = array()) {
        $version = Configure::version();
        if ((substr($version, 0, 1) >= 2) && (substr($version, 2, 1) < 2)) {
            if ($state === 'before') {
                if (isset($query['type']) && isset($this->findMethods[$query['type']])) {
                    $query = $this->{'_find' . ucfirst($query['type'])}('before', $query);
                    if (!empty($query['fields']) && is_array($query['fields'])) {
                        if (!preg_match('/^count/i', current($query['fields']))) {
                            unset($query['fields']);
                        }
                    }
                }
            }
        }
        return parent::_findCount($state, $query, $results);
    }
}
