(function($) {
  $(document).ready(function() {

    /* Script for gifts: flag automatically the gift checkbox */
    var input = $('input[name=mpgft-signup-gift-checkbox]');
    if(input.length) {
      input.prop('checked', 'true');
    }
    /* Script for gifts: Remove some fields for Premium membership buyers */
    var url = window.location.href;
	  const lowerUrl = url.toLowerCase();

    const isPremiumRegalo = lowerUrl.includes('premium-regalo') && !lowerUrl.includes('gift-');
    const is6Mesi = lowerUrl.includes('abbonamento/6-mesi');
    const is12Mesi = lowerUrl.includes('abbonamento/12-mesi');
    const isCartaceoRegalo = lowerUrl.includes('cartaceo-regalo') && !lowerUrl.includes('gift-');

    if (isPremiumRegalo || is6Mesi || is12Mesi || isCartaceoRegalo) {
      if( !$('#mepr_book1').length || !$('#mepr_book1').val() ) {
        $('#mepr_book1').val("---");
      }
      if( !$('#mepr_address1').length || !$('#mepr_address1').val() ) {
        $('#mepr_address1').val("---");
      }
      if( !$('#mepr_cap1').length || !$('#mepr_cap1').val() ) {
        $('#mepr_cap1').val("---");
      }
      if( !$('#mepr_city1').length || !$('#mepr_city1').val() ) {
        $('#mepr_city1').val("---");
      }
      if( !$('#mepr_provincia1').length || !$('#mepr_provincia1').val() ) {
        $('#mepr_provincia1').val("ag");
      }
      $('.mepr_mepr_book').hide();
      $('.mepr_mepr_address').hide();
      $('.mepr_mepr_cap').hide();
      $('.mepr_mepr_city').hide();
      $('.mepr_mepr_provincia').hide();
      $('.mepr_mepr_phone').hide();
      $('.mepr_mepr_state').hide();
	  }
    /* Script for gifts: Remove membership description for gift destinataires */
    if((url.toLowerCase().indexOf('premium-regalo')>=0) || (url.toLowerCase().indexOf('6-mesi-regalo')>=0) || (url.toLowerCase().indexOf('12-mesi-regalo')>=0)){
      if(url.toLowerCase().indexOf('gift-')>=0){
        $('.li-gift-text').addClass('mepr-hidden');
      }
    }
    
    /* Substitute some translation strings to use HTML */
    var rowProratio = $('.account #mepr-account-subscriptions-table .mepr-account-terms');
    var rowProratioReadyLaunch = $('.mepr-account-container .mepr-pro-account-terms:contains("(compensazione)")');
	  var rowCancelSubscription = $('.account .mp_wrapper .mepr_updated');
    if(rowProratio.length) {
      var content = rowProratio.html();
      var newContent = content.replace('(compensazione)', '(<a href="/termini-e-condizioni" target="_blank">compensazione</a>)');
      rowProratio.html(newContent);
    }
    if(rowProratioReadyLaunch.length) {
      var content = rowProratioReadyLaunch.html();
      var newContent = content.replace('(compensazione)', '(<a href="/termini-e-condizioni" target="_blank">compensazione</a>)');
      rowProratioReadyLaunch.html(newContent);
    }
    if(rowCancelSubscription.length) {
      var cancelContent = rowCancelSubscription.html();
      var newCancelContent = cancelContent.replace('Il rinnovo automatico del tuo abbonamento è stato cancellato con successo.', 'Il rinnovo automatico del tuo abbonamento è stato cancellato con successo. Facci sapere come possiamo migliorare, <a href="https://us2.list-manage.com/survey?u=fc6a2373726095bfbf68aad96&id=64e9dad630&attribution=false" target="_blank">clicca qui</a>.');
      rowCancelSubscription.html(newCancelContent);
    }
  });
})(jQuery);

