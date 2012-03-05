$(function() { 

    $('.dropdown-toggle').dropdown();
    
    //Stop Dropdown closing when i click on it.
    $('.dropdown-menu').find('form').click(function (e) {
            e.stopPropagation();
    });
});
