// Request a position. We accept positions whose age is not
// greater than 10 minutes. If the user agent does not have a
// fresh enough cached position object, it will automatically
// acquire a new one.
navigator.geolocation.getCurrentPosition(successCallback,
                                         errorCallback,
                                         {timeout:10000, maximumAge:600000});

function successCallback(position) {
    // By using the 'maximumAge' option above, the position
    // object is guaranteed to be at most 10 minutes old.

    // redirect with new coordinates
    var url = baseUrl + "/users/coords/lat:"+position['coords']['latitude']+"/lng:"+position['coords']['longitude']+"/";
    window.location.replace(url);
}

function errorCallback(error) {
    // Update a div element with error.message.
}
