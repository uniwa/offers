function reverse_coordinates(req_url, req_address) {
    $.ajax(
        {
            type: 'POST',
            url: req_url,
            data: {
                address: req_address
            },
            beforeSend: function() {
                //alert('sending request on:' + req_url);
            },
            success: function(data) {
                return data;
            },
            error: function() {
                return {"error": "request failed"};
            }
        }
    );
}
