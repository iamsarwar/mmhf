<?php
/* 
Template Name: CityTripReturn
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
    //echo wp_get_current_user();
    //if(!empty(get_current_user_id())) {
        session_start();
        //var_dump($_SESSION);
        //echo $_SESSION['mmhfreg_data'];
        //echo $_COOKIE['mmhfreg_data'];
        //$trip_data = json_decode(stripslashes($_COOKIE['mmhftrip_data']));
        //echo '<pre>';
        //var_dump($reg_data);
        //echo '</pre>';
        //echo $reg_data->user_email;
    
        global $wpdb;
        if (!empty($_REQUEST['xmlmsg'])) {
            $_REQUEST['xmlmsg'] = stripslashes($_REQUEST['xmlmsg']);
            $xmlResponse = simplexml_load_string($_REQUEST['xmlmsg']);
            $json = json_encode($xmlResponse);
            $return_var = json_decode($json, TRUE);
            //echo '<pre>';
            //print_r($return_var);
            //echo '</pre>';
            //exit;
            //echo 'ASI<br>'.$xmlResponse;
            $trans_id = !empty($return_var['OrderID'])?$return_var['OrderID']:$return_var['Message']['OrderID'];
            $session_id = !empty($return_var['SessionID'])?$return_var['SessionID']:$return_var['Message']['SessionID'];
            $pgw_transaction_id = $trans_id . '||' . $session_id;
            $total_cost = !empty($return_var['TotalAmountScr'])?$return_var['TotalAmountScr']:$return_var['Message']['TotalAmountScr'];
            
            $OrderStatus = !empty($return_var['OrderStatus'])?$return_var['OrderStatus']:$return_var['Message']['OrderStatus'];
            //exit;
            $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
            $bank_tokens_retrieve_data = $wpdb->get_row("SELECT * FROM $bank_tokens_table_name WHERE bank_token = '".$session_id."'");
            $trip_data = json_decode(stripslashes($bank_tokens_retrieve_data->user_data));
            $trip_no = $wpdb->escape($trip_data->trip_no);
            $extra_trip = $wpdb->escape($trip_data->extra_trip);
            $user_id = $wpdb->escape($trip_data->user_id);
            $gea_subscription_id = $wpdb->escape($trip_data->gea_subscription_id);
            $invoice_id = $wpdb->escape($trip_data->invoice_id);
            $vehicle_type = $wpdb->escape($trip_data->vehicle_type);
            $tellamount = $wpdb->escape($trip_data->amount);
            //exit;

            if (isset($OrderStatus) && !empty($OrderStatus)) {
                if ($OrderStatus == 'APPROVED') {
                    $PAN = !empty($return_var['PAN'])?$return_var['PAN']:$return_var['Message']['PAN'];
                    $CardHolderName = !empty($return_var['CardHolderName'])?$return_var['CardHolderName']:$return_var['Message']['CardHolderName'];
                    $Brand = !empty($return_var['Brand'])?$return_var['Brand']:$return_var['Message']['Brand'];

                    $bank_desc = $PAN . '|' . $CardHolderName . '|' . $Brand;
                    //echo 'Success';

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
                                'pgw_name' => 'city bank',
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
                    $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
                    $bank_tokens_update_data = $wpdb->update($bank_tokens_table_name, array('process_status'=>'success', 'bank_data'=>$bank_desc,'modified_by' => get_current_user_id(), 'modified_at' => date('Y-m-d H:i:s')), array('bank_token'=>$session_id));
                    echo '<meta http-equiv="refresh" content="0;url=' . get_bloginfo('url') . '/my-account/add-trip/?msg=' . $success . '" />';
                } else {
                    echo '
                    <div id="loading">
                        <center><img src="' . get_bloginfo('url') . '/wp-content/uploads/2019/12/charging.gif"></center>
                    </div>
                ';
                    unset($_COOKIE['mmhftrip_data']);
                    $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
                    $bank_tokens_update_data = $wpdb->update($bank_tokens_table_name, array('process_status'=>'failed', 'modified_by' => get_current_user_id(), 'modified_at' => date('Y-m-d H:i:s')), array('bank_token'=>$session_id));
                    echo '<meta http-equiv="refresh" content="0;url=' . get_bloginfo('url') . '/my-account/add-trip/?msg=Payment process wrong or declined." />';
                }
            }
        }

    //}
?>
    <script src="//code.jquery.com/jquery-1.8.3.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#main").removeClass("all_colors").addClass("all_colors myAccClass");
        });
    </script>

<?php get_footer() ?>


