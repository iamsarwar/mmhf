<?php
/*
Template Name: ForgotPassword
*/

the_post();
get_header();

	$error = '';
	$success = '';
    session_start();
    $_SESSION['process_fee'] = 1;

	global $wpdb, $PasswordHash, $current_user, $user_ID;

	if(isset($_POST['task']) && $_POST['task'] == 'forgot_pass' ) {
	    //echo $_SESSION['passUserID'];
	    //exit;
        $password1 = $wpdb->escape($_POST['password1']);
        $password2 = $wpdb->escape($_POST['password2']);
        $user_id = $wpdb->escape($_SESSION['passUserID']);
        if( $password1 == "" || $password2 == "" || $user_id == "") {
            $error= 'Please don\'t leave the required fields.';
        } else if($password1 <> $password2 ){
            $error= 'Password do not match.';
        } else {
            //wp_set_password( $password1, $user_id );
            $userdata['ID'] = $user_id; //user ID
            $userdata['user_pass'] = $password1;
            wp_update_user( $userdata );
            $success = 'You have successfully reset your password';
            echo '<meta http-equiv="refresh" content="0;url='.get_bloginfo('url').'/my-account/" />';
        }
    }
?>
<div class='container_wrap container_wrap_first main_color <?php avia_layout_class( 'main' ); ?>'>

    <div class='container'>
        <main class="template-page content  av-content-full alpha units" role="main" itemprop="mainContentOfPage">

            <article class="post-entry post-entry-type-page post-entry-43" itemscope="itemscope" itemtype="https://schema.org/CreativeWork">

                <div class="entry-content-wrapper clearfix">
                    <header class="entry-content-header"></header>
                    <div class="entry-content" itemprop="text">
                        <div class="woocommerce">

                            <div id="message">
                                <?php
                                if(! empty($error) ) :
                                    echo '<ul class="woocommerce-error">';
                                    echo '<li class="error">'.$error.'</li>';
                                    echo '</ul>';
                                    echo '
                                    	<script type="text/javascript">
                                            $(document).ready(function () {
                                                jQuery(\'#signon_form\').css(\'display\', \'none\');
                                                jQuery(\'#loading\').css(\'display\', \'none\');
                                                jQuery(\'#pin_form\').css(\'display\', \'none\');
                                                jQuery(\'#pass_form\').css(\'display\', \'block\');
                                                jQuery("#main").removeClass("all_colors").addClass("all_colors myAccClass");
                                            });
                                        </script>
                                    ';
                                endif;
                                ?>

                                <?php
                                if(! empty($success) ) :
                                    echo '<p class="error">'.$success.'';
                                endif;
                                ?>
                            </div>

                            <div id="loading" style="display:none;">
                                <center><img src="<?php echo get_bloginfo('url');?>/wp-content/uploads/2019/12/charging.gif"></center>
                            </div>

                            <form id="signon_form" name="signon_form" class="woocomerce-form woocommerce-form-login login" method="post" onsubmit="return false;">
                                <h3>Forgot password?</h3>
                                <p id="auth_label"><label>Authorized Mobile No <span class="required" title="required" style="color:red;">*</span></label></p>
                                <p><input type="text" style="width:50%;" value="" name="mobile_no" id="mobile_no" required /></p>
                                <button name="btnregister" onclick="get_pin();" class="button">Get pin</button>
                            </form>
                            <div class="pin_form" id="pin_form" style="display:none;">
                                <h3>Please give your SMS OTP</h3><br>
                                <p><label>PIN Code:&nbsp;</label><input type="text" name="pin_code" id="pin_code" style="width:50%;" required /></p>
                                <button name="btnMatch" onclick="match_pin()" class="button">Proceed</button>&nbsp;<button name="btnResend" onclick="resend_pin();" class="button">Resend</button>
                            </div>
                            <form class="pass_form" id="pass_form" class="woocomerce-form woocommerce-form-login login" style="display:none;height:400px;" method="post" action="">
                                <h3>Please reset your password</h3><br>
                                <p><label>Password <span class="required" title="required" style="color:red;">*</span></label></p>
                                <p><input type="password" value="" style="width:50%;" name="password1" id="password1" required /></p>
                                <p><label>Confirm Password <span class="required" title="required" style="color:red;">*</span></label></p>
                                <p><input type="password" value="" style="width:50%;" name="password2" id="password2" required /></p>
                                <input type="hidden" name="user_id" id="user_id" value="" />
                                <input type="hidden" name="task" value="forgot_pass" />
                                <button id="btnResetPassword" class="button" type="submit">Reset Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </article>
        </main>
    </div>
</div>
<script src="//code.jquery.com/jquery-1.8.3.min.js"></script>
<script>
    $(document).ready(function () {
        $("#main").removeClass("all_colors").addClass("all_colors myAccClass");
    });

    function resend_pin(){
        var mobile_no = jQuery('#s_mobile_no').val();
        jQuery.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: 'action=get_mobile_pin&mobile_no=' + mobile_no + '&security=' + ajax_object.ajax_nonce,
            success: function (results) {
                alert('Your OTP code resend successfully.');
            }
        });

    }

    function get_pin(){
        //alert('too');
        var mobile_no = jQuery('#mobile_no').val();
        //alert(mobile_no);
        if(mobile_no == ''){
            alert('Please enter mobile no');
            //return false;
        }
        else
        {
            jQuery.ajax({
                url:"<?php echo get_bloginfo('url');?>/wp-admin/admin-ajax.php",
                type:'POST',
                data:'action=user_check&mobile_no=' + mobile_no,
                success:function(results) {
                    console.log(results);
                    if(results > 0){
                        jQuery('#signon_form').css('display', 'none');
                        jQuery('#loading').css('display', 'block');
                        jQuery("#main").removeClass("all_colors").addClass("all_colors myAccClass");
                        document.getElementById('user_id').value = results;
                        jQuery.ajax({
                            url: "<?php echo get_bloginfo('url');?>/wp-admin/admin-ajax.php",
                            type: 'POST',
                            data: 'action=setuser&t=' + results,
                            success: function (results) {
                                jQuery.ajax({
                                    url: ajax_object.ajax_url,
                                    type: 'POST',
                                    data: 'action=get_mobile_pin&mobile_no=' + mobile_no + '&security=' + ajax_object.ajax_nonce,
                                    success: function (results) {
                                        //alert(results);
                                        //console.log(output);
                                        if (results == 'error0') {
                                            alert('Please enter your valid mobile phone no');
                                            jQuery('#signon_form').css('display', 'block');
                                            jQuery('#loading').css('display', 'none');
                                        } else {
                                            jQuery('#loading').css('display', 'none');
                                            jQuery('#pin_form').css('display', 'block');
                                        }
                                    }
                                });
                            }
                        });
                    }
                    else
                        alert('Mobile no. not exists.');
                }
            });
            //return false;
        }

    }

    function match_pin(){
        //alert('too');
        var pin_code = jQuery('#pin_code').val();
        var user_mobile = jQuery('#mobile_no').val();
        //alert(outlets);
        if(pin_code == '')
            alert('Please enter pin code');
        else
        {
            jQuery('#pin_form').css('display', 'none');
            jQuery('#loading').css('display', 'block');
            jQuery( "#main" ).removeClass( "all_colors" ).addClass( "all_colors myAccClass" );
            jQuery.ajax({
                url:"<?php echo get_bloginfo('url');?>/wp-admin/admin-ajax.php",
                type:'POST',
                data:'action=match_mobile_pin&pin_code=' + pin_code + '&user_mobile=' + user_mobile,
                success:function(results)
                {
                    //alert(results);
                    //console.log(output);
                    if(results == 'error0'){
                        alert('Your PIN code does not match. Please try again...');
                        jQuery('#pin_form').css('display', 'block');
                        jQuery('#loading').css('display', 'none');
                    }
                    else{
                        jQuery('#loading').css('display', 'none');
                        jQuery('#pass_form').css('display', 'block');
                        //document.getElementById('reg_form').submit();
                    }
                }
            });
        }
    }
</script>
<?php get_footer() ?>
