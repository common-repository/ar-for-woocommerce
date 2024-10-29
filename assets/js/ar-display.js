function copyToClipboard(ID) {
    var copyText = document.getElementById(ID);
    copyText.select();
    copyText.setSelectionRange(0, 99999)
    document.execCommand("copy");
}


jQuery(document).ready(function(){


    jQuery(document).on('click','button.ar-button', function(event){
        event.preventDefault();
        
        var alt_id = jQuery(this).data('alt');
        var arViewer = jQuery('#model_' + alt_id);
        console.log('#model_' + alt_id);
        if (arViewer[0].canActivateAR) {

            arViewer[0].activateAR();
          
        }
    });

}); 