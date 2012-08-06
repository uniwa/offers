<?php
$html = '';
$page_title = _("Κλείδωμα επιχείρησης");
$html .= "<h2>{$page_title}</h2>";
$html .= "<h3>{$company['Company']['name']}</h3>";
$html .= "<br />";

$html .= $this->Form->create(false);

$label = _('Αιτιολογία');
$options = array('type' => 'text', 'label' => $label);
$html .= $this->Form->input('Company.explanation', $options);
$html .= "<br />";
$confirm = _('Κλείδωμα');
$cancel = _('Ακύρωση');

// don't ban - go back
$submit_options = array(
    'name' => 'cancel',
    'div' => array('class' => 'twobuttons'),
    'class' => 'btn');
$html .= $this->Form->submit($cancel, $submit_options);

// Form->end() does not support 2 parameters
// @see: http://api20.cakephp.org/class/form-helper#method-FormHelperend
$end_options = array(
    'label' => $confirm,
    'class' => 'btn btn-danger');
$html .= $this->Form->end($end_options);
echo $html;
