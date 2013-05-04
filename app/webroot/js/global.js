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
        var imgLink = baseUrl+imgId;
        var img = "<a href='"+imgLink+"'><img class='offer_image' src='"+imgLink+"' /></a>";
        $('#big_image').html(img);
    });
});
