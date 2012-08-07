<?php
    echo $this->Html->script('gsis_lookup');
?>
<div class="register form">
<?php
    echo $this->Session->flash();
    echo $this->Form->create(false, array(
                                        'action' => 'register',
                                        'type' => 'POST',
                                        'enctype' => 'multipart/form-data',
                                    )
                              );
?>
    <fieldset>
        <legend><?php echo __('Εγγραφή επιχείρησης'); ?></legend>
<?php
    echo $this->Tb->input(array(
        'field' => 'User.username',
        'input' => $this->Form->text(
                        'User.username', array('class' => 'span4')
                    ),
        'label' => 'Όνομα χρήστη'
    ));

    echo $this->Tb->input(array(
        'field' => 'User.password',
        'input' => $this->Form->password(
                        'User.password', array('class' => 'span4')
                    ),
        'label' => 'Κωδικός πρόσβασης'
    ));

    echo $this->Tb->input(array(
        'field' => 'User.repeat_password',
        'input' => $this->Form->password(
                        'User.repeat_password', array('class' => 'span4')
                    ),
        'label' => 'Επάναληψη κωδικού πρόσβασης'
    ));

    echo $this->Tb->input(array(
        'field' => 'User.email',
        'input' => $this->Form->text(
                        'User.email', array('class' => 'span4')
                    )
    ));

    echo $this->Tb->input(array(
        'field' => 'Company.afm',
        'input' => $this->Form->text(
                        'Company.afm', array('class' => 'span4')
                    ),
        'label' => 'Α.Φ.Μ. επιχείρησης'
    ));

    echo "<div id='lookup' class='btn btn-small btn-info'>Αναζήτηση με ΑΦΜ</div>";
    echo "<div id='ajax-status' style='display:inline;margin-left:12px;'></div>";

    // we need the application URL for the ajax call
    $app_url = trim(APP_URL, '/');

    echo "<br /><script type='text/javascript'>";
    echo "$(document).ready(function() {";
    echo "$('#lookup').live('click', function() {";
    echo "var afm = $('#CompanyAfm').val();";
    echo "var url='{$app_url}'+'/companies/gsis_get/' + afm;";
    echo "gsis_lookup(url);});";
    echo "});</script><br />";

    echo $this->Tb->input(array(
        'field' => 'Company.name',
        'input' => $this->Form->text(
                        'Company.name', array('class' => 'span4')
                    ),
        'label' => 'Όνομα επιχείρησης'
    ));

    echo $this->Tb->input(array(
        'field' => 'Company.phone',
        'input' => $this->Form->text(
                        'Company.phone', array('class' => 'span4')
                    ),
        'label' => 'Τηλέφωνο επιχείρησης'
    ));

    echo $this->Tb->input(array(
        'field' => 'User.terms_accepted',
        'input' => $this->Form->checkbox(
                        'User.terms_accepted', array('value' => '0')
                    ),
        'label' => 'Όροι χρήσης'
    ));

    echo $this->Form->hidden('CompanyFax', array('name' => 'data[Company][fax]'));
    echo $this->Form->hidden('CompanyAddress', array('name' => 'data[Company][address]'));
    echo $this->Form->hidden('CompanyPostalcode', array('name' => 'data[Company][postalcode]'));
    echo $this->Form->hidden('CompanyServiceType', array('name' => 'data[Company][service_type]'));
?>

<div class="control-group text">
    <div class="controls">
        <textarea readonly="readonly" rows="4" name="" id="" cols="80" class="uneditable-input" style="width: 31.9149%; height: 100px;">
OROI XRHSHS

Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque neque nunc, vehicula sit amet tempor sit amet, feugiat a velit. Aliquam ultrices facilisis leo. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed id ante diam, in viverra lectus. Cras augue quam, suscipit quis blandit at, convallis at enim. Ut nec malesuada erat. Suspendisse leo magna, gravida ac sodales sit amet, tempor ut magna. In et enim ac eros lacinia lacinia quis sed justo. Pellentesque eleifend scelerisque lectus, ut porttitor nibh hendrerit id. Maecenas eget hendrerit arcu. Aenean tempor risus eget leo tristique gravida.
</textarea>
    </div>
</div>

    </fieldset>
<?php
    echo $this->Tb->button(
            __('Υποβολή'),
            array(
                'style' => 'success',
                'size' => 'large'
            )
        );
?>
</div>
