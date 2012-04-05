<?php
$message = "<h2>ΟΡΟΙ ΧΡΗΣΗΣ</h2>";
$message .= "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque neque nunc, vehicula sit amet tempor sit amet, feugiat a velit. Aliquam ultrices facilisis leo. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed id ante diam, in viverra lectus. Cras augue quam, suscipit quis blandit at, convallis at enim. Ut nec malesuada erat. Suspendisse leo magna, gravida ac sodales sit amet, tempor ut magna. In et enim ac eros lacinia lacinia quis sed justo. Pellentesque eleifend scelerisque lectus, ut porttitor nibh hendrerit id. Maecenas eget hendrerit arcu. Aenean tempor risus eget leo tristique gravida.</p>";
$message .= "<p>Pellentesque sed faucibus augue. Vestibulum quis libero vel ligula cursus iaculis. Phasellus posuere, magna in tincidunt rhoncus, enim neque volutpat felis, eu mattis nisl augue at quam. Aliquam erat volutpat. Nulla rutrum tincidunt ligula, non volutpat neque feugiat vitae. Fusce non turpis felis. Sed velit lacus, accumsan non varius ut, fermentum id justo. Proin varius, libero vel dapibus tristique, nulla ipsum dapibus mi, sed lobortis quam massa at ligula. Proin porta nunc id tortor auctor euismod. Pellentesque bibendum interdum est at eleifend.</p>";
$message .= "<p>Suspendisse posuere, neque gravida tempus ullamcorper, tellus arcu consequat lacus, a convallis arcu risus ultrices dui. Phasellus rhoncus vestibulum diam, eget consequat tortor convallis non. Nam sed urna bibendum purus tincidunt convallis. Vivamus vitae quam id elit ultrices lacinia. Nulla mauris nisl, varius eget vestibulum at, malesuada non urna. Aenean commodo rutrum nunc a vestibulum. Maecenas metus neque, egestas auctor euismod et, venenatis id orci. Aliquam gravida gravida sodales. Morbi tincidunt ultricies sodales. Ut tristique aliquam velit, sit amet molestie ligula tristique nec. Cras porta adipiscing faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Ut facilisis auctor tortor, eu ultrices odio varius in. In vitae ipsum a nulla varius iaculis. Etiam vehicula dui non odio adipiscing placerat.</p>";
echo $message;

// Display form to accept terms if terms not accepted
if (!$terms_accepted) {
    echo $this->Form->create();
    echo $this->Form->input('accept', array('label'=>'Αποδέχομαι τους όρους χρήσης', 'type'=>'checkbox'));
    echo $this->Form->end('Συνέχεια');
}
