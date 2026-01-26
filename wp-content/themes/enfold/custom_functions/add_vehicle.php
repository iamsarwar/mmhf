<?php


function add_vehicle_endpoint_content(){
    echo '<h2>Add Vehicle</h2>';
    session_start();
    $_SESSION['process_fee'] = 1;

    if(!empty($_REQUEST['msg']))
        echo '<h3>'.$_REQUEST['msg'].'</h3>';

    ?>
    <div id="loading" style="display:none;">
        <center><img src="<?php echo get_bloginfo('url');?>/wp-content/uploads/2019/12/charging.gif"></center>
    </div>
    <form id="vehicle_form" name="vehicle_form" class="woocomerce-form woocommerce-form-login login" method="post" onsubmit="return false;" style="min-height:280px;">
        <p><label for="vehicle_type">Vehicle Type <span class="required" title="required" style="color:red;">*</span></label></p>
        <p><select name="vehicle_type" class="select is_empty" style="width:50%;" id="vehicle_type">
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
        <p><input type="text" value="" name="vehicle_reg_no" id="vehicle_reg_no" style="width:50%;" required /></p>
        <input type="hidden" name="user_id" id="user_id" value="<?php echo get_current_user_id();?>" required />
        <input type="hidden" name="last_invoice_id" id="last_invoice_id" value="<?php echo generateProcessFeeInvoiceID(get_current_user_id());?>" required />
        <button id="add_button" class="button" onclick="go_vehicle();" type="button">Add</button>
    </form>
    <!--style>#bKash_button:hover{box-shadow:none !important; padding:0 !important;}</style-->
    <form class="bkash_form" id="bkash_form" style="display:none;">
        <h3>Please deposit for processing fee</h3><br>
        <p><label>Processing Fee:&nbsp;</label><input type="text" readonly="readonly" name="start_amount" id="start_amount" value="100" style="width:50%;" required /></p>
        <p style="border: 1px solid #0c73b8; padding: 1%;font-weight: bold;width: 25%;">Payment Method</p>
        <p><input type="radio" name="pgw_type" id="bkash_pgw" onclick="show_hide(this.id)" checked><img src="<?php echo get_bloginfo('url');?>/wp-content/uploads/2021/01/bKash-Logo-4color.png" width="120" style="margin-left:2%;vertical-align: middle;"/></p>
        <p><input type="radio" name="pgw_type" id="city_pgw" onclick="show_hide(this.id)"><img src="<?php echo get_bloginfo('url');?>/wp-content/uploads/2021/03/city_logo.png" width="120" style="margin-left:2%;vertical-align: middle;"/></p>
        <p><input type="radio" name="pgw_type" id="nagad_pgw" onclick="show_hide(this.id)"><img src="<?php echo get_bloginfo('url');?>/wp-content/uploads/2021/03/nagad.png" width="120" style="margin-left:2%;vertical-align: middle;"/></p>
        <button id="bKash_button" class="button" style="/*background:transparent !important; border: none !important; padding:0 !important;*/" type="button">Add</button>
        <button id="city_button" onclick="go_city()" class="button" style="display:none;" type="button">Add</button>
        <button id="nagad_button" onclick="go_nagad()" class="button" style="display:none;" type="button">Add</button>
    </form>
    <?php
    echo '
    <script>
        function show_hide(pgw_id) {
            if(pgw_id == \'city_pgw\'){
                document.getElementById(\'bKash_button\').style.display = \'none\';
                document.getElementById(\'city_button\').style.display = \'block\';
                document.getElementById(\'nagad_button\').style.display = \'none\';
            }
            else if(pgw_id == \'nagad_pgw\'){
                document.getElementById(\'bKash_button\').style.display = \'none\';
                document.getElementById(\'city_button\').style.display = \'none\';
                document.getElementById(\'nagad_button\').style.display = \'block\';
            }
            else{
                document.getElementById(\'bKash_button\').style.display = \'block\';
                document.getElementById(\'city_button\').style.display = \'none\';
                document.getElementById(\'nagad_button\').style.display = \'none\';
            }
        }

        function go_city(){
            jQuery(\'#bkash_form\').css(\'display\', \'none\');
            jQuery(\'#loading\').css(\'display\', \'block\');
            var vehicle_type = jQuery(\'#vehicle_type\').val();
            //alert(vehicle_type);
            var vehicle_reg_no = jQuery(\'#vehicle_reg_no\').val();
            var user_id = jQuery(\'#user_id\').val();
            var invoice_id = jQuery(\'#last_invoice_id\').val();
            var amount = 100;
            jQuery.ajax({
                url: "'.get_bloginfo('url').'/wp-admin/admin-ajax.php",
                type: \'POST\',
                data: \'action=addvehicle_city&vehicle_reg_no=\' + vehicle_reg_no + \'&user_id=\' + user_id + \'&invoice_id=\' + invoice_id + \'&vehicle_type=\' + vehicle_type+ \'&amount=\' + amount,
                success: function (results) {
                    var url = results.slice(0, -1);
                    //alert(results);
                    window.location.href = url;
                }
            });
        }


        function go_nagad(){
            jQuery(\'#bkash_form\').css(\'display\', \'none\');
            jQuery(\'#loading\').css(\'display\', \'block\');
            var vehicle_type = jQuery(\'#vehicle_type\').val();
            //alert(vehicle_type);
            var vehicle_reg_no = jQuery(\'#vehicle_reg_no\').val();
            var user_id = jQuery(\'#user_id\').val();
            var invoice_id = jQuery(\'#last_invoice_id\').val();
            var amount = 100;
            jQuery.ajax({
                url: "'.get_bloginfo('url').'/wp-admin/admin-ajax.php",
                type: \'POST\',
                data: \'action=addvehicle_nagad&vehicle_reg_no=\' + vehicle_reg_no + \'&user_id=\' + user_id + \'&invoice_id=\' + invoice_id + \'&vehicle_type=\' + vehicle_type+ \'&amount=\' + amount,
                success: function (results) {
                    //var url = results.slice(0, -1);
                    //alert(results);
                    window.location.href = results;
                }
            });
        }


        function go_vehicle(){
            var vehicle_type = jQuery(\'#vehicle_type\').val();
            //alert(vehicle_type);
            var vehicle_reg_no = jQuery(\'#vehicle_reg_no\').val();
            var user_id = jQuery(\'#user_id\').val();
            //alert(user_id);
            if(vehicle_type == \'\'){
                alert(\'Please enter vehicle type\');
            }
            else if(vehicle_reg_no == \'\'){
                alert(\'Please enter vehicle reg. no\');
            }
            else
            {
                jQuery(\'#vehicle_form\').css(\'display\', \'none\');
                add_vehicle(\'\');
            }

        }
    </script>
	<script src="//code.jquery.com/jquery-1.8.3.min.js"></script>
	<script src="https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
	<script type="text/javascript">
        $(document).ready(function () {
            $( "#main" ).removeClass( "all_colors" ).addClass( "all_colors myAccClass" );
            //Token
            //console.log("I\'m in");
			//alert(\'<?php echo admin_url(\'admin-ajax.php\'); ?>\');
			//alert(bkash_sc.ajax_url);
			$.post("'.admin_url('admin-ajax.php').'", { action: \'bkash_token\' }, function(output) {
				//location.reload();
				output = output.substring(0, output.length - 1);
			});
			
			var paymentRequest;
			paymentRequest = {  };


			bKash.init({

				paymentMode: \'checkout\',

				paymentRequest: paymentRequest,

				createRequest: function (request) {

					console.log(\'=> createRequest (request) :: \');
					//console.log(request);
					paymentRequest.amount = 100;
					//paymentRequest.amount = 1; 
					paymentRequest.invoice = $(\'#last_invoice_id\').val();
					$.post("'.admin_url('admin-ajax.php').'", { action: \'bkash_createpayment\', amount: paymentRequest.amount, invoice: paymentRequest.invoice }, function(output) {
						//location.reload();
						//console.log(output);
						output = output.substring(0, output.length - 1);
						//alert(output);
	                    data = JSON.parse(output);
						console.log(\'got data from create  ..\');
						//alert(data.paymentID);
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

					console.log(\'=> executeRequestOnAuthorization\');
					$.post("'.admin_url('admin-ajax.php').'", { action: \'bkash_executepayment\', paymentID: paymentID }, function(output) {
						//location.reload();
						output = output.substring(0, output.length - 1);
                                                //alert(output);
						data = JSON.parse(output);
						//console.log(\'got data from execute  ..\');
						//alert("Transaction ID :" + data.trxID);
						if (data && data.paymentID != null) {
        					//$.post("'.admin_url('admin-ajax.php').'", { action: \'bkash_querypayment\', paymentID: paymentID }, function(output) {
						//	output = output.substring(0, output.length - 1);
                                                  //      alert(output);
						    //});
        					//$.post("'.admin_url('admin-ajax.php').'", { action: \'bkash_paymentdetails\', trxID: trxID }, function(output) {
						//	output = output.substring(0, output.length - 1);
                                                  //      alert(output);
						    //});
						    /*var trip_no = $(\'#trip_no\').val();;
						    var user_id = "'.get_current_user_id().'";
						    var user_info_id = document.getElementById("user_info_id").value;
						    var invoice_id = data.merchantInvoiceNumber;
                            $.post("'.admin_url('admin-ajax.php').'", { action: \'add_trip\', trip_no: trip_no, total_cost: data.amount, user_id: user_id, user_info_id: user_info_id, invoice_id: invoice_id}, function(output) {
                                //alert(trip_no);
                                //location.reload();
                                //window.location.href = "'.get_bloginfo('url').'/my-account/";
                                window.location.href = "'.get_bloginfo('url').'/invoice/?data="+btoa(invoice_id);
                            });*/
                            Swal.fire("", "Successfully paid", "success",{
                                closeOnClickOutside: false,
                                closeOnEsc: false,
                            });
						    $(\'#last_invoice_id\').val(data.merchantInvoiceNumber);
						    add_vehicle(data.trxID);
						} else {
							bKash.execute().onError();
                            Swal.fire("Payment Failed", data.errorMessage, "error",{
                                closeOnClickOutside: false,
                                closeOnEsc: false,
                            });
							//alert("error with "+ data.errorMessage);
						}
					});
				},
                onClose : function () {
                    location.reload();
                }
			});

		});
    
    </script>
    ';
    ?>
    <script>
        function add_vehicle(pgw_transaction_id){
            var vehicle_type = jQuery('#vehicle_type').val();
            //alert(vehicle_type);
            var vehicle_reg_no = jQuery('#vehicle_reg_no').val();
            var user_id = jQuery('#user_id').val();
            var invoice_id = jQuery('#last_invoice_id').val();
            //alert(user_id);
            /*if(vehicle_type == ''){
                alert('Please enter vehicle type');
            }
            else if(vehicle_reg_no == ''){
                alert('Please enter vehicle reg. no');
            }
            else
            {*/
            jQuery('#bkash_form').css('display', 'none');
            jQuery('#loading').css('display', 'block');
            jQuery.ajax({
                url: "<?php echo get_bloginfo('url');?>/wp-admin/admin-ajax.php",
                type: 'POST',
                data: 'action=vehicle_add&vehicle_type=' + vehicle_type + '&vehicle_reg_no=' + vehicle_reg_no + '&user_id=' + user_id + '&invoice_id=' + invoice_id + '&pgw_transaction_id=' + pgw_transaction_id,
                success: function (results) {
                    jQuery('#loading').html('Added Successfully...');
                }
                //return false;
            });
            //}

        }
    </script>

    <?php
}
add_action( 'woocommerce_account_add-vehicle_endpoint', 'add_vehicle_endpoint_content' );

add_action( 'wp_ajax_nopriv_vehicle_add', 'vehicle_add' );
add_action( 'wp_ajax_vehicle_add', 'vehicle_add' );
function vehicle_add()
{
    global $wpdb;
    $vehicle_type = $wpdb->escape($_POST['vehicle_type']);
    $user_id = $wpdb->escape($_POST['user_id']);
    $vehicle_reg_no = $wpdb->escape($_POST['vehicle_reg_no']);
    $invoice_id = $wpdb->escape($_POST['invoice_id']);
    $pgw_transaction_id = $wpdb->escape($_POST['pgw_transaction_id']);
    $info_table_name = $wpdb->prefix . "users_info";
    $info_insert_data = $wpdb->insert(
        $info_table_name,
        array(
            'user_id' => $user_id,
            'vehicle_type' => $vehicle_type,
            'vehicle_reg_no' => $vehicle_reg_no,
            //'process_amount' => 100,
            'created_by' => $user_id,
            'date_inserted' => date('Y-m-d H:i:s')
        ),
        array(
            '%d',
            '%s',
            '%s',
            //'%d',
            '%d',
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
            'invoice_id' => $invoice_id,
            'process_amount' => 100,
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
}


?>
