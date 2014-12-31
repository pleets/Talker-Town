/* Namespace app */
if (!(Srvdata instanceof Object))
    var Srvdata = {};

$(function () {
    
    // jQuery UI and Bootstrap functionality
    if (!Srvdata.noConflict) {
        var btn = $.fn.button.noConflict() // reverts $.fn.button to jqueryui btn
        $.fn.btn = btn // assigns bootstrap button functionality to $.fn.btn
        Srvdata.noConflict = true;
    }

});