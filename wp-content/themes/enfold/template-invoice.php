<?php  
/* 
Template Name: Invoice
*/
 
    the_post();
    get_header();
?>
    <style>
        .invoice_header{ }
    </style>
<?php
    if(!empty(get_current_user_id()) && !empty($_REQUEST['data'])){
	if( !empty($_REQUEST['debug']) && $_REQUEST['debug'] == 'yes'  ){    
	    echo get_current_user_id();
            echo '<br>';
	    echo base64_decode($_REQUEST['data']);
	}

        $table_name = $wpdb->prefix . 'users_info';
        $user_table_name = $wpdb->prefix . 'users';
        $tariff_table_name = $wpdb->prefix . 'tariff_rates';
        $recharge_table_name = $wpdb->prefix . 'users_recharge_histories';
        $recharge_history_result = $wpdb->get_row("SELECT * FROM $recharge_table_name WHERE user_id='".get_current_user_id()."' AND invoice_id='".base64_decode($_REQUEST['data'])."'");
        if($_REQUEST['type'] == 'process'){
            $recharge_table_name = $wpdb->prefix . 'user_process_fees';
            $recharge_history_result = $wpdb->get_row("SELECT * FROM $recharge_table_name WHERE user_id='".get_current_user_id()."' AND invoice_id='".base64_decode($_REQUEST['data'])."'");
        }

	if( !empty($_REQUEST['debug']) && $_REQUEST['debug'] == 'yes'  ){
		echo '<br>' .  "SELECT * FROM $recharge_table_name WHERE user_id='".get_current_user_id()."' AND invoice_id='".base64_decode($_REQUEST['data'])."'";
	}

        if(!empty($recharge_history_result)) {
            $pgw_name = !empty($recharge_history_result->pgw_name)?$recharge_history_result->pgw_name:'Cash';
            $rest_query = "";
            if(!empty($recharge_history_result->user_info_id))
                $rest_query = " id=".$recharge_history_result->user_info_id;
            elseif(!empty($recharge_history_result->gea_subscription_id))
                $rest_query = " gea_subscription_id=".$recharge_history_result->gea_subscription_id;

            $info_result = $wpdb->get_row("SELECT * FROM $table_name WHERE user_id=".get_current_user_id()." AND ".$rest_query);
            $user_result = $wpdb->get_row("SELECT * FROM $user_table_name WHERE ID=".$recharge_history_result->user_id);
            $tariff_result = $wpdb->get_row("SELECT * FROM $tariff_table_name WHERE vehicle_type='".$info_result->vehicle_type."'");
            echo '
            <div style="margin:5%;" xmlns="http://www.w3.org/1999/html">
                <div style="invoice_header">
                    <div style="float:left;width:8%;">
                        <img src="' . get_bloginfo('url') . '/wp-content/uploads/2019/12/orion.png">
                    </div>
                    <div style="float:left;width:82%;">
                        <div style="text-align:center;color:#82a8fa;">
                            <h2>ORION INFRASTRUCTURE LTD.</h2>
                            <hr style="color:#000;margin:0;padding:0 0 1.5% 0;" />
                            <p style="color:#B37575;margin:0;padding:0;">Jatrabari-Gulistan Flyover</p>
                        </div>
                    </div>
                    <div style="float:right;width:8%;">
                        <img src="' . get_bloginfo('url') . '/wp-content/uploads/2020/01/orion_logo.jpg">
                    </div>
                </div>
                <hr />
                <div style="margin:0 5%;">
                    <div style="float:left;width:50%">
                        <p style="float:left;;font-weight:bold;padding-right: 1%;">Address: <br><br></p> 
                        <p>Orion House<br>153-154, Tejgaon Industrial Area<br>Dhaka-1208</p>   
                    </div>
                    <div style="float:left;width:50%">
                        <p style="margin:0;padding:1%;"><strong>Phone:</strong> (880961) 176-7676</p> 
                        <p style="margin:0;padding:0 1% 1% 1%;"><strong>Fax:</strong> (8802) 887-0130</p> 
                        <p style="margin:0;padding:0 1%;"><strong>Email:</strong> care.mmhf@orion-group.net</p> 
                    </div>
                </div>
                <div style="clear: both;"></div>
                <div style="margin:3% 0;background-color:#B1C5D7;">
                    <hr style="color:#000;margin:0;padding:0 0 1.5% 0;" />';
            if($_REQUEST['type'] == 'process')
                echo '<div align="center"><h2>PROCESSING FEES INVOICE</h2></div>';
            else
                echo '<div align="center"><h2>TRIP RECHARGE INVOICE</h2></div>';

            echo '
                    <hr style="margin:1.5% 0;" />
                </div>
                <div style="clear: both;"></div>
                <div style="margin:0 5%;">
                    <div style="float:left;width:50%">';

            if($_REQUEST['type'] == 'process')
                echo '<p style="margin:0;padding:1%;"><strong>Processing NO:</strong> '.$recharge_history_result->invoice_id.'</p>';
            else
                echo '<p style="margin:0;padding:1%;"><strong>Recharge NO:</strong> '.$recharge_history_result->invoice_id.'</p>';

            echo '
                        <p style="margin:0;padding:0 1% 1% 1%;"><strong>Date:</strong> '.date('Y-m-d', strtotime($recharge_history_result->date_inserted)).'</p> 
                        <p style="margin:0;padding:0 1% 1% 1%;"><strong>Customer ID:</strong> '.$info_result->gea_customer_id.'</p>';
                        if(!empty($recharge_history_result->pgw_transaction_id)){
                            echo '<p style="margin:0;padding:0 1% 1% 1%;"><strong>PGW Transaction ID:</strong> '.$recharge_history_result->pgw_transaction_id.'</p>';
                        }
                        echo '
                        <p style="margin:0;padding:0 1% 1% 1%;"><strong>Subscription ID:</strong> '.$info_result->gea_subscription_id.'</p> 
                        <p style="margin:0;padding:0 1%;"><strong>Vehicle Reg. No:</strong> '.$info_result->vehicle_reg_no.'</p> 
                    </div>
                    <div style="float:left;width:50%">
                        <p style="float:left;;font-weight:bold;padding-right: 1%;">Bill To: <br><br></p> 
                        <p>'.$user_result->display_name.'<br>'.$user_result->user_login.'<br>Dhaka-1208</p>   
                    </div>
                </div>
                <div style="clear: both;"></div>
                <div style="margin:2% 5%;">
                    <div style="float:left;width:60%;background-color:#4A9B82;color:#fff;">
                        <div style="padding-left:3%;">
                            <p style="margin:0;padding:1%;"><strong>Vehicle Class :</strong> '.$tariff_result->classes.'</p>';

            if($_REQUEST['type'] != 'process') {
                if(!empty($recharge_history_result->packages)){
                    $trip_package = explode('_', $recharge_history_result->packages);
                    echo '      <p style="margin:0;padding:0 1% 1% 1%;"><strong>Trip Purchase :</strong> ' . ($recharge_history_result->trips - ($trip_package[1] * 2)) . '</p>
                                <p style="margin:0;padding:0 1% 1% 1%;"><strong>Bonus Trip :</strong> ' . ($trip_package[1] * 2) . '</p>
                                <p style="margin:0;padding:0 1% 1% 1%;"><strong>Total Trip :</strong> ' . $recharge_history_result->trips . '</p>
                                <p style="margin:0;padding:0 1% 1% 1%;"><strong>Payment Amount (BDT) :</strong> ' . (($recharge_history_result->trips * $tariff_result->toll_rates) - ($trip_package[1] * 2 * $tariff_result->toll_rates)) . '</p>';
                }
                else{
                    echo '      <p style="margin:0;padding:0 1% 1% 1%;"><strong>Trip Purchase :</strong> ' . $recharge_history_result->trips . '</p>
                                <p style="margin:0;padding:0 1% 1% 1%;"><strong>Payment Amount (BDT) :</strong> ' . ($recharge_history_result->trips * $tariff_result->toll_rates) . '</p>';
                }
            }
            else
                echo '         <p style="margin:0;padding:0 1% 1% 1%;"><strong>Payment Amount (BDT) :</strong> ' . $recharge_history_result->process_amount . '</p>';

            echo '
                        </div>
                    </div>
                </div>
                <div style="clear: both;"></div>
                <div style="margin:4% 5%;">
                    <div style="float:right;width:60%;background-color:#9DCFE7;color:#000;">
                        <div style="padding-left:7%;">
                            <p style="margin:0;padding:1%;"><strong>PAYMENT METHOD:</strong> '. $pgw_name .'</p> 
                        </div>
                    </div>
                </div>
                <div style="clear: both;"></div>
                <div style="margin:2% 0;">
                    <div align="center">This is auto generated invoice, no signature required</div>
                </div>
            </div>
            ';
        }
        else
            echo '<br><br><center>No data found</center>';
    }

    get_footer();
?>

