(function($) {
  $(document).ready(function() {
    /*
    Script for gifts: flag automatically the gift checkbox
    */
    var input = $('input[name=mpgft-signup-gift-checkbox]');
    if(input.length) {
      input.prop('checked', 'true');
    }

    /*
    Script for gifts: Remove some fields for Premium membership buyers
    */
    var url = window.location.href;
		if(url.toLowerCase().indexOf('premium-regalo')>=0){
			if(url.toLowerCase().indexOf('gift-')==-1){
				if( !$('#mepr_address1').val() ) {
          $('#mepr_address1').val("---");
        }
        if( !$('#mepr_cap1').val() ) {
          $('#mepr_cap1').val("---");
        }
        if( !$('#mepr_city1').val() ) {
          $('#mepr_city1').val("---");
        }
        if( !$('#mepr_provincia1').val() ) {
          $('#mepr_provincia1').val("ag");
        }
				$('.mepr_mepr_address').hide();
				$('.mepr_mepr_cap').hide();
				$('.mepr_mepr_city').hide();
				$('.mepr_mepr_provincia').hide();
				$('.mepr_mepr_phone').hide();
			}
		}
    /*
    Script for gifts: Remove membership description for gift destinataires
    */
    if((url.toLowerCase().indexOf('premium-regalo')>=0) || (url.toLowerCase().indexOf('6-mesi-regalo')>=0) || (url.toLowerCase().indexOf('12-mesi-regalo')>=0)){
      if(url.toLowerCase().indexOf('gift-')>=0){
        $('.li-gift-text').addClass('mepr-hidden');
      }
    }
  });
})(jQuery);

/* Script for gifts, fix some pages
window.addEventListener("load", function() {
    var body = document.getElementsByTagName("body")[0];
    var href = window.location.href;
    var coupon = "WOMENSDAY2022";
    if (body.innerHTML.indexOf("Regala un Abbonamento") !== -1) {
        var checkbox = document.getElementById("mpgft-signup-gift-checkbox1");
        checkbox.click();
        //checkbox.parentNode.parentNode.className += " mepr-hidden";
        //document.getElementsByName("mepr_coupon_code")[0].value = coupon;
        //document.getElementsByClassName("have-coupon-link")[0].className += " mepr-hidden";
        //document.getElementsByClassName("mepr_price")[0].className += " mepr-hidden";
    }
    if (
        href.indexOf("/abbonamento/3-mesi") > -1 ||
        href.indexOf("/abbonamento/6-mesi") > -1 ||
        href.indexOf("/abbonamento/12-mesi") > -1) {
        document.getElementsByName("mepr_coupon_code")[0].value = coupon;
    }
});*/

/*Script for gifts, deactivate mouse on box to send gift
window.addEventListener("load", function(){
	var links = document.getElementsByClassName("mpgft-open-send-gift");
	var body = document.getElementsByTagName("body")[0];
	var forms = document.getElementsByClassName("mpgft-white-popup");
	var closes = document.getElementsByClassName("mfp-close");
	for(i=0;i<links.length;i++){
		links[i].addEventListener("click",function(event){
			body.style.pointerEvents = "none";
			for(j=0;j<forms.length;j++){
				forms[j].style.pointerEvents = "all";
			}
			for(j=0;j<closes.length;j++){
				closes[j].addEventListener("click",function(event){
					body.style.pointerEvents = "all";
				});
			}
		});
	}
});*/
