/*
 * signup.js
 * jquery validation plugin based form validation
 *
 * */
$(document).ready(function () {

    // RUN AJAX TIMER
    initTimer();

    // set a timeout so that ajax calls will be delayed
    function initTimer(){
    var myTimer = 0;
    $("#form").on('keydown', function() {
        // cancel any previously-set timer
        if (myTimer) {
            clearTimeout(myTimer);
        }

        myTimer = setTimeout(function() {
            // call validation settings
            checkForm();
        }, 400); //delay time in milliseconds
    });
    }

    function checkForm(){
    $('#form').validate({ // initialize the plugin
        errorPlacement: function(error, element) {
            error.insertBefore(element);
        },
        rules: {
            gid: {
                required: true
            },
            username: {
                required: true,
                minlength: 4,
                maxlength: 48,
                remote: {
                    url: "system/plugins/signup/js/check-username.php",
                    type: "post"
                    }
            },
            email: {
                required: true,
                email: true,
                maxlength: 128,
                remote: {
                    url: "system/plugins/signup/js/check-emailBooking.php",
                    type: "post"
                }
            },
            password1: {
                required: true,
                minlength: 4,
                maxlength: 48
            },
            password2: {
                required: true,
                minlength: 4,
                maxlength: 48,
                equalTo: "#password1"
            },
            checkTerms: {
                required: true
            },
            firstname: {
                required: true
            },
            lastname: {
                required: true,
                minlength: 2
            },
            street: {
                required: true
            },
            zipcode: {
                required: true,
                number: true,
                minlength: 2,
                maxlength: 10
            },
            city: {
                required: true,
                minlength: 2
            },
            country: {
                required: true,
                minlength: 2
            },
            newEmail: {
                email: true,
                maxlength: 128,
                remote: {
                    url: "system/plugins/signup/js/check-emailChange.php",
                    type: "post"
                }
            },
            newPassword1: {
                minlength: 4,
                maxlength: 48
            },
            newPassword2: {
                minlength: 4,
                maxlength: 48,
                equalTo: "#newPassword1"
            },
            newFirstname: {
                minlength: 2
            },
            newLastname: {
                minlength: 2
            },
            newStreet: {
                minlength: 2
            },
            newZipcode: {
                number: true,
                minlength: 2,
                maxlength: 10
            },
            newCity: {
                minlength: 2
            },
            newCountry: {
                minlength: 2
            },
            newUrl: {
                url: true,
                minlength: 2
            },
            newTwitter: {
                url: true,
                minlength: 2
            },
            newFacebook: {
                url: true,
                minlength: 2
            }
        },
        messages: {
            username: {
                remote: "Bitte w&auml;hle Sie einen anderen Namen. &nbsp;"
            },
            email: {
                remote: "Hast Du bereits eine Einladung erhalten? Du bist noch nicht berechtigt, Dich hier anzumelden. &nbsp;"
            },
            firstname: {
                remote: "Dieses Feld ist ein Pflichtfeld. &nbsp;"
            },
            zipcode: {
                number: "Bitte geben Sie eine g&uuml;tige Postleitzahl ein. &nbsp;"
            },
            password2: {
                equalTo: "Die Passw&ouml;rter stimmen nicht &uuml;berein. &nbsp;"
            },
            newEmail: {
                remote: "Bitte w&auml;hlen Sie eine andere Emailadresse. &nbsp;"
            },
            newPassword2: {
                equalTo: "Die Passw&ouml;rter stimmen nicht &uuml;berein. &nbsp;"
            }
        }
    });
    }

    /*
     * function on select set legend (text) in frontend beneath the form
     */
    $('#1_hidden').hide();
    $('#2_hidden').hide();
    $('#3_hidden').hide();
    $('#4_hidden').hide();
    $('#5_hidden').hide();
    $('#gid').change(function(){
        // gid 0
        if($('#gid').val() == '0') {
            $('#0_hidden').fadeIn();
        } else {
            $('#0_hidden').hide();
            $('#1_hidden').hide();
            $('#2_hidden').hide();
            $('#3_hidden').hide();
            $('#4_hidden').hide();
            $('#5_hidden').hide();
        }
        // gid 5
        if($('#gid').val() == '5') {
            $('#5_hidden').fadeIn();
        }
        // gid 4
        if($('#gid').val() == '4') {
            $('#4_hidden').fadeIn();
        }
        // gid 3
        if($('#gid').val() == '3') {
            $('#3_hidden').fadeIn();
        }
        // gid 2
        if($('#gid').val() == '2') {
            $('#2_hidden').fadeIn();
        }
        // gid 1
        if($('#gid').val() == '1') {
            $('#1_hidden').fadeIn();
        }
    });
/* end document ready */
});