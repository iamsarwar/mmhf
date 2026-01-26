<?php
/* 
Template Name: UpayReturn
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

                                    <div id="loading" style="display:none;">
                                        <center><img src="<?php echo get_bloginfo('url');?>/wp-content/uploads/2019/12/charging.gif"></center>
                                    </div>

	<?php
    session_start();
    //var_dump($_SESSION);
    //echo $_SESSION['upay_token'];
    //echo '<br>';
    //echo $_COOKIE['upay_token'];
    //echo '<br>';
    //$trip_data = json_decode(stripslashes($_COOKIE['mmhftrip_data']));
    //echo '<pre>';
    //var_dump($reg_data);
    //echo '</pre>';
    //echo $reg_data->user_email;
    //echo $_COOKIE['mmhfreg_data'];
    //var_dump($_REQUEST);
    //exit;
	global $wpdb, $PasswordHash, $current_user, $user_ID;
    
    $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
    $bank_tokens_retrieve_data = $wpdb->get_row("SELECT * FROM $bank_tokens_table_name WHERE bank_name = 'upay' AND bank_token = '".$_SESSION['upay_token']."'");
    
    if (!empty($_REQUEST['status']) && $_REQUEST['status'] == 'successful') {
        $header = array(
            'Content-Type:application/json',
            'Authorization:UPAY ' . $_SESSION['upay_token']
        );
    
        $init_ch = curl_init();
        curl_setopt($init_ch, CURLOPT_URL, "https://pg.upaysystem.com/payment/single-payment-status/".$_REQUEST['invoice_id']."/");
        curl_setopt($init_ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($init_ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($init_ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($init_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($init_ch, CURLOPT_HTTPHEADER, $header);
        
        $upay_init_payment_result = curl_exec($init_ch);
        $upay_init_payment_result = json_decode($upay_init_payment_result);
        curl_close($init_ch);
        $return_var = $upay_init_payment_result->data;
        if(!empty($return_var) && $return_var->status == 'success'){
            $bank_desc = $paymentRefId;
            if(trim($bank_tokens_retrieve_data->payment_for) == 'new user registration'){
                //echo 'Success';
                $reg_data = json_decode(stripslashes($bank_tokens_retrieve_data->user_data));
                $password1 = $wpdb->escape($reg_data->password1);
                $first_name = $wpdb->escape($reg_data->f_name);
                $v_account_type = $wpdb->escape($reg_data->account_type);
                $username = $wpdb->escape($reg_data->mobile_no);
                $useremail = $wpdb->escape($reg_data->user_email);
                $vehicle_reg_no = $wpdb->escape($reg_data->vehicle_reg_no);
                $vehicle_type = $wpdb->escape($reg_data->vehicle_type);
                $nid = $wpdb->escape($reg_data->nid);
                $address = $wpdb->escape($reg_data->address);
                $auth_person_name = $wpdb->escape($reg_data->auth_person_name);      
        
                $user_id = wp_insert_user( array ('first_name' => apply_filters('pre_user_first_name', $first_name), 'user_pass' => apply_filters('pre_user_user_pass', $password1), 'user_login' => apply_filters('pre_user_user_login', $username), 'user_email' => apply_filters('pre_user_user_email', $useremail), 'role' => 'subscriber' ) );
                if( is_wp_error($user_id) ) {
                    $error= 'Error on user creation.';
                } else {
                    do_action('user_register', $user_id);
                    $user_table_name = $wpdb->prefix . "users";
                    $user_update_data = $wpdb->update($user_table_name, array('id'=>$user_id, 'v_account_type'=>$v_account_type, 'auth_person_name'=>$auth_person_name, 'user_address'=>$address), array('id'=>$user_id));

                    $process_fees_table_name = $wpdb->prefix . "user_process_fees";
                    $process_fees_retrieve_data = $wpdb->get_row("SELECT MAX(id) AS last_id FROM $process_fees_table_name");
                    if(!empty($process_fees_retrieve_data->last_id)){
                        $last_invoice_id = $user_id.date('Ym').($process_fees_retrieve_data->last_id+1).date('his');
                    }
                    else
                        $last_invoice_id = $user_id.date('Ym').'1'.date('his');
            
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
                            'pgw_name' => 'upay',
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

                    /*echo '
                        <div id="loading">
                            <center><img src="'.get_bloginfo('url').'/wp-content/uploads/2019/12/charging.gif"></center>
                        </div>
                    ';*/
                    $success = 'Successfully registered';
                    unset($_COOKIE['mmhfreg_data']);
                    $success_url = get_bloginfo('url').'/my-account';
                }
            }
            elseif(trim($bank_tokens_retrieve_data->payment_for) == 'add vehicle'){
                $vehicle_data = json_decode(stripslashes($bank_tokens_retrieve_data->user_data));
                //$vehicle_data = json_decode(stripslashes($bank_tokens_retrieve_data->user_data));
                //var_dump(vehicle_data);
                //exit;
                $vehicle_type = $wpdb->escape($vehicle_data->vehicle_type);
                $user_id = $wpdb->escape($vehicle_data->user_id);
                $vehicle_reg_no = $wpdb->escape($vehicle_data->vehicle_reg_no);
                $invoice_id = $wpdb->escape($vehicle_data->invoice_id);
                $tellamount = $wpdb->escape($vehicle_data->amount);

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
                        'pgw_name' => 'upay',
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

                /*echo '
                    <div id="loading">
                        <center><img src="' . get_bloginfo('url') . '/wp-content/uploads/2019/12/charging.gif"></center>
                    </div>
                ';*/
                $success = 'Successfully added vehicle.';
                unset($_COOKIE['mmhfvehicle_data']);
                $success_url = get_bloginfo('url').'/my-account/add-vehicle';
            }
            else{
                $trip_data = json_decode(stripslashes($bank_tokens_retrieve_data->user_data));
                $trip_no = $wpdb->escape($trip_data->trip_no);
                $extra_trip = $wpdb->escape($trip_data->extra_trip);
                $user_id = $wpdb->escape($trip_data->user_id);
                $gea_subscription_id = $wpdb->escape($trip_data->gea_subscription_id);
                $invoice_id = $wpdb->escape($trip_data->invoice_id);
                $vehicle_type = $wpdb->escape($trip_data->vehicle_type);
                $tellamount = $wpdb->escape($trip_data->amount);
                
                $bank_process_data = json_decode(stripslashes($bank_tokens_retrieve_data->bank_process_data));
                $pgw_transaction_id = $wpdb->escape($bank_process_data->trx_id);
                $pgw_session_id = $wpdb->escape($bank_process_data->session_id);
                $recharge_table_name = $wpdb->prefix . "users_recharge_histories";
                $recharge_retrieve_data = $wpdb->get_row("SELECT $recharge_table_name.id FROM $recharge_table_name WHERE $recharge_table_name.pgw_transaction_id = '$pgw_transaction_id'");

                if (empty($recharge_retrieve_data->id)) {
                    $info_table_name = $wpdb->prefix . "users_info";
                    $info_retrieve_data = $wpdb->get_row("SELECT $info_table_name.* FROM $info_table_name WHERE $info_table_name.gea_subscription_id = $gea_subscription_id");
                    $tariff_table_name = $wpdb->prefix . "tariff_rates";
                    $tariff_retrieve_data = $wpdb->get_row("SELECT $tariff_table_name.* FROM $tariff_table_name WHERE $tariff_table_name.vehicle_type = '$vehicle_type'");
                    $actual_trip_no = floor($total_cost / $tariff_retrieve_data->toll_rates);
                    $total_cost = $tariff_retrieve_data->toll_rates * $trip_no;
                    $packages = '';
                    if(!empty($extra_trip)){
                        $trip_no = $trip_no + $extra_trip;
                        $packages = 'package_'.($extra_trip / 2);
                    }

                    $recharge_insert_data = $wpdb->insert(
                        $recharge_table_name,
                        array(
                            'user_id' => $user_id,
                            'gea_subscription_id' => $gea_subscription_id,
                            'invoice_id' => $invoice_id,
                            'pgw_name' => 'upay',
                            'pgw_transaction_id' => $pgw_transaction_id,
                            'pgw_payment_id' => $pgw_session_id,
                            'pgw_amount' => $tellamount,
                            'trips' => $trip_no,
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
                        $total_trip = $trip_no + $trip_retrieve_data->total_trip;
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

                    //send reload request to GEA
                    //$url ="http://182.160.116.91/subsrevise.php";
                    //$url = "http://192.168.174.251/subsrevise.php";
                    $url ="http://182.163.122.66/subsrevise.php";
                    //$info_retrieve_data->gea_customer_id.'<br>';
                    if(!empty($extra_trip) && !empty($tariff_retrieve_data->toll_rates)){
                        $tellamount = $tellamount + ($extra_trip * $tariff_retrieve_data->toll_rates);
                    }

                    $data = array
                    (
                        "ID_PRODUCT_TYPE" => $info_retrieve_data->gea_product_type_id,
                        "ID_PRODUCT" => $info_retrieve_data->gea_product_id,
                        "AMOUNT" => $tellamount,
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

                /*echo '
                    <div id="loading">
                        <center><img src="' . get_bloginfo('url') . '/wp-content/uploads/2019/12/charging.gif"></center>
                    </div>
                ';*/
                $success = 'Successfully added trip.';
                unset($_COOKIE['mmhftrip_data']);
                $success_url = get_bloginfo('url').'/my-account/add-trip';
            }

            $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
            $bank_tokens_update_data = $wpdb->update($bank_tokens_table_name, array('process_status'=>'success', 'bank_data'=>$bank_desc,'modified_by' => $user_id, 'modified_at' => date('Y-m-d H:i:s')), array('bank_token'=>$_SESSION['upay_token']));
            echo '<meta http-equiv="refresh" content="0;url='.$success_url.'/?msg='.$success.'" />';
        } else {
            /*echo '
                    <div id="loading">
                        <center><img src="'.get_bloginfo('url').'/wp-content/uploads/2019/12/charging.gif"></center>
                    </div>
                ';*/
            $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
            $bank_tokens_update_data = $wpdb->update($bank_tokens_table_name, array('process_status'=>'failed', 'modified_by' => $user_id, 'modified_at' => date('Y-m-d H:i:s')), array('bank_token'=>$_SESSION['upay_token']));
            echo '<meta http-equiv="refresh" content="0;url='.get_bloginfo('url').'/my-account/?msg=Payment process wrong or declined, can not be processed." />';
        }
    } else {
        /*echo '
                <div id="loading">
                    <center><img src="'.get_bloginfo('url').'/wp-content/uploads/2019/12/charging.gif"></center>
                </div>
            ';*/
        $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
        $bank_tokens_update_data = $wpdb->update($bank_tokens_table_name, array('process_status'=>'failed', 'modified_by' => $user_id, 'modified_at' => date('Y-m-d H:i:s')), array('bank_token'=>$_SESSION['upay_token']));
        echo '<meta http-equiv="refresh" content="0;url='.get_bloginfo('url').'/my-account/?msg=Payment process wrong or declined, can not be processed." />';
    }
?>
    <script src="//code.jquery.com/jquery-1.8.3.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#main").removeClass("all_colors").addClass("all_colors myAccClass");
        });
    </script>

<?php get_footer() ?>



