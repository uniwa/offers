$(function() {

    //bootsrap plugin for login
    $('.dropdown-toggle').dropdown();

    //Stop Dropdown closing when i click on it.
    $('.dropdown-menu').find('form').click(function (e) {
            e.stopPropagation();
    });

    $('.image_frame').live('click', function(e){
        var imgId = $(this).attr('id');
        imgId = imgId.substring(3);
        var img = "<img src='"+baseUrl+imgId+"' />";
        $('#big_image').html(img);
    });
});
