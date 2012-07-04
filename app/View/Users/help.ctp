<div id='issue_view'>
    <div id='issue_form' class='main_center'>
        <?php
            echo $this->Form->create(false, array('action' => 'help'));
        ?>
        <div class='form-title'>
            <h2>Παρακαλώ συμπληρώστε τα πεδία:</h2>
        </div>
        <ul>
            <li class='form-line'>
                <div class='form-elem form-label'>
                    Θέμα
                </div>
                <div class='form-elem form-input'>
                    <?php
                        echo $this->Form->input('subject', array(
                            'label' => '', 'class' => 'input-elem'));
                    ?>
                </div>
            </li>
            <li class='form-line'>
                <div class='form-elem form-label'>
                    Κατηγορία
                </div>
                <div class='form-elem form-input'>
                    <?php
                        echo $this->Form->input('category', array(
                            'label' => '', 'type' => 'select',
                            'options' => $issues_categories, 'class' => 'input-elem'));
                    ?>
                </div>
            </li>
            <li class='form-line'>
                <div class='form-elem form-label'>
                    Περιγραφή
                </div>
                <div class='form-elem form-input'>
                    <?php
                        echo $this->Form->input('description', array(
                            'label' => '', 'type' => 'textarea',
                            'rows' => '4', 'class' => 'input-elem'));
                    ?>
                </div>
            </li>
            <li class='form-line form-buttons'>
                <div class='form-elem form-submit'>
                    <?php
                        echo $this->Form->submit('Αποστολή', array(
                            'class' => 'button'));
                        echo $this->Form->end();
                    ?>
                </div>
            </li>
        </ul>
    </div>
</div>
