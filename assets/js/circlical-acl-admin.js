function CirclicalAclAdmin()
{

    this.isValidEmailAddress = function(emailAddress) {
        var pattern = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i;
        return pattern.test(emailAddress);
    };

    this.showUsers = function()
    {
        $('div#acl_viewport').load( "acl-admin/users", function(){


        });
    };

    this.addUserToList = function( user_object ){

    };

    this.setup = function()
    {
        var xxx = this;
        $(document).on( "click", "div#acl_content ul.vertical-list > li", function(){
            console.log( "Hit on : " + $(this).data('id') );
            switch( $(this).data('id') )
            {
                case 'users':
                    xxx.showUsers();
                    break;
            }
        });

        $(document).on( "click", "#users-action-acl-invite", function(){
            var all_ok   = true,
                invitees = [];

            $.each( $('div#users-action > div.invite_area input[type=text]'), function( i, v ){
                var e        = $(v).val();

                if( e )
                {
                    if( xxx.isValidEmailAddress( e ) )
                    {
                        invitees.push( e );
                    }
                    else
                    {
                        all_ok = false;
                        $(v).siblings( ".validation_error").animate({ opacity: 1.0 });
                    }
                }
            });

            if( all_ok && invitees.length )
            {
                $("#users-action-acl-progress").html( "Starting invitation process").fadeIn();
                $.post( 'acl-admin/index/invite-users', { 'email_addresses[]': invitees, message: $("#welcome_message").val() }, function( json ){

                    if( json.success )
                    {
                        // show a thanks interstitial
                        $("#users-action-acl-progress").addClass('success').html( "Success! Invitations have been sent!" );
                        setTimeout( function(){
                            $("#users-action-acl-progress").fadeOut( 'slow', function(){
                                $(this).removeClass("success");
                            });
                        }, 3500 );
                    }
                    else
                    {
                        if( json.errors.field )
                        {
                            var user_fields = $("div.invite_area input[type=text]");
                            $.each( json.errors.field, function( s, t ){
                                $.each( user_fields, function( u, v ){
                                    if( $(v).val() == s )
                                    {
                                        $(v).siblings( ".validation_error").html( t ).animate({ opacity: 1.0 });
                                    }
                                });
                            });
                        }

                        $("#users-action-acl-progress").addClass('error').html( "Some errors have occurred. No invitations sent." );
                        setTimeout( function(){
                            $("#users-action-acl-progress").fadeOut( 'slow', function(){
                                $(this).removeClass("error");
                            });
                        }, 3500 );
                    }

                }, 'json' );
            }
        });

        $(document).on( "focus", 'div#users-action > div.invite_area input[type=text]', function(){
            $(this).siblings( ".validation_error").animate({ opacity: 0 });
        });


    };
};

var CLADM = new CirclicalAclAdmin();

$(document).ready( function(){
    CLADM.setup();
});