/**
 * Requires Advanced Targeting Conditions extension: https://wppopupmaker.com/extensions/advanced-targeting-conditions/
 * 
 * Sets a cookie to display the popup for the duration of the cookie lifetime, after the user has viewed n pages.
 * Reset when the cookie expires.
 * 
 * Popup ID: 1526 (REPLACE throughout snippet with your popup ID)
 *
 * For this snippet to work, make sure:
 * 1. No cookie is set or linked to the trigger in the popup's Popup Settings > Triggers
 * 2. The following conditions are set:
 *    - User Has Viewed X Pages
 *    - Cookie Exists (this should be the value of the popupDisplayCookie; in this case, popup-display-cookie)
 * 
 *  Advanced Targeting Conditions: User Conditions:
 *  https://docs.wppopupmaker.com/article/238-advanced-targeting-conditions-user-conditions
 * 
 *  Advanced Targeting Condition: Cookie Conditions:
 *  https://docs.wppopupmaker.com/article/237-advanced-targeting-conditions-cookie-conditions
*/

(function ($, document, undefined) {
  /**
   * The site's path
  */
  const sitePath = '/';
  /**
   * The cookie the popup "Cookie Exists" condition will check for to display the popup.
  */
  const popupDisplayCookie = 'pum-paywall';
  /**
   * The cookie that tracks the page views.
  */
  const popupTrackViewsCookieStaging = 'pum_popup_16941_page_views';
  const popupTrackViewsCookieProd = 'pum_popup_121819_page_views';

  /**
   * Set the environment
  */
  const isProduction = true;

/** Sets cookie
 * name			string name of the cookie
 * value		string or int value of the cookie
 * durationDays int number of days to set the cookie for
 * path			string the cookie path, e.g., "/my-page/"
*/
const setCookie = ( name, value, durationDays, path ) => {
  const theDate = new Date();
  theDate.setTime( theDate.getTime() + ( durationDays * 24 * 60 * 60 * 1000 ) );
  let expires = 'expires=' + theDate.toUTCString();
  document.cookie = name + '=' + value + ';' + expires + ';path=' + path;
};

/**
   * Checks for a cookie's existence.
   * name string cookie name.
  */
const getCookie = ( name ) => {
  let value = '; ' + document.cookie;
  let parts = value.split( '; ' + name + '=' );
  
  if ( parts.length == 2 ) {
    return parts.pop().split( ';' ).shift();
  }
};

/**
 * Gets the value of a specified cookie.
 * name		string cookie name.
*/
const getCookieValue = ( name ) => {
  let nameEQ = name + "=";
  let ca = document.cookie.split( ';' );
  
  for(let i=0;i < ca.length;i++) {
    let c = ca[ i ];
    
    while (c.charAt( 0 ) == ' ') c = c.substring( 1, c.length );
    
    if ( c.indexOf( nameEQ ) == 0 ) {
      return c.substring( nameEQ.length, c.length );
    }
  }
  
  return null;
};

$( document )
  .on( 'pumInit', () => {
    // Set cookie to display the popup if the popup exists, the cookie does not exist or has expired, and the user has 20 or more page views.
    if (PUM.getPopup(16941) || PUM.getPopup(121819)) {
      const popuptrackViewsCookie = isProduction ? popupTrackViewsCookieProd : popupTrackViewsCookieStaging;
  
      if (!getCookie(popupDisplayCookie) && getCookie(popuptrackViewsCookie) >= 4) {
        // Set cookie for 60 days (two months)
        setCookie(popupDisplayCookie, 'for-two-months', 60, sitePath);
      }
    }
} );

}(jQuery, document))

/* MEMBERPRESS DOWNLOADS
/* Delete all in the first child
**********************************/
document.addEventListener('DOMContentLoaded', () => {
  const spans = document.querySelectorAll('span[style*="position:relative;top:1px;"]');

  spans.forEach(span => {
      // Controlliamo se l'elemento span ha un antenato con la classe desiderata
      const hasParentWithClass = (element, className) => {
        let parent = element.parentNode;
        while (parent) {
          if (parent.classList && parent.classList.contains(className)) {
              return true;
          }
          parent = parent.parentNode;
        }
        return false;
      };

      // Eseguiamo l'operazione solo se l'elemento span ha uno dei due genitori
      if (hasParentWithClass(span, 'mpdl-file-links-item') || hasParentWithClass(span, 'mpdl-file-link')) {
          span.firstChild.textContent = "";
      }
  });
});

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
});

Script for gifts, deactivate mouse on box to send gift --- TO REVIEW
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
});
*/
