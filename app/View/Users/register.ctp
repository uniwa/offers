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
        'label' => 'Αποδοχή όρων χρήσης'
    ));

    echo $this->Form->hidden('CompanyFax', array('name' => 'data[Company][fax]'));
    echo $this->Form->hidden('CompanyAddress', array('name' => 'data[Company][address]'));
    echo $this->Form->hidden('CompanyPostalcode', array('name' => 'data[Company][postalcode]'));
    echo $this->Form->hidden('CompanyServiceType', array('name' => 'data[Company][service_type]'));
?>

<div class="control-group text">
    <div class="controls">
        <textarea readonly="readonly" rows="4" name="" id="" cols="80" class="uneditable-input" style="width: 31.9149%; height: 100px;">
Όροι χρήσης

ΠΑΡΑΚΑΛΟΥΜΕ ΔΙΑΒΑΣΤΕ ΤΟΥΣ ΠΑΡΑΚΑΤΩ ΟΡΟΥΣ ΧΡΗΣΗΣ ΠΡΟΣΕΚΤΙΚΑ ΚΑΘΩΣ ΠΕΡΙΕΧΟΥΝ ΤΟΥΣ ΟΡΟΥΣ ΚΑΙ ΠΡΟΫΠΟΘΕΣΕΙΣ ΠΟΥ ΕΦΑΡΜΟΖΟΝΤΑΙ ΣΤΗΝ ΑΠΟ ΜΕΡΟΥΣ ΣΑΣ ΧΡΗΣΗ ΤΩΝ ΥΠΗΡΕΣΙΩΝ ΜΑΣ ΚΑΙ ΤΗΝ ΑΚΟΛΟΥΘΟΥΜΕΝΗ ΠΟΛΙΤΙΚΗ ΠΡΟΣΤΑΣΙΑΣ ΠΡΟΣΩΠΙΚΩΝ ΔΕΔΟΜΕΝΩΝ ΚΑΙ ΟΙ ΟΠΟΙΟΙ ΣΥΝΙΣΤΟΥΝ ΝΟΜΙΚΑ ΔΕΣΜΕΥΤΙΚΗ ΣΥΜΦΩΝΙΑ ΜΕΤΑΞΥ ΣΑΣ ΚΑΙ ΤΗΣ ΕΤΑΙΡΙΑΣ ΜΑΣ

Κάθε επισκέπτης/μέλος αποδέχεται και συναινεί στη διατήρηση αρχείου από το site του Προγράμματος « Εξωστρεφείς Δράσεις Παροχής Ψηφιακών Υπηρεσιών » και στην επεξεργασία των προσωπικών του στοιχείων με σκοπό την παροχή των ηλεκτρονικών υπηρεσιών που διαθέτει, την εν γένει ενημέρωσή του. Το Πρόγραμμα « Εξωστρεφείς Δράσεις Παροχής Ψηφιακών Υπηρεσιών » καλύπτει το σύνολο των προσωπικών δεδομένων και στοιχείων καθώς επίσης και τις προϋποθέσεις συγκέντρωσης, επεξεργασίας και διαχείρισης των προσωπικών δεδομένων των επισκεπτών/χρηστών/μελών του site. Κάθε επισκέπτης/μέλος δύναται να ζητήσει τη διαγραφή των προσωπικών του δεδομένων ή/και τη διόρθωσή τους ή/και την ενημέρωσή τους. Περαιτέρω, το site μπορεί να χρησιμοποιεί cookiesγια την αναγνώριση του επισκέπτη/μέλους ορισμένων υπηρεσιών και σελίδων του.
Σε καμία περίπτωση με την παρούσα δεν καλύπτεται η σχέση μεταξύ των επισκεπτών/χρηστών/μελών από το site και οιωνδήποτε υπηρεσιών που δεν υπόκεινται στον έλεγχο του Προγράμματος « Εξωστρεφείς Δράσεις Παροχής Ψηφιακών Υπηρεσιών ». Το Πρόγραμμα συλλέγει προσωπικά δεδομένα στο site:

Α) Όταν ο επισκέπτης χρήστης εγγράφεται στις υπηρεσίες του
Β) Όταν χρησιμοποιεί τις υπηρεσίες του
Γ) Όταν χρησιμοποιεί τις σελίδες του site και εισέρχεται στα προγράμματα

Κάθε χρήστης υποχρεούται να δηλώνει τα αληθινά και πλήρη στοιχεία του καθώς και να ενημερώνει το site για κάθε αλλαγή παρέχοντας τις απαιτούμενες πληροφορίες που απαιτούνται ώστε τα στοιχεία αυτά να είναι πλήρη, αληθινά και ενημερωμένα.
Ο χρήστης αποδέχεται και συναινεί όπως το Πρόγραμμα «Εξωστρεφείς Δράσεις Παροχής Ψηφιακών Υπηρεσιών», υπό τους όρους και τους περιορισμούς των διατάξεων του Συντάγματος και του Ν.2472/1997 και προς το σκοπό της πλήρους διασφάλισης και προστασίας των προσωπικών δεδομένων των χρηστών/μελών της ιστοσελίδας του Προγράμματος, θα διατηρεί σε αρχείο και θα επεξεργάζεται τυχόν προσωπικά δεδομένα του, τα οποία θα περιέρχονται σε γνώση αυτού (του Προγράμματος) από την επίσκεψη του χρήστη στο site με σκοπό :

Α) την εκπλήρωση των όρων και υποχρεώσεων των επιμέρους υπηρεσιών, που παρέχει το site στους χρήστες του,
Β) την εν γένει ενημέρωση του χρήστη,
Γ) την ικανοποίηση των εκάστοτε απαιτήσεων του χρήστη αναφορικά με τις υπηρεσίες που διατίθενται από και μέσω του site,
Δ) την ενημέρωση του χρήστη σχετικά με νέες υπηρεσίες,
Ε) την εύρυθμη λειτουργία του site,
Τ) κάθε συναφή με τα ανωτέρω ενέργεια

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
