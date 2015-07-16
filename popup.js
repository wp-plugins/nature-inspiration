var baseUrl = 'https://api.500px.com/v1/';
var searchEndPoint = 'photos/search';

jQuery('#widget-insp_widget').on('click', function() {
    //get parameters from element attributes
    var attributes = this.getAttribute( "data-options" );

    var parameters = attributes.split(",");

    //will sometimes return the last index, which will always be empty -
    //so people will sometimes see things outside their preferences
    var parameterIndexToUse = Math.floor(Math.random()*(parameters.length-1));

    //determine what to search
    var fullUrl = baseUrl + searchEndPoint + '?consumer_key=' + this.getAttribute( "data-key" );
    fullUrl = fullUrl + '&tag=' + parameters[parameterIndexToUse];
    fullUrl = fullUrl + '&image_size=440'; //21
    //actually do search and process result
    jQuery.get(fullUrl, function(data, status) {
        console.log("return data is: ");
        console.log(data);
        var randomIndex = Math.floor(Math.random()*20);
        lightBoxPopup(data.photos[randomIndex]);
    });

});

//make lightbox style popup
function lightBoxPopup(photoDetails) {
    var desc = photoDetails.name;
    var url = photoDetails.image_url;
    var popup = jQuery('#nature_box');
    popup.find('.description').text(desc);
    popup.find('.image_holder').attr("src", url);
    popup.find('.attribution').text(" © " + photoDetails.user.fullname + " / 500px ");
    popup.show();
}

jQuery('#nature_box .close').on('click', function() {
    jQuery('#nature_box').hide();
});
