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

    } else {
        e.className = 'wpbiztextwc-bad-validation';
        
    }

}

ready(function () {

    var override_chbx = document.querySelector('#wpbiztextwc_override_order_status_chbx');
  
    if (override_chbx != null && override_chbx != ''){
        override_chbx.addEventListener('click', function(){
            var send_sms_btn = document.querySelector('#wpbiztextwc_send_sms_metabxbtn');
            if (override_chbx.checked == true){
                send_sms_btn.disabled = true;
                document.getElementById("wpbiztextwc_custom_sms_templates").disabled = true;

                var current_status = document.getElementById('wpbiztextwc_override_order_status_chbx_text').innerHTML.toLowerCase();
                if (current_status == 'on hold') current_status = 'on-hold';
                
                var template_option = document.querySelector('#wpbiztextwc_status_' + current_status);
                
                document.getElementById('wpbiztextwc_custom_sms_txtarea').value = template_option.getAttribute('data-sms-template');

            } else {
                send_sms_btn.disabled = false;
                document.getElementById("wpbiztextwc_custom_sms_templates").disabled = false;
                document.getElementById('wpbiztextwc_custom_sms_txtarea').value = '';
            }
        
        });
    }
    
    //order status box
      
        jQuery('.woocommerce-order-data').find('#order_status').select2().on('change', function (e) {
            //alert(this.value)
            
           jQuery('#wpbiztextwc_status_change_checkbox_section').css({"display": "block", "border-top": "solid 1px black", "padding-top": "5px" });
           jQuery('#wpbiztextwc_override_order_status_chbx_text').text(this.value.substring(3));
           
            if (document.querySelector('#wpbiztextwc_override_order_status_chbx')) {
                if (document.querySelector('#wpbiztextwc_override_order_status_chbx').checked == true){
                
                        var template_option = document.querySelector('#wpbiztextwc_status_' + this.value.substring(3));
            
                        document.getElementById('wpbiztextwc_custom_sms_txtarea').value = template_option.getAttribute('data-sms-template');
                
                }
            }
           
         
        });
        

});
        

function wpbiztextwc_select_template(obj){

    var getData = obj.options[obj.selectedIndex].getAttribute('data-sms-template');
    document.getElementById('wpbiztextwc_custom_sms_txtarea').value = getData;

}

function wpbiztextwc_selectplaceholder(obj){
    
    var focusId = document.getElementById('wpbiztextwc_custom_sms_txtarea');
    
                
                
                var startPos = focusId.selectionStart;
                var endPos = focusId.selectionEnd;
        
                var a = focusId.value;
                var b = obj.value;
                var position = parseInt(endPos);
                var output = [a.slice(0, position), b, a.slice(position)].join('');
                
                focusId.value = output;
                
                obj.value = "insert";

}

var wpbiztextwc_send_to_client = function () {
    var txt = document.getElementById('wpbiztextwc_custom_sms_txtarea').value;

    if (txt.trim() === ""){
        document.getElementById('wpbiztextwc_custom_sms_error_msg').innerHTML = "<div class='error inline'>SMS not sent, a message is required.</div>"
    } else {
        document.getElementById('wpbiztextwc_custom_sms_error_msg').innerHTML = "";
        var postId = document.getElementById('wpbiztextwc_send_sms_metabxbtn').dataset.id;
        var route = document.getElementById('wpbiztextwc_send_sms_metabxbtn').dataset.link;

        var parm = 'action=wpbiztextwc_send_custom_text&id=' + encodeURIComponent(postId) + '&txt=' + encodeURIComponent(txt);
        var xhr = new XMLHttpRequest();
        xhr.open("POST", route, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;');
        xhr.send(parm);
        xhr.onreadystatechange = function () {
        
            if (this.readyState == 4 && this.responseText != 0) {
                location.reload();

            }
            if (this.readyState == 4 && this.responseText == 0) {
                //location.reload(); 
                document.getElementById('wpbiztextwc_custom_sms_error_msg').innerHTML = "<div class='error inline'>SMS was not sent. Ensure that there is a Mobile Phone number. Enter and update before sending SMS.</div>"
                
            }


        };
    }

    
       
}