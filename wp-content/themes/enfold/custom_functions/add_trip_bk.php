<?php


function my_custom_endpoint_content() {
    global $wpdb;
    session_start();
    $_SESSION['process_fee'] = 0;

    echo '<h2>Add Trip</h2>';
    $user_table_name = $wpdb->prefix . "users";
    $user_retrieve_data = $wpdb->get_row( "SELECT $user_table_name.* FROM $user_table_name WHERE $user_table_name.ID = ".get_current_user_id() );
    $info_table_name = $wpdb->prefix . "users_info";
    $rate_table_name = $wpdb->prefix . "tariff_rates";
    $info_retrieve_data = $wpdb->get_results( "SELECT DISTINCT $info_table_name.gea_subscription_id, $info_table_name.vehicle_type, $rate_table_name.toll_rates FROM $info_table_name LEFT JOIN $rate_table_name ON $rate_table_name.vehicle_type = $info_table_name.vehicle_type WHERE $info_table_name.user_id = ".get_current_user_id()." AND $info_table_name.installed = 1" );
    //var_dump($info_retrieve_data);
    if(!empty($info_retrieve_data)){
        $is_corporate = '';
        /*if($user_retrieve_data->v_account_type == 'Corporate') {
            $is_corporate = "style='display:none;'";
            echo '
            ';
        }*/
        //$user_retrieve_data->v_account_type;
        ?>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
        <script type="text/javascript">
            $.extend( $.fn.dataTable.defaults, {
                responsive: true
            } );

            $(document).ready(function() {
                $( "#main" ).removeClass( "all_colors" ).addClass( "all_colors myAccClass" );
                $( "#vehicle_table" ).DataTable();
                $( "#package_table" ).DataTable({
                    "pageLength": 4,
                    "lengthMenu": [
                                    [4, 5, -1],
                                    [4, 5, 'All']
                                ]
                });
            } );
        </script>

        <?php
        if(!empty($_REQUEST['msg']))
            echo '<h3>'.$_REQUEST['msg'].'</h3>';

        echo '<table width="100%" class="display" id="vehicle_table">
                <thead>
                    <tr>
                        <th>Subscription ID</th>
                        <th>Type</th>
                        <th>Reg. No`s</th>
                        <th>Total trip</th>
                        <th '.$is_corporate.'>Action</th>
                    </tr>
                 </thead>
                 <tbody>
                ';
        foreach ($info_retrieve_data as $thisData){
            //$installed = ($thisData->installed == 1)?'Yes':'Processing';
            $total_trip = 0;
            //$info_id = $thisData->id;
            $reg_retrieve_data = $wpdb->get_row( "SELECT GROUP_CONCAT(vehicle_reg_no SEPARATOR ', ') AS vehicle_reg_no FROM $info_table_name WHERE gea_subscription_id = '".$thisData->gea_subscription_id."' GROUP BY 'all'" );

            /*if(!empty($thisData->parent_id))
                $info_id = $thisData->parent_info_id;*/

            $trip_table_name = $wpdb->prefix . "users_trips";
            $trip_retrieve_data = $wpdb->get_row( "SELECT $trip_table_name.* FROM $trip_table_name WHERE $trip_table_name.user_id = ".get_current_user_id()." AND $trip_table_name.gea_subscription_id = ".$thisData->gea_subscription_id);
            if(!empty($trip_retrieve_data->id))
                $total_trip = $trip_retrieve_data->total_trip;

            echo '
                <tr>
                    <td>'.$thisData->gea_subscription_id.'</td>
                    <td>'.$thisData->vehicle_type.'</td>
                    <td>'.$reg_retrieve_data->vehicle_reg_no.'</td>
                    <td>'.$total_trip.'</td>
                    <td '.$is_corporate.'><button name="btnAdd" onclick="show_trip_form(\''.$thisData->toll_rates.'\',\''.$thisData->gea_subscription_id.'\',\''.$thisData->vehicle_type.'\',\''.$user_retrieve_data->user_login.'\');" class="button">Add Trip</button> </td>
                </tr>
            ';
        }

        echo '
                </tbody>
                <tfoot>
                    <tr>
                        <th>Subscription ID</th>
                        <th>Type</th>
                        <th>Reg. No`s</th>
                        <th>Total trip</th>
                        <th '.$is_corporate.'>Action</th>
                    </tr>
                 </tfoot>
            </table>';
    }

    ?>
    <div id="loading" style="display:none;">
        <center><img src="<?php echo get_bloginfo('url');?>/wp-content/uploads/2019/12/charging.gif"></center>
    </div>
    <form id="trip_form" name="trip_form" class="woocomerce-form woocommerce-form-login login" method="post" style="display:none;min-height:600px;">
        <h2>10% Bonus Trip</h2>
        <table width="100%" class="table table-striped table-bordered text-center" id="package_table">
            <thead>
                <tr>
                    <th>Package Name</th>
                    <th>Purchase Trip</th>
                    <th>Bonus Trip</th>
                    <th>Total Trip</th>
                    <th>Grab</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Package_01</td>
                    <td>23</td>
                    <td>2</td>
                    <td>25</td>
                    <td><button type="button" class="button" onclick="grab_package(23,2)">Grab Package</button></td>
                </tr>
                <tr>
                    <td>Package_02</td>
                    <td>46</td>
                    <td>4</td>
                    <td>50</td>
                    <td><button type="button" class="button" onclick="grab_package(46,4)">Grab Package</button></td>
                </tr>
                <tr>
                    <td>Package_03</td>
                    <td>69</td>
                    <td>6</td>
                    <td>75</td>
                    <td><button type="button" class="button" onclick="grab_package(69,6)">Grab Package</button></td>
                </tr>
                <tr>
                    <td>Package_04</td>
                    <td>92</td>
                    <td>8</td>
                    <td>100</td>
                    <td><button type="button" class="button" onclick="grab_package(92,8)">Grab Package</button></td>
                </tr>
                <tr>
                    <td>Package_05</td>
                    <td>115</td>
                    <td>10</td>
                    <td>125</td>
                    <td><button type="button" class="button" onclick="grab_package(115,10)">Grab Package</button></td>
                </tr>
                <tr>
                    <td>Package_06</td>
                    <td>138</td>
                    <td>12</td>
                    <td>150</td>
                    <td><button type="button" class="button" onclick="grab_package(138,12)">Grab Package</button></td>
                </tr>
                <tr>
                    <td>Package_07</td>
                    <td>161</td>
                    <td>14</td>
                    <td>175</td>
                    <td><button type="button" class="button" onclick="grab_package(161,14)">Grab Package</button></td>
                </tr>
                <tr>
                    <td>Package_08</td>
                    <td>184</td>
                    <td>16</td>
                    <td>200</td>
                    <td><button type="button" class="button" onclick="grab_package(184,16)">Grab Package</button></td>
                </tr>
                <tr>
                    <td>Package_09</td>
                    <td>207</td>
                    <td>18</td>
                    <td>225</td>
                    <td><button type="button" class="button" onclick="grab_package(207,18)">Grab Package</button></td>
                </tr>
                <tr>
                    <td>Package_10</td>
                    <td>230</td>
                    <td>20</td>
                    <td>250</td>
                    <td><button type="button" class="button" onclick="grab_package(230,20)">Grab Package</button></td>
                </tr>
            </tbody>
        </table>
        <div class="row" id="add_trip_now">
            <div class="col-md-6">
                <h3>Add Trip now.</h3>
            </div>
            <div class="col-md-6">
                <button type="button" class="button" onclick="window.location.reload()">Back to normal</button>
            </div>
        </div>
        <p><label for="vehicle_type">How many trips <span class="required" title="required" style="color:red;">*</span></label></p>
        <p><select name="trip_no" class="select is_empty" id="trip_no" onchange="get_cost(this.value)">
                <?php
                for($i=1;$i<251;$i++){
                    echo '<option value="'.$i.'">'.$i.'</option>';
                }
                ?>
            </select>
        </p>
        <p><label>Total Cost <span class="required" title="required" style="color:red;">*</span></label></p>
        <p><input type="text" readonly value="0" name="total_cost" id="total_cost" required /></p>
        <input type="hidden" name="unique_rate" id="unique_rate">
        <input type="hidden" name="user_login" id="user_login">
        <input type="hidden" name="vehicle_type" id="vehicle_type">
        <input type="hidden" name="extra_trip" id="extra_trip">
        <input type="hidden" name="gea_subscription_id" id="gea_subscription_id">
        <input type="hidden" name="last_invoice_id" id="last_invoice_id" value="<?php echo generateRechargeInvoiceID();?>">
        <p style="border: 1px solid #0c73b8; padding: 1%;font-weight: bold;width: 20%;">Payment Method</p>
        <div class="row">
            <div class="col-md-4">
                <p><input type="radio" name="pgw_type" id="bkash_pgw" onclick="show_hide(this.id)" checked><img src="<?php echo get_bloginfo('url');?>/wp-content/uploads/2021/01/bKash-Logo-4color.png" width="120" style="margin-left:2%;"/></p>
                <p><input type="radio" name="pgw_type" id="city_pgw" onclick="show_hide(this.id)"><img src="<?php echo get_bloginfo('url');?>/wp-content/uploads/2021/03/city_logo.png" width="120" style="margin-left:2%;vertical-align: middle;"/></p>
            </div>
            <div class="col-md-4">
                <p><input type="radio" name="pgw_type" id="nagad_pgw" onclick="show_hide(this.id)"><img src="<?php echo get_bloginfo('url');?>/wp-content/uploads/2021/03/nagad.png" width="120" style="margin-left:2%;vertical-align: middle;"/></p>
                <p><input type="radio" name="pgw_type" id="rocket_pgw" onclick="show_hide(this.id)"><img src="<?php echo get_bloginfo('url');?>/wp-content/uploads/2021/03/dbbl-mb.png" width="60" style="margin-left:2%;vertical-align: middle;"/></p>
            </div>
            <div class="col-md-4">
                <p><input type="radio" name="pgw_type" id="nexus_pgw" onclick="show_hide(this.id)"><img src="<?php echo get_bloginfo('url');?>/wp-content/uploads/2021/03/nexuspay.png" width="50" style="margin-left:2%;vertical-align: middle;"/></p>
                <p><input type="radio" name="pgw_type" id="upay_pgw" onclick="show_hide(this.id)"><img src="<?php echo get_bloginfo('url');?>/wp-content/uploads/2021/03/upay.png" width="60" style="margin-left:2%;vertical-align: middle;"/></p>
            </div>
        </div>
        <button id="bKash_button" class="button" style="/*background:transparent !important; border: none !important; padding:0 !important;*/" type="button">Add</button>
        <button id="city_button" onclick="go_city()" class="button" style="display:none;" type="button">Add</button>
        <button id="nagad_button" onclick="go_nagad()" class="button" style="display:none;" type="button">Add</button>
        <button id="rocket_button" onclick="go_dbbl(6)" class="button" style="display:none;" type="button">Deposit</button>
        <button id="nexus_button" onclick="go_dbbl(7)" class="button" style="display:none;" type="button">Deposit</button>
        <button id="upay_button" onclick="go_upay()" class="button" style="display:none;" type="button">Deposit</button>
    </form>
    <div id="bkash_token"></div>
    <?php

    echo '
    <script>
        function grab_package(trip, extra_trip){
            var unique_rate = document.getElementById("unique_rate").value;
            var total_cost = parseInt(trip) * parseInt(unique_rate);
            document.getElementById("total_cost").value=total_cost;
            document.getElementById("extra_trip").value=extra_trip;
            var selective_trip_no = parseInt(trip) - 1;
            $(\'#trip_no option\').eq(selective_trip_no).prop(\'selected\', true);
            $("#add_trip_now").focus(() => {
                $("span").css("display", "inline").fadeOut(3000);
            });;
        }
        
        function show_hide(pgw_id) {
            if(pgw_id == \'city_pgw\'){
                document.getElementById(\'bKash_button\').style.display = \'none\';
                document.getElementById(\'city_button\').style.display = \'block\';
                document.getElementById(\'nagad_button\').style.display = \'none\';
                document.getElementById(\'rocket_button\').style.display = \'none\';
                document.getElementById(\'nexus_button\').style.display = \'none\';
                document.getElementById(\'upay_button\').style.display = \'none\';
            }
            else if(pgw_id == \'nagad_pgw\'){
                document.getElementById(\'bKash_button\').style.display = \'none\';
                document.getElementById(\'city_button\').style.display = \'none\';
                document.getElementById(\'nagad_button\').style.display = \'block\';
                document.getElementById(\'nexus_button\').style.display = \'none\';
                document.getElementById(\'nexus_button\').style.display = \'none\';
                document.getElementById(\'upay_button\').style.display = \'none\';
            }
            else if(pgw_id == \'rocket_pgw\'){
                document.getElementById(\'bKash_button\').style.display = \'none\';
                document.getElementById(\'city_button\').style.display = \'none\';
                document.getElementById(\'nagad_button\').style.display = \'none\';
                document.getElementById(\'rocket_button\').style.display = \'block\';
                document.getElementById(\'nexus_button\').style.display = \'none\';
                document.getElementById(\'upay_button\').style.display = \'none\';
            }
            else if(pgw_id == \'nexus_pgw\'){
                document.getElementById(\'bKash_button\').style.display = \'none\';
                document.getElementById(\'city_button\').style.display = \'none\';
                document.getElementById(\'nagad_button\').style.display = \'none\';
                document.getElementById(\'rocket_button\').style.display = \'none\';
                document.getElementById(\'nexus_button\').style.display = \'block\';
                document.getElementById(\'upay_button\').style.display = \'none\';
            }
            else if(pgw_id == \'upay_pgw\'){
                document.getElementById(\'bKash_button\').style.display = \'none\';
                document.getElementById(\'city_button\').style.display = \'none\';
                document.getElementById(\'nagad_button\').style.display = \'none\';
                document.getElementById(\'rocket_button\').style.display = \'none\';
                document.getElementById(\'nexus_button\').style.display = \'none\';
                document.getElementById(\'upay_button\').style.display = \'block\';
            }
            else{
                document.getElementById(\'bKash_button\').style.display = \'block\';
                document.getElementById(\'city_button\').style.display = \'none\';
                document.getElementById(\'nagad_button\').style.display = \'none\';
                document.getElementById(\'rocket_button\').style.display = \'none\';
                document.getElementById(\'nexus_button\').style.display = \'none\';
                document.getElementById(\'upay_button\').style.display = \'none\';
            }
        }

        function go_city(){
            jQuery(\'#trip_form\').css(\'display\', \'none\');
            jQuery(\'#loading\').css(\'display\', \'block\');
            var trip_no = $(\'#trip_no\').val();;
            var user_id = "'.get_current_user_id().'";
            var gea_subscription_id = document.getElementById("gea_subscription_id").value;
            var invoice_id = $(\'#last_invoice_id\').val();
            var vehicle_type = document.getElementById("vehicle_type").value;
            var amount = $(\'#total_cost\').val();
            jQuery.ajax({
                url: "'.get_bloginfo('url').'/wp-admin/admin-ajax.php",
                type: \'POST\',
                data: \'action=addtrip_city&trip_no=\' + trip_no + \'&user_id=\' + user_id + \'&gea_subscription_id=\' + gea_subscription_id + \'&invoice_id=\' + invoice_id + \'&vehicle_type=\' + vehicle_type+ \'&amount=\' + amount,
                success: function (results) {
                    var url = results.slice(0, -1);
                    //alert(results);
                    window.location.href = url;
                }
            });
        }

        function go_nagad(){
            jQuery(\'#trip_form\').css(\'display\', \'none\');
            jQuery(\'#loading\').css(\'display\', \'block\');
            var trip_no = $(\'#trip_no\').val();;
            var user_id = "'.get_current_user_id().'";
            var gea_subscription_id = document.getElementById("gea_subscription_id").value;
            var invoice_id = $(\'#last_invoice_id\').val();
            var vehicle_type = document.getElementById("vehicle_type").value;
            var amount = $(\'#total_cost\').val();
            jQuery.ajax({
                url: "'.get_bloginfo('url').'/wp-admin/admin-ajax.php",
                type: \'POST\',
                data: \'action=addtrip_nagad&trip_no=\' + trip_no + \'&user_id=\' + user_id + \'&gea_subscription_id=\' + gea_subscription_id + \'&invoice_id=\' + invoice_id + \'&vehicle_type=\' + vehicle_type+ \'&amount=\' + amount,
                success: function (results) {
                    //var url = results.slice(0, -1);
                    //alert(results);
                    window.location.href = results;
                }
            });
        }

        function go_dbbl(card_type){
            jQuery(\'#bkash_form\').css(\'display\', \'none\');
            jQuery(\'#loading\').css(\'display\', \'block\');
            var trip_no = $(\'#trip_no\').val();;
            var user_id = "'.get_current_user_id().'";
            var gea_subscription_id = document.getElementById("gea_subscription_id").value;
            var invoice_id = $(\'#last_invoice_id\').val();
            var vehicle_type = document.getElementById("vehicle_type").value;
            var amount = $(\'#total_cost\').val();
            jQuery.ajax({
                url: "'.get_bloginfo('url').'/wp-admin/admin-ajax.php",
                type: \'POST\',
                data: \'action=addtrip_dbbl&trip_no=\' + trip_no + \'&user_id=\' + user_id + \'&gea_subscription_id=\' + gea_subscription_id + \'&invoice_id=\' + invoice_id + \'&vehicle_type=\' + vehicle_type+ \'&amount=\' + amount + \'&card_type=\' + card_type,
                success: function (results) {
                    var url = results.slice(0, -1);
                    //alert(results);
                    //console.log(results);
                    window.location.href = url;
                }
            });
        }

        function go_upay(){
            jQuery(\'#trip_form\').css(\'display\', \'none\');
            jQuery(\'#loading\').css(\'display\', \'block\');
            var trip_no = $(\'#trip_no\').val();;
            var user_id = "'.get_current_user_id().'";
            var gea_subscription_id = document.getElementById("gea_subscription_id").value;
            var invoice_id = $(\'#last_invoice_id\').val();
            var vehicle_type = document.getElementById("vehicle_type").value;
            var amount = $(\'#total_cost\').val();
            var user_login = $(\'#user_login\').val();
            jQuery.ajax({
                url: "'.get_bloginfo('url').'/wp-admin/admin-ajax.php",
                type: \'POST\',
                data: \'action=addtrip_upay&trip_no=\' + trip_no + \'&user_id=\' + user_id + \'&gea_subscription_id=\' + gea_subscription_id + \'&invoice_id=\' + invoice_id + \'&vehicle_type=\' + vehicle_type + \'&amount=\' + amount + \'&user_login=\' + user_login,
                success: function (results) {
                    //var url = results.slice(0, -1);
                    //alert(results);
                    window.location.href = results;
                }
            });
        }

        function show_trip_form(toll_rate, info_id, vehicle_type, user_login){
            document.getElementById("vehicle_table").style.display="none";
            document.getElementById("trip_form").style.display="block";
            document.getElementById("total_cost").value=toll_rate;
            document.getElementById("unique_rate").value=toll_rate;
            document.getElementById("gea_subscription_id").value=info_id;
            document.getElementById("vehicle_type").value=vehicle_type;
            document.getElementById("user_login").value=user_login;
        }
        
        function get_cost(trip_no){
            var unique_rate = document.getElementById("unique_rate").value;
            var total_cost = parseInt(trip_no) * parseInt(unique_rate);
            document.getElementById("total_cost").value=total_cost;
        }
    </script>
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
				//alert(JSON.stringify(output));
				//$("#bkash_token").html(output);
                data = JSON.parse(output);
                //alert(data.id_token);
			});
			
			var paymentRequest;
			paymentRequest = {  };


			bKash.init({

				paymentMode: \'checkout\',

				paymentRequest: paymentRequest,

				createRequest: function (request) {

					console.log(\'=> createRequest (request) :: \');
					//console.log(request);
					paymentRequest.amount = $(\'#total_cost\').val();
					//paymentRequest.amount = 1; 
					paymentRequest.invoice = $(\'#last_invoice_id\').val();
					//paymentRequest.invoice = "78509834";
                    //alert(paymentRequest.invoice);
					$.post("'.admin_url('admin-ajax.php').'", { action: \'bkash_createpayment\', amount: paymentRequest.amount, invoice: paymentRequest.invoice }, function(output) {
						//location.reload();
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
                            Swal.fire("", "Successfully paid", "success",{
                                closeOnClickOutside: false,
                                closeOnEsc: false,
                            });
                            var trip_no = $(\'#trip_no\').val();;
                            var extra_trip = $(\'#extra_trip\').val();;
						    var user_id = "'.get_current_user_id().'";
						    var gea_subscription_id = document.getElementById("gea_subscription_id").value;
						    var invoice_id = $(\'#last_invoice_id\').val();
						    var vehicle_type = document.getElementById("vehicle_type").value;
						    var pgw_payment_id = data.paymentID;
						    var pgw_transaction_id = data.trxID;
                            $.post("'.admin_url('admin-ajax.php').'", { action: \'add_trip\', trip_no: trip_no, extra_trip: extra_trip, total_cost: data.amount, user_id: user_id, gea_subscription_id: gea_subscription_id, invoice_id: invoice_id, pgw_transaction_id: pgw_transaction_id, pgw_payment_id: pgw_payment_id, vehicle_type: vehicle_type}, function(output) {
                                //alert(trip_no);
                                //location.reload();
                                //window.location.href = "'.get_bloginfo('url').'/my-account/";
                                window.location.href = "'.get_bloginfo('url').'/invoice/?data="+btoa(invoice_id);
                            });
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

}
add_action( 'woocommerce_account_add-trip_endpoint', 'my_custom_endpoint_content' );


add_action( 'wp_ajax_nopriv_add_trip', 'add_trip' );
add_action( 'wp_ajax_add_trip', 'add_trip' );
function add_trip(){
    global $wpdb;
    $trip_no = $wpdb->escape($_POST['trip_no']);
    $user_id = $wpdb->escape($_POST['user_id']);
    $extra_trip = $wpdb->escape($_POST['extra_trip']);
    $vehicle_type = $wpdb->escape($_POST['vehicle_type']);
    $gea_subscription_id = $wpdb->escape($_POST['gea_subscription_id']);
    $invoice_id = $wpdb->escape($_POST['invoice_id']);
    $pgw_transaction_id = $wpdb->escape($_POST['pgw_transaction_id']);
    $pgw_payment_id = $wpdb->escape($_POST['pgw_payment_id']);
    $total_cost = $wpdb->escape($_POST['total_cost']);

    $recharge_table_name = $wpdb->prefix . "users_recharge_histories";
    $recharge_retrieve_data = $wpdb->get_row( "SELECT $recharge_table_name.id FROM $recharge_table_name WHERE $recharge_table_name.pgw_transaction_id = '$pgw_transaction_id'" );

    if(empty($recharge_retrieve_data->id)) {
        $info_table_name = $wpdb->prefix . "users_info";
        $info_retrieve_data = $wpdb->get_row("SELECT $info_table_name.* FROM $info_table_name WHERE $info_table_name.gea_subscription_id = $gea_subscription_id");
        $tariff_table_name = $wpdb->prefix . "tariff_rates";
        $tariff_retrieve_data = $wpdb->get_row("SELECT $tariff_table_name.* FROM $tariff_table_name WHERE $tariff_table_name.vehicle_type = '$vehicle_type'");
        $actual_trip_no = floor($total_cost / $tariff_retrieve_data->toll_rates);
        $packages = '';
        if(!empty($extra_trip)){
            $actual_trip_no = $actual_trip_no + $extra_trip;
            $packages = 'package_'.($extra_trip / 2);
        }

        $recharge_insert_data = $wpdb->insert(
            $recharge_table_name,
            array(
                'user_id' => $user_id,
                'gea_subscription_id' => $gea_subscription_id,
                'invoice_id' => $invoice_id,
                'pgw_name' => 'bkash',
                'pgw_transaction_id' => $pgw_transaction_id,
                'pgw_payment_id' => $pgw_payment_id,
                'pgw_amount' => $total_cost,
                'trips' => $actual_trip_no,
                'packages' => $packages,
                'created_by' => $user_id,
                //'date_inserted' => date('Y-m-d H:i:s')
            ),
            array(
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%d',
                '%d',
                '%s',
                '%d'
            )
        );
        $trip_table_name = $wpdb->prefix . "users_trips";
        $trip_retrieve_data = $wpdb->get_row("SELECT $trip_table_name.* FROM $trip_table_name WHERE $trip_table_name.user_id = " . $user_id . " AND $trip_table_name.gea_subscription_id=" . $gea_subscription_id);
        $total_trip = $actual_trip_no;
        if (!empty($trip_retrieve_data)) {
            $total_trip = $actual_trip_no + $trip_retrieve_data->total_trip;
            $trip_update_data = $wpdb->update($trip_table_name, array('id' => $trip_retrieve_data->id, 'total_trip' => $total_trip, 'updated_by' => get_current_user_id(), 'date_updated' => date('Y-m-d H:i:s')), array('id' => $trip_retrieve_data->id));
        } else {
            $trip_insert_data = $wpdb->insert(
                $trip_table_name,
                array(
                    'user_id' => $user_id,
                    'gea_subscription_id' => $gea_subscription_id,
                    'total_trip' => $total_trip,
                    'created_by' => $user_id,
                    'date_inserted' => date('Y-m-d H:i:s')
                ),
                array(
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                    '%s'
                )
            );
        }

        //send reload request to GEA 182.163.122.66
        //$url ="http://182.160.116.91/subsrevise.php";
        //$url = "http://192.168.174.251/subsrevise.php";
        $url ="http://182.163.122.66/subsrevise.php";
        //$info_retrieve_data->gea_customer_id.'<br>';
        if(!empty($extra_trip) && !empty($tariff_retrieve_data->toll_rates)){
            $total_cost = $total_cost + ($extra_trip * $tariff_retrieve_data->toll_rates);
        }

        $data = array
        (
            "ID_PRODUCT_TYPE" => $info_retrieve_data->gea_product_type_id,
            "ID_PRODUCT" => $info_retrieve_data->gea_product_id,
            "AMOUNT" => $total_cost,
            "VAT_AMOUNT" => $tariff_retrieve_data->vat_rate,
            "ID_CUSTOMER" => $info_retrieve_data->gea_customer_id,
            "ID_SUBSCRIPTION" => $info_retrieve_data->gea_subscription_id
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_COOKIEJAR, "my_cookies.txt");
        curl_setopt($ch, CURLOPT_COOKIEFILE, "my_cookies.txt");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3");
        $response = curl_exec($ch);
        curl_close($ch);
    }
    //return $response;*/
}

?>



