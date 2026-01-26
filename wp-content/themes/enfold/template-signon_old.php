<?php
/* 
Template Name: SignOn
*/

the_post();
get_header()
?>
		<div class='container_wrap container_wrap_first main_color <?php avia_layout_class( 'main' ); ?>'>

			<div class='container'>
                <main class="template-page content  av-content-full alpha units" role="main" itemprop="mainContentOfPage">

            		<article class="post-entry post-entry-type-page post-entry-43" itemscope="itemscope" itemtype="https://schema.org/CreativeWork">

            			<div class="entry-content-wrapper clearfix">
                            <header class="entry-content-header"></header>
                            <div class="entry-content" itemprop="text">
                                <div class="woocommerce">

	<?php
        if(!empty($_REQUEST['msg']))
            echo '<h3 style="color:red;">'.$_REQUEST['msg'].'</h3>';

	$error= '';
	$success = '';
    session_start();
    $_SESSION['process_fee'] = 1;

	global $wpdb, $PasswordHash, $current_user, $user_ID;

	if(isset($_POST['task']) && $_POST['task'] == 'register' ) {


		$password1 = $wpdb->escape($_POST['password1']);
		$password2 = $wpdb->escape($_POST['password2']);
		$first_name = $wpdb->escape($_POST['f_name']);
		$v_account_type = $wpdb->escape($_POST['account_type']);
        $username = $wpdb->escape($_POST['mobile_no']);
        $useremail = $wpdb->escape($_POST['user_email']);
		$vehicle_reg_no = $wpdb->escape(trim($_POST['vehicle_reg_no']));
		$vehicle_type = $wpdb->escape($_POST['vehicle_type']);
		//exit;
        $nid = $wpdb->escape($_POST['nid']);
        $address = $wpdb->escape($_POST['address']);
		$auth_person_name = $wpdb->escape($_POST['auth_person_name']);
        $amount = $wpdb->escape($_POST['user_amount']);
        $last_invoice_id = $wpdb->escape($_POST['last_invoice_id']);
        $pgw_transaction_id = $wpdb->escape($_POST['pgw_transaction_id']);

		if( $password1 == "" || $password2 == "" || $username == "" || $first_name == "") {
			$error= 'Please don\'t leave the required fields.';
		} else if(username_exists($username) ) {
			$error= 'Mobile no already exist.';
		} else if($password1 <> $password2 ){
			$error= 'Password do not match.';
		} else {

			$user_id = wp_insert_user( array ('first_name' => apply_filters('pre_user_first_name', $first_name), 'user_pass' => apply_filters('pre_user_user_pass', $password1), 'user_login' => apply_filters('pre_user_user_login', $username), 'user_email' => apply_filters('pre_user_user_email', $useremail), 'role' => 'subscriber' ) );
			if( is_wp_error($user_id) ) {
				$error= 'Error on user creation.';
			} else {
				do_action('user_register', $user_id);
                $user_table_name = $wpdb->prefix . "users";
                $user_update_data = $wpdb->update($user_table_name, array('id'=>$user_id, 'v_account_type'=>$v_account_type, 'auth_person_name'=>$auth_person_name, 'user_address'=>$address), array('id'=>$user_id));

                $users_info_table_name = $wpdb->prefix . "users_info";
                $users_info_insert_data = $wpdb->insert(
                                                        $users_info_table_name,
                                                        array(
                                                            'user_id' => $user_id,
                                                            'nid' => $nid,
                                                            'vehicle_type' => $vehicle_type,
                                                            'vehicle_reg_no' => $vehicle_reg_no,
                                                            //'process_amount' => $amount,
                                                            'date_inserted' => date('Y-m-d H:i:s')
                                                        ),
                                                        array(
                                                            '%d',
                                                            '%s',
                                                            '%s',
                                                            '%s',
                                                            //'%d',
                                                            '%s'
                                                        )
                                                    );
                $user_info_id = $wpdb->insert_id;
                $process_fees_table_name = $wpdb->prefix . "user_process_fees";
                $process_fees_insert_data = $wpdb->insert(
                                                        $process_fees_table_name,
                                                        array(
                                                            'user_id' => $user_id,
                                                            'user_info_id' => $user_info_id,
                                                            'invoice_id' => $last_invoice_id,
                                                            'process_amount' => $amount,
                                                            'pgw_name' => 'free', //have to do dynamic
                                                            'pgw_transaction_id' => $pgw_transaction_id
                                                        ),
                                                        array(
                                                            '%d',
                                                            '%d',
                                                            '%s',
                                                            '%d',
                                                            '%s',
                                                            '%s'
                                                        )
                                                    );

				$success = 'You\'re successfully registered';
				echo '<meta http-equiv="refresh" content="0;url='.get_bloginfo('url').'/my-account/" />';
			}

		}

	}

    // this adds the prefix which is set by the user upon instillation of wordpress
    $process_fees_table_name = $wpdb->prefix . "user_process_fees";
    $process_fees_retrieve_data = $wpdb->get_row("SELECT MAX(id) AS last_id FROM $process_fees_table_name");
    if(!empty($process_fees_retrieve_data->last_id)){
        $last_invoice_id = $user_id.date('Ym').($process_fees_retrieve_data->last_id+1).date('his');
    }
    else
        $last_invoice_id = $user_id.date('Ym').'1'.date('his');

    $_SESSION['signon_invoie_id'] = $last_invoice_id;
    ?>

        <!--display error/success message-->
	<div id="message">
		<?php
			if(! empty($error) ) :
			    echo '<ul class="woocommerce-error">';
				echo '<li class="error">'.$error.'</li>';
				echo '</ul>';
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
                                    <h3>Don't have an account?<br /> Create one now.</h3>
                                    <p><label for="account_type">Account Type <span class="required" title="required" style="color:red;">*</span></label></p>
                                    <p><select name="s_account_type" class="select is_empty" id="s_account_type" onchange="auth_focus(this.value);">
                                            <option value="Individual">Individual</option>
                                            <option value="Corporate">Corporate</option>
                                        </select></p>
                                    <p><label>Name(Individual/ Organization) <span class="required" title="required" style="color:red;">*</span></label></p>
                                    <p><input type="text" value="" name="f_name" id="s_f_name" required /></p>
                                    <p id="auth_label" style="display:none;"><label>Authorized Person’s Name</label></p>
                                    <p id="auth_input" style="display:none;"><input type="text" value="" name="s_auth_person_name" id="s_auth_person_name" /></p>
                                    <p><label>Email </label></p>
                                    <p><input type="text" value="" name="s_user_email" id="s_user_email" /></p>
                                    <p><label>Mobile No <span class="required" title="required" style="color:red;">*</span></label></p>
                                    <p><input type="text" value="" name="s_mobile_no" id="s_mobile_no" required /></p>
                                    <p><label>Password <span class="required" title="required" style="color:red;">*</span></label></p>
                                    <p><input type="password" value="" name="s_password1" id="s_password1" required /></p>
                                    <p><label>Confirm Password <span class="required" title="required" style="color:red;">*</span></label></p>
                                    <p><input type="password" value="" name="s_password2" id="s_password2" required /></p>
                                    <p><label>NID <span class="required" title="required" style="color:red;">*</span></label></p>
                                    <p><input type="text" value="" name="s_nid" id="s_nid" required /></p>
                                    <p><label>Address <span class="required" title="required" style="color:red;">*</span></label></p>
                                    <p><input type="text" value="" name="s_address" id="s_address" required /></p>
                                    <p><label for="vehicle_type">Vehicle Type <span class="required" title="required" style="color:red;">*</span></label></p>
                                    <p><select name="s_vehicle_type" class="select is_empty" id="s_vehicle_type">
                                        <!--option value="2 Wheeler">2 Wheeler</option>
                                        <option value="3 Wheeler">3 Wheeler</option-->
                                        <option value="Car">Car</option>
                                        <option value="Jeep">Jeep</option>
                                        <option value="Microbus">Microbus</option>
                                        <option value="Pick up">Pick up</option>
                                        <option value="Minibus">Minibus</option>
                                        <option value="Bus">Bus</option>
                                        <option value="Truck 4 Wheeler">Truck 4 Wheeler</option>
                                        <option value="Truck 6 Wheeler">Truck 6 Wheeler</option>
                                        <option value="Trailer Truck">Trailer Truck</option>
                                    </select></p>
                                    <p><label>Vehicle Reg. No <span class="required" title="required" style="color:red;">*</span></label></p>
                                    <p><input type="text" value="" name="s_vehicle_reg_no" id="s_vehicle_reg_no" required /></p>
                                    <div class="alignleft"><p><?php if($success != "") { echo $success; } ?> <?php if($error!= "") { echo $error; } ?></p></div>
                                    <button name="btnregister" onclick="get_pin();" class="button">Submit</button>
                                </form>
                            	<div class="pin_form" id="pin_form" style="display:none;">
                            		<h3>Please give your SMS OTP</h3><br>
                            		<p><label>PIN Code:&nbsp;</label><input type="text" name="pin_code" id="pin_code" style="width:50%;" required /></p>
                            		<button name="btnMatch" onclick="match_pin()" class="button">Proceed</button>&nbsp;<button name="btnResend" onclick="resend_pin();" class="button">Resend</button>
                            	</div>
	                            <!--style>#bKash_button:hover{box-shadow:none !important; padding:0 !important;}</style-->
                            	<div class="bkash_form" id="bkash_form" style="display:none;height:400px;">
                                    <form id="b_form" name="b_form" class="woocomerce-form woocommerce-form-login login" method="post" onsubmit="return false;">
                                        <h3>Please deposit for processing fee</h3><br>
                                        <p><label>Amount:&nbsp;</label><input type="text" readonly="readonly" name="start_amount" id="start_amount" value="100" style="width:50%;" required /></p>
                                        <p style="border: 1px solid #0c73b8; padding: 1%;font-weight: bold;width: 15%;">Payment Method</p>
                                        <p><input type="radio" name="pgw_type" id="bkash_pgw" onclick="show_hide(this.id)" checked><img src="<?php echo get_bloginfo('url');?>/wp-content/uploads/2021/01/bKash-Logo-4color.png" width="120" style="margin-left:2%;vertical-align: middle;"/></p>
                                        <p><input type="radio" name="pgw_type" id="city_pgw" onclick="show_hide(this.id)"><img src="<?php echo get_bloginfo('url');?>/wp-content/uploads/2021/03/city_logo.png" width="120" style="margin-left:2%;vertical-align: middle;"/></p>
                                        <p><input type="radio" name="pgw_type" id="nagad_pgw" onclick="show_hide(this.id)"><img src="<?php echo get_bloginfo('url');?>/wp-content/uploads/2021/03/nagad.png" width="120" style="margin-left:2%;vertical-align: middle;"/></p>
                                        <button id="bKash_button" class="button" style="/*background:transparent !important; border: none !important; padding:0 !important;*/" type="button">Deposit</button>
                                        <button id="city_button" onclick="go_city()" class="button" style="display:none;" type="button">Deposit</button>
                                        <button id="nagad_button" onclick="go_nagad()" class="button" style="display:none;" type="button">Deposit</button>
                                    </form>
                            	</div>
                            	<form id="reg_form" name="reg_form" method="post" style="display:none;">
                                    <p><input type="hidden" value="" name="f_name" id="f_name" required /></p>
                                    <p><input type="hidden" value="" name="mobile_no" id="mobile_no" required /></p>
                                    <p><input type="hidden" value="" name="user_email" id="user_email" required /></p>
                                    <p><input type="hidden" value="" name="password1" id="password1" required /></p>
                                    <p><input type="hidden" value="" name="password2" id="password2" required /></p>
                                    <p><input type="hidden" value="" name="account_type" id="account_type" required /></p>
                                    <p><input type="hidden" value="" name="vehicle_reg_no" id="vehicle_reg_no" required /></p>
                                    <p><input type="hidden" value="" name="vehicle_type" id="vehicle_type" required /></p>
                                    <p><input type="hidden" value="" name="nid" id="nid" required /></p>
                                    <p><input type="hidden" value="" name="address" id="address" required /></p>
                                    <p><input type="hidden" value="" name="auth_person_name" id="auth_person_name" /></p>
                                    <p><input type="hidden" value="" name="user_amount" id="user_amount" required /></p>
                                    <p><input type="hidden" value="<?php echo $last_invoice_id;?>" name="last_invoice_id" id="last_invoice_id" required /></p>
                                    <p><input type="hidden" value="" name="pgw_transaction_id" id="pgw_transaction_id" required /></p>
                                    <input type="hidden" name="task" value="register" />
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            </main>
        </div>
    </div>

    <script>
        function show_hide(pgw_id) {
            if(pgw_id == 'city_pgw'){
                document.getElementById('bKash_button').style.display = 'none';
                document.getElementById('city_button').style.display = 'block';
                document.getElementById('nagad_button').style.display = 'none';
            }
            else if(pgw_id == 'nagad_pgw'){
                document.getElementById('bKash_button').style.display = 'none';
                document.getElementById('city_button').style.display = 'none';
                document.getElementById('nagad_button').style.display = 'block';
            }
            else{
                document.getElementById('bKash_button').style.display = 'block';
                document.getElementById('city_button').style.display = 'none';
                document.getElementById('nagad_button').style.display = 'none';
            }
        }

        function go_city(){
            jQuery('#bkash_form').css('display', 'none');
            jQuery('#loading').css('display', 'block');
            var f_name = jQuery('#f_name').val();
            var mobile_no = jQuery('#mobile_no').val();
            var user_email = jQuery('#user_email').val();
            var password1 = jQuery('#password1').val();
            var account_type = jQuery('#account_type').val();
            var vehicle_reg_no = jQuery('#vehicle_reg_no').val();
            var nid = jQuery('#nid').val();
            var address = jQuery('#address').val();
            var vehicle_type = jQuery('#vehicle_type').val();
            var auth_person_name = jQuery('#auth_person_name').val();
            jQuery.ajax({
                url: "<?php echo get_bloginfo('url');?>/wp-admin/admin-ajax.php",
                type: 'POST',
                data: 'action=register_city&f_name=' + f_name + '&mobile_no=' + mobile_no + '&user_email=' + user_email + '&password1=' + password1 + '&account_type=' + account_type + '&vehicle_reg_no=' + vehicle_reg_no + '&nid=' + nid + '&address=' + address + '&vehicle_type=' + vehicle_type + '&auth_person_name=' + auth_person_name,
                success: function (results) {
                    var url = results.slice(0, -1);
                    //alert(results);
                    window.location.href = url;
                }
            });
        }

        function go_nagad(){
            jQuery('#bkash_form').css('display', 'none');
            jQuery('#loading').css('display', 'block');
            var f_name = jQuery('#f_name').val();
            var mobile_no = jQuery('#mobile_no').val();
            var user_email = jQuery('#user_email').val();
            var password1 = jQuery('#password1').val();
            var account_type = jQuery('#account_type').val();
            var vehicle_reg_no = jQuery('#vehicle_reg_no').val();
            var nid = jQuery('#nid').val();
            var address = jQuery('#address').val();
            var vehicle_type = jQuery('#vehicle_type').val();
            var auth_person_name = jQuery('#auth_person_name').val();
            jQuery.ajax({
                url: "<?php echo get_bloginfo('url');?>/wp-admin/admin-ajax.php",
                type: 'POST',
                data: 'action=register_nagad&f_name=' + f_name + '&mobile_no=' + mobile_no + '&user_email=' + user_email + '&password1=' + password1 + '&account_type=' + account_type + '&vehicle_reg_no=' + vehicle_reg_no + '&nid=' + nid + '&address=' + address + '&vehicle_type=' + vehicle_type + '&auth_person_name=' + auth_person_name,
                success: function (results) {
                    //var url = results.slice(0, -1);
                    //alert(results);
                    window.location.href = results;
                }
            });
        }

        function auth_focus(auth_val){
            if(auth_val == 'Corporate'){
                document.getElementById('auth_label').style.display='block';
                document.getElementById('auth_input').style.display='block';
            }
            else{
                document.getElementById('auth_label').style.display='none';
                document.getElementById('auth_input').style.display='none';
            }

        }

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
    		var mobile_no = jQuery('#s_mobile_no').val();
    		var name = jQuery('#s_f_name').val();
    		var password1 = jQuery('#s_password1').val();
    		var password2 = jQuery('#s_password2').val();
            var nid = jQuery('#s_nid').val();
            var address = jQuery('#s_address').val();
    		var vehicle_reg_no = jQuery('#s_vehicle_reg_no').val();
     		//alert(mobile_no);
    		if(mobile_no == ''){
    		    alert('Please enter mobile no');
    		    //return false;
    		}
    		else if(name == ''){
    		    alert('Please enter name');
    		    //return false;
    		}
    		else if(password1 == ''){
    		    alert('Please enter password');
    		    //return false;
    		}
    		else if(password1 != password2){
    		    alert('Password missmatch');
    		    //return false;
    		}
    		else if(nid == ''){
    		    alert('Please enter NID');
    		    //return false;
    		}
            else if(address == ''){
                alert('Please enter Address');
                //return false;
            }
    		else if(vehicle_reg_no == ''){
    		    alert('Please enter vehicle reg. no');
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
                        if(results == 0){
                            jQuery('#signon_form').css('display', 'none');
                            jQuery('#loading').css('display', 'block');
                            jQuery("#main").removeClass("all_colors").addClass("all_colors myAccClass");
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
                                        //jQuery('#bkash_form').css('display', 'block');
                                        document.getElementById('f_name').value = jQuery('#s_f_name').val();
                                        document.getElementById('mobile_no').value = jQuery('#s_mobile_no').val();
                                        document.getElementById('user_email').value = jQuery('#s_user_email').val();
                                        document.getElementById('password1').value = jQuery('#s_password1').val();
                                        document.getElementById('password2').value = jQuery('#s_password2').val();
                                        document.getElementById('account_type').value = jQuery('#s_account_type').val();
                                        document.getElementById('vehicle_reg_no').value = jQuery('#s_vehicle_reg_no').val();
                                        document.getElementById('nid').value = jQuery('#s_nid').val();
                                        document.getElementById('address').value = jQuery('#s_address').val();
                                        document.getElementById('vehicle_type').value = jQuery('#s_vehicle_type').val();
                                        document.getElementById('auth_person_name').value = jQuery('#s_auth_person_name').val();
                                    }
                                }
                            });
                        }
                        else
                            alert('Mobile no. already exists.');
                    }
                });
        		//return false;
    		}

    	}

     	function match_pin(){
    		//alert('too');
    		var pin_code = jQuery('#pin_code').val();
    		var user_mobile = jQuery('#s_mobile_no').val();
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
        				    //jQuery('#loading').css('display', 'none');
        				    //jQuery('#bkash_form').css('display', 'block');
                            //document.getElementById('user_amount').value = '100';
        				    //document.getElementById('reg_form').submit();
                            jQuery('#loading').css('display', 'block');
                            document.getElementById('user_amount').value = '100';
                            window.setTimeout(function() { document.getElementById('reg_form').submit(); }, 6000);
        				}
                    }
    			});
    		}
    	}
    </script>

	<script src="//code.jquery.com/jquery-1.8.3.min.js"></script>
        <script src="https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
	<script type="text/javascript">
        $(document).ready(function () {
            $( "#main" ).removeClass( "all_colors" ).addClass( "all_colors myAccClass" );
			$.post('<?php echo admin_url('admin-ajax.php'); ?>', { action: 'bkash_token' }, function(output) {
				//location.reload();
			});

			var paymentRequest;
			paymentRequest = {  };


			bKash.init({

				paymentMode: 'checkout',

				paymentRequest: paymentRequest,

				createRequest: function (request) {

					console.log('=> createRequest (request) :: ');
					paymentRequest.amount = '100';
					paymentRequest.invoice = $('#last_invoice_id').val();

					$.post('<?php echo admin_url('admin-ajax.php'); ?>', { action: 'bkash_createpayment', amount: paymentRequest.amount, invoice: paymentRequest.invoice }, function(output) {
						//location.reload();
						output = output.substring(0, output.length - 1)
						data = JSON.parse(output);
						console.log('got data from create  ..');
						if (data && data.paymentID != null) {
							paymentID = data.paymentID;
							bKash.create().onSuccess(data);

						} else {
							bKash.create().onError();
                            Swal.fire("", data.errorMessage, "error",{
                                closeOnClickOutside: false,
                                closeOnEsc: false,
                            });
							//alert("error with data");
						}
					});

				},
				executeRequestOnAuthorization: function () {

					console.log('=> executeRequestOnAuthorization');
					$.post('<?php echo admin_url('admin-ajax.php'); ?>', { action: 'bkash_executepayment', paymentID: paymentID }, function(output) {
						//location.reload();
						output = output.substring(0, output.length - 1)
						data = JSON.parse(output);
						//console.log('got data from execute  ..');
						if (data && data.paymentID != null) {
                            Swal.fire("", "Successfully paid", "success",{
                                closeOnClickOutside: false,
                                closeOnEsc: false,
                            });
        				    jQuery('#bkash_form').css('display', 'none');
                            jQuery('#loading').css('display', 'block');
                            document.getElementById('user_amount').value = '100';
                            document.getElementById('pgw_transaction_id').value = data.trxID;
                            window.setTimeout(function() { document.getElementById('reg_form').submit(); }, 6000);
						} else {
							bKash.execute().onError();
                            //alert("error with "+ data.errorMessage);
                            Swal.fire("Payment Failed", data.errorMessage, "error",{
                                closeOnClickOutside: false,
                                closeOnEsc: false,
                            });
        				    jQuery('#bkash_form').css('display', 'block');
                            jQuery('#loading').css('display', 'none');
                            //location.reload();
						}
					});
				},
                onClose : function () {
                    location.reload();
                }
			});

		});

    </script>

<?php get_footer() ?>


