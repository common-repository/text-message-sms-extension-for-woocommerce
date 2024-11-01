function ready(fn) {
    if (document.readyState != 'loading') {
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}

function wpbiztextwcMobileNotifValidation(e) {

    var inputValue = e.value.trim();
    var reg = new RegExp(/^\+?1?\s*?\(?\d{3}(?:\)|[-|\s])?\s*?\d{3}[-|\s]?\d{4}$/);
    if (reg.test(inputValue) || inputValue == "") {
        e.className = '';
        e.value = inputValue.replace(/\D/g,'').replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        document.querySelector('#wpbiztextwc-error-templates').innerHTML = "";

    } else {
        e.className = 'wpbiztextwc-bad-validation';
        
    }

}

function wpBizTextWcTextTemplateValidation(e){
  
    if (e.value.trim() == ''){
        e.className += ' wpbiztextwc-bad-validation';
        e.value = e.value.trim();
    } else { 
        e.className = e.className.replace(/wpbiztextwc-bad-validation/g,'');
    }
}

ready(function () {

    var bizTextInputClick = false;

    function wpbiztextwc_clickTable(e) {

        if (e.target.tagName == "INPUT" || e.target.tagName == "TEXTAREA" ||
            (e.target.tagName == 'SPAN' && e.target.className === 'select2-selection__choice__remove') ||
            (e.target.tagName == 'UL') && (e.target.className == 'select2-selection__rendered')) {

            bizTextInputClick = true;

        };

        if (e.target.tagName == "A" && bizTextInputClick) {

            var message = "Any changes you made may not be saved.";
            action = confirm(message) ? true : event.preventDefault();

        }

    }

    var bizTextTable = document.getElementsByTagName('form');

    for (var i = 0, len = bizTextTable.length; i < len; i++) {
        bizTextTable[i].addEventListener('click', wpbiztextwc_clickTable);
    }




    var currentFocus;
    var endPos;
    var startPos;
    var btnClassName = document.getElementsByClassName("wpbiztextwc-placeholders-button");
    var addPlaceHolder = false;
    var currentClick;


    //polyfills element closest
    if (!Element.prototype.matches) {
        Element.prototype.matches = Element.prototype.msMatchesSelector ||
            Element.prototype.webkitMatchesSelector;
    }

    if (!Element.prototype.closest) {
        Element.prototype.closest = function (s) {
            var el = this;

            do {
                if (el.matches(s)) return el;
                el = el.parentElement || el.parentNode;
            } while (el !== null && el.nodeType === 1);
            return null;
        };
    }


    var biztext_id = document.querySelector('#wpbiztextwc_biztext_id');
    if (biztext_id) {
        document.getElementsByClassName("woocommerce-save-button")[0].addEventListener('click', wpbiztextwcValidation, false);
    } else {
        document.getElementsByClassName("woocommerce-save-button")[0].addEventListener('click', wpBiztextWcValidate, false);
    }

    function wpBiztextWcValidate(e){
        if (document.querySelectorAll('.wpbiztextwc-bad-validation').length > 0){
            e.preventDefault();
            
            document.querySelector('#wpbiztextwc-error-templates').innerHTML = "<div class='error inline'><p>Your settings have not been saved. Please make sure templates are not empty, even if disabled.</p></div>";
           
        }
    }

    var validationFlag = false;
    function wpbiztextwcValidation(e) {
        
        if (document.querySelectorAll('.wpbiztextwc-bad-validation').length == 0) {
            
            if (!validationFlag) {
                e.preventDefault();
                validationFlag = true;


                var bizTextData = {

                    "websiteId": document.getElementById("wpbiztextwc_biztext_id").value.trim(), // the encrypted website id from my-account
                    "txt": "",
                    "from": ""

                }

                var spinner = document.querySelector('#wpbiztextwc-spinner');
                spinner.classList.add('is-active');

                var xhr = new XMLHttpRequest();
                xhr.open("POST", "https://www.biztextsolutions.com/api/send/to-business", true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                setTimeout(function () {
                    xhr.send(JSON.stringify(bizTextData));
                }, 1000);

         

                document.querySelector('#wpbiztextwc-verification-text').innerText = 'Verifying ';
                xhr.onreadystatechange = function () {

                    if (this.readyState == 4 && this.status == 400) {

                        document.getElementById('wpbiztextwc_hidden_field').value = 'Y';
                        spinner.classList.remove('is-active');
                        setTimeout(function () {
                            document.getElementsByClassName("woocommerce-save-button")[0].click();
                        }, 0);


                    }
                    if (this.readyState == 4 && this.status != 400) {

                        document.getElementById('wpbiztextwc_hidden_field').value = 'N';
                        spinner.classList.remove('is-active');

                        setTimeout(function () {
                            document.getElementsByClassName("woocommerce-save-button")[0].click();
                        }, 0);

                    }

                };


            } else {
                document.getElementsByClassName("woocommerce-save-button")[0].click();

            }
        } else {
            e.preventDefault();            
            document.querySelector('#wpbiztextwc-error-templates').innerHTML = "<div class='error inline'><p>Your settings have not been saved. Please make sure to enter a valid Notification Mobile Number.</p></div>";

        }

    }


    var customer_status_change_chbx = document.getElementsByClassName("wpbiztextwc-status-change-chbx")[0];
    if (!document.querySelector('#wpbiztextwc_biztext_id')) {
        customer_status_change_chbx.addEventListener('click', function () {

            var chbox = document.getElementsByClassName("wpbiztextwc-status-change-chbx")[0];
            if (chbox.checked) {

                document.querySelector('#btwc_customer_sms_status').innerHTML = "";
                document.querySelector('#btwc_customer_sms_statuschange').innerHTML = "Same SMS for Status Change is Enabled";
                
                document.getElementsByTagName('table')[1].style.color = "lightgrey";

                for (var x = 0; x < document.getElementsByTagName('table')[1].getElementsByTagName('textarea').length; x++) {

                    document.getElementsByTagName('table')[1].getElementsByTagName('textarea')[x].style.color = "lightgrey";

                }

                document.getElementsByTagName('table')[2].getElementsByTagName('textarea')[0].style.color = "#444";


            } else {

                for (var x = 0; x < document.getElementsByTagName('table')[1].getElementsByTagName('textarea').length; x++) {

                    document.getElementsByTagName('table')[1].getElementsByTagName('textarea')[x].style.color = "#444";

                }

                document.getElementsByTagName('table')[1].style.color = "#444";
                document.querySelector('#btwc_customer_sms_status').innerHTML = "Same SMS for Status Change is Not Enabled";
                document.querySelector('#btwc_customer_sms_statuschange').innerHTML = "";
                document.getElementsByTagName('table')[2].getElementsByTagName('textarea')[0].style.color = "lightgrey";
            }

        });
    }

    for (var x = 0; x < btnClassName.length; x++) {

        btnClassName[x].addEventListener('click', wpbiztextwcAddPlaceholder, false);
        btnClassName[x].addEventListener('mouseover', wpbiztextwcGetFocus, false);

    }

    if (!document.querySelector('#wpbiztextwc_biztext_id')) {
        document.addEventListener('click',wpBizTextWCDocClick, false);
    }

    // functions

    function wpbiztextwcGetFocus(event) {

        if (document.getElementById("wpbiztextwc-placeholders").getAttribute("class") == "wpbiztextwc-placeholders active-focus") {

            currentFocus = document.activeElement.id;
            var ctl = document.getElementById(currentFocus);
            startPos = ctl.selectionStart;
            endPos = ctl.selectionEnd;

        }

    };


    function wpbiztextwcAddPlaceholder(e) {

        if (currentFocus != "" && document.getElementById("wpbiztextwc-placeholders").getAttribute("class") == "wpbiztextwc-placeholders active-focus") {

            var getPlaceHolders = document.getElementsByClassName("wpbiztextwc-placeholders")[0];


            //getPlaceHolders.className += " active-focus";

            var focusId = document.getElementById(currentFocus);

            var a = focusId.value;
            var b = this.innerHTML;
            var position = parseInt(endPos);
            var output = [a.slice(0, position), b, a.slice(position)].join('');

            if (focusId.innerHTML == "") {

                focusId.value = b;

            } else {

                focusId.value = output;

            }

            setTimeout(function () {
                focusId.focus();
                focusId.selectionEnd = position + (b.length);
                focusId.selectionStart = position + (b.length);
            }, 0);

        }



    }

    function wpBizTextWCDocClick(e) {

        var placeholderDiv = document.getElementById("wpbiztextwc-placeholders");
        var placeHolderClass = document.getElementsByClassName("wpbiztextwc-placeholders");

        if (e.target.type == "textarea") {

            // add bold - make active placeholders

            if (placeholderDiv.getAttribute("class") != "wpbiztextwc-placeholders active-focus") {

                placeHolderClass[0].className += " active-focus";


            }

        } else {

            // remove - make active placeholders

            if (e.target.getAttribute("class") != "wpbiztextwc-placeholders-button") {

                placeHolderClass[0].className = "wpbiztextwc-placeholders";


            }


        }

    }


});