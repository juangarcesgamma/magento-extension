require(['jquery'], function($) {
    $(document).ready( function() {
        $("#warranty_authentication_auth_mode").change(function(){
            if($(this).val() == "0"){
                $("#warranty_authentication_store_id").prop('disabled', true);
                $("#warranty_authentication_api_key").prop('disabled', true);
                $("#warranty_authentication_sandbox_store_id").prop('disabled', false);
                $("#warranty_authentication_sandbox_api_key").prop('disabled', false);
            }else{
                $("#warranty_authentication_store_id").prop('disabled', false);
                $("#warranty_authentication_api_key").prop('disabled', false);
                $("#warranty_authentication_sandbox_store_id").prop('disabled', true);
                $("#warranty_authentication_sandbox_api_key").prop('disabled', true);
            }
        });
        $("#warranty_authentication_auth_mode").change();

        $("#warranty_enableExtend_enable").change(function () {
            if($(this).val() == "1"){
                $("#syncBtn").prop('disabled', false);
            } else {
                $("#syncBtn").prop('disabled', true);
            }
        });

        $("#warranty_enableExtend_enable").change();
    });
});