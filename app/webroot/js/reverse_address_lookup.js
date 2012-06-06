function address_lookup(req_url, req_address) {
    $.ajax(
        {
            type: 'POST',
            url: req_url,
            data: {
                address: req_address
            },
            beforeSend: function() {
                $("#ajax-status").html('<img src="../../img/ajax-loader.gif" alt="searching" />');
            },
            success: function(data) {
                $("#ajax-status").html('<i class="icon-ok"></i>');
                return data;
            },
            error: function() {
                $("#ajax-status").html('<i class="icon-warning-sign"></i>');
                return {"error": "request failed"};
            }
        }
    );
}
