/***********
*
* Countdown Timer
*
************/

// Set the date we're counting down to
/// var countDownDate = new Date("Jan 1, 2022 01:00:00").getTime();

// Update the count down every 1 second
///var x = setInterval(function() {

  // Get today's date and time
  ///var now = new Date().getTime();

  // Find the distance between now and the count down date
  ///var distance = countDownDate - now;

  // Time calculations for days, hours, minutes and seconds
  /*** var days = Math.floor(distance / (1000 * 60 * 60 * 24));
  var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  var seconds = Math.floor((distance % (1000 * 60)) / 1000); ***/

  // Output the result in an element with id="countdownTimer"
  ///document.getElementById("countdownTimer").innerHTML = days + " giorni " + hours + " ore " + minutes + " minuti " + seconds + " secondi ";

  // If the count down is over, write some text
  /*** if (distance < 0) {
    clearInterval(x);
    document.getElementById("countdownTimer").innerHTML = "0 giorni 0 ore 0 minuti 0 secondi";
  }
}, 1000); ***/

/*
Script for gifts, fix some pages
*/
window.addEventListener("load", function() {
    var body = document.getElementsByTagName("body")[0];
    var href = window.location.href;
    var coupon = "WOMENSDAY2022";
    if (body.innerHTML.indexOf("Regala un Abbonamento") !== -1) {
        var checkbox = document.getElementById("mpgft-signup-gift-checkbox1");
        checkbox.checked = true;
        checkbox.parentNode.parentNode.className += " mepr-hidden";
        //document.getElementsByName("mepr_coupon_code")[0].value = coupon;
        //document.getElementsByClassName("have-coupon-link")[0].className += " mepr-hidden";
        //document.getElementsByClassName("mepr_price")[0].className += " mepr-hidden";
    }
    /*if (
        href.indexOf("/abbonamento/3-mesi") > -1 ||
        href.indexOf("/abbonamento/6-mesi") > -1 ||
        href.indexOf("/abbonamento/12-mesi") > -1) {
        document.getElementsByName("mepr_coupon_code")[0].value = coupon;
    }*/
});

/*
Script for gifts, deactivate mouse on box to send gift
*/
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
