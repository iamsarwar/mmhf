<?php
/* 
Template Name: DBBLCallback
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

        function getresultRS($trans_id, $clientIp){
            $userid = '000599992510000';
            $pwd    = '$2a$10$fW4dQ945hQDWZBv45XFcj.DqucgjrVHdZ3TTWNSzaLAqvvAllCi7C'; //-- UAT
            $checksum = 'ORINfrad251';  // -- UAT
            $dbblurl = "https://ecom1.dutchbanglabank.com"; // --- Test Server ---//
            $ecomrsUrl = $dbblurl.'/ecomrs/rsvr/ecomtxn/getresult';
            
            $dbbl_data  = array(
                'userid' => $userid,
                'passwd' => $pwd,
                'transid' => $trans_id,
                'clientip' => $clientIp
            
            );
            $payload = json_encode($dbbl_data);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $ecomrsUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        
            // Execute the POST request
            $result = curl_exec($ch);
            $dbblresult = json_decode($result, false);

            if($dbblresult->SIGNATURE == hash_hmac('sha256',$trans_id.$dbblresult->RESULT_CODE.$dbblresult->RRN,$checksum)){
                $dbblresult->SIGNVERIFY = "Y";
            }else{
                $dbblresult->SIGNVERIFY = "N";
            }

            return $dbblresult;
        }

		if(isset($_POST['trans_id']) && !empty($_POST['trans_id'])) {
			$trans_id = $_POST['trans_id'];
		} else if(isset($_SESSION['DBBL_TRANSACTION_ID']) && !empty($_SESSION['DBBL_TRANSACTION_ID'])) {
			$trans_id = $_SESSION['DBBL_TRANSACTION_ID'];
		} else if(isset($_COOKIE['trans_id']) && !empty($_COOKIE['trans_id'])) {
			$trans_id = $_COOKIE['trans_id'];
		} else{
			$trans_id = "N/A";
		}

        if($trans_id == "N/A") { 
			$errmsg =  "Transaction ID missing";
            echo '
                <div id="loading">
                    <center><img src="'.get_bloginfo('url').'/wp-content/uploads/2019/12/charging.gif"></center>
                </div>
            ';
            unset($_COOKIE['mmhfreg_data']);
            $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
            $bank_tokens_update_data = $wpdb->update($bank_tokens_table_name, array('process_status'=>'failed', 'modified_at' => date('Y-m-d H:i:s')), array('bank_token'=>$_SESSION['DBBL_TRANSACTION_ID']));
            echo '<meta http-equiv="refresh" content="0;url='.get_bloginfo('url').'/registration/?msg=Payment process wrong or declined, can not be registered." />';
        } else {
			$clientIp = '192.144.80.231';
			$dbblresult = getresultRS($trans_id, $clientIp); 

            //echo '<pre>';
            //print_r($dbblresult);
            //echo '</pre>';
            //echo '<br>'.$dbblresult->RESULT_CODE;

            if($dbblresult->RESULT_CODE == '000' & $dbblresult->RESULT == 'OK'){
                $bank_desc = $dbblresult->CARDNAME . '|' . $dbblresult->CARD_NUMBER . '|' . $dbblresult->DESCRIPTION . '|' . $dbblresult->RRN . '|' . $dbblresult->SIGNATURE . '|' . $dbblresult->TRANSACTION_ID;
                
                $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
                $bank_tokens_retrieve_data = $wpdb->get_row("SELECT * FROM $bank_tokens_table_name WHERE bank_token = '".$dbblresult->TRANSACTION_ID."'");
                
                $pgw_name = $bank_tokens_retrieve_data->bank_name;
                $pgw_transaction_id = $dbblresult->TRANSACTION_ID;

                if(trim($bank_tokens_retrieve_data->payment_for) == 'new user registration'){
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

                    $process_fees_table_name = $wpdb->prefix . "user_process_fees";
                    $process_fees_retrieve_data = $wpdb->get_row("SELECT MAX(id) AS last_id FROM $process_fees_table_name");
                    if(!empty($process_fees_retrieve_data->last_id)){
                        $last_invoice_id = $user_id.date('Ym').($process_fees_retrieve_data->last_id+1).date('his');
                    }
                    else
                        $last_invoice_id = $user_id.date('Ym').'1'.date('his');
            
                    //echo 'Success';
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
                                'pgw_name' => $pgw_name,
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

                        echo '
                            <div id="loading">
                                <center><img src="'.get_bloginfo('url').'/wp-content/uploads/2019/12/charging.gif"></center>
                            </div>
                        ';
                        $success = 'Successfully registered';
                        unset($_COOKIE['mmhfreg_data']);
                        $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
                        $bank_tokens_update_data = $wpdb->update($bank_tokens_table_name, array('process_status'=>'success', 'bank_data'=>$bank_desc,'modified_by' => $user_id, 'modified_at' => date('Y-m-d H:i:s')), array('bank_token'=>$dbblresult->TRANSACTION_ID));
                        echo '<meta http-equiv="refresh" content="0;url='.get_bloginfo('url').'/my-account/?msg='.$success.'" />';
                    }
                }
                else if(trim($bank_tokens_retrieve_data->payment_for) == 'add vehicle'){
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
                            'pgw_name' => $pgw_name,
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

                    echo '
                        <div id="loading">
                            <center><img src="' . get_bloginfo('url') . '/wp-content/uploads/2019/12/charging.gif"></center>
                        </div>
                    ';
                    $success = 'Successfully added vehicle.';
                    unset($_COOKIE['mmhfvehicle_data']);
                    $success_url = get_bloginfo('url').'/my-account/add-vehicle';
                    $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
                    $bank_tokens_update_data = $wpdb->update($bank_tokens_table_name, array('process_status'=>'success', 'bank_data'=>$bank_desc,'modified_by' => $user_id, 'modified_at' => date('Y-m-d H:i:s')), array('bank_token'=>$dbblresult->TRANSACTION_ID));
                    echo '<meta http-equiv="refresh" content="0;url='.$success_url.'/?msg='.$success.'" />';
                }
                else {
                    $trip_data = json_decode(stripslashes($bank_tokens_retrieve_data->user_data));
                    $trip_no = $wpdb->escape($trip_data->trip_no);
                    $extra_trip = $wpdb->escape($trip_data->extra_trip);
                    $user_id = $wpdb->escape($trip_data->user_id);
                    $gea_subscription_id = $wpdb->escape($trip_data->gea_subscription_id);
                    $invoice_id = $wpdb->escape($trip_data->invoice_id);
                    $vehicle_type = $wpdb->escape($trip_data->vehicle_type);
                    $tellamount = $wpdb->escape($trip_data->amount);

                    $recharge_table_name = $wpdb->prefix . "users_recharge_histories";
                    $recharge_retrieve_data = $wpdb->get_row("SELECT $recharge_table_name.id FROM $recharge_table_name WHERE $recharge_table_name.pgw_transaction_id = '$pgw_transaction_id'");
                    //echo 'ASI';
                    if (empty($recharge_retrieve_data->id)) {
                        $info_table_name = $wpdb->prefix . "users_info";
                        $info_retrieve_data = $wpdb->get_row("SELECT $info_table_name.* FROM $info_table_name WHERE $info_table_name.gea_subscription_id = $gea_subscription_id");
                        $tariff_table_name = $wpdb->prefix . "tariff_rates";
                        $tariff_retrieve_data = $wpdb->get_row("SELECT $tariff_table_name.* FROM $tariff_table_name WHERE $tariff_table_name.vehicle_type = '$vehicle_type'");
                        //$actual_trip_no = floor($total_cost / $tariff_retrieve_data->toll_rates);
                        $total_cost = $tariff_retrieve_data->toll_rates * $trip_no;
			            $actual_trip_no = floor($total_cost / $tariff_retrieve_data->toll_rates);
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
                                'pgw_name' => $pgw_name,
                                'pgw_transaction_id' => $pgw_transaction_id,
                                'pgw_payment_id' => $trans_id,
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

                    echo '
                        <div id="loading">
                            <center><img src="' . get_bloginfo('url') . '/wp-content/uploads/2019/12/charging.gif"></center>
                        </div>
                    ';
                    $success = 'Successfully added trip.';
                    unset($_COOKIE['mmhftrip_data']);
                    $success_url = get_bloginfo('url').'/my-account/add-trip';
                    $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
                    $bank_tokens_update_data = $wpdb->update($bank_tokens_table_name, array('process_status'=>'success', 'bank_data'=>$bank_desc,'modified_by' => $user_id, 'modified_at' => date('Y-m-d H:i:s')), array('bank_token'=>$dbblresult->TRANSACTION_ID));
                    echo '<meta http-equiv="refresh" content="0;url='.$success_url.'/?msg='.$success.'" />';
                }
            } else {
                echo '
                        <div id="loading">
                            <center><img src="'.get_bloginfo('url').'/wp-content/uploads/2019/12/charging.gif"></center>
                        </div>
                    ';
                unset($_COOKIE['mmhfreg_data']);
                $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
                $bank_tokens_update_data = $wpdb->update($bank_tokens_table_name, array('process_status'=>'failed', 'modified_at' => date('Y-m-d H:i:s')), array('bank_token'=>$dbblresult->TRANSACTION_ID));
                echo '<meta http-equiv="refresh" content="0;url='.get_bloginfo('url').'/my-account/?msg=Payment process wrong or declined, can not be processed." />';
                //echo '<meta http-equiv="refresh" content="0;url='.get_bloginfo('url').'/registration/?msg=Payment process wrong or declined, can not be registered." />';
            }
        }


?>
    <script src="//code.jquery.com/jquery-1.8.3.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#main").removeClass("all_colors").addClass("all_colors myAccClass");
        });
    </script>

<?php get_footer() ?>


