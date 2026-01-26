<?php
/* 
Template Name: CityVehicleReturn
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

            $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
            $bank_tokens_retrieve_data = $wpdb->get_row("SELECT * FROM $bank_tokens_table_name WHERE bank_token = '".$session_id."'");
            $vehicle_data = json_decode(stripslashes($bank_tokens_retrieve_data->user_data));
            $vehicle_type = $wpdb->escape($vehicle_data->vehicle_type);
            $user_id = $wpdb->escape($vehicle_data->user_id);
            $vehicle_reg_no = $wpdb->escape($vehicle_data->vehicle_reg_no);
            $invoice_id = $wpdb->escape($vehicle_data->invoice_id);
            $tellamount = $wpdb->escape($vehicle_data->amount);

            if (isset($OrderStatus) && !empty($OrderStatus)) {
                if ($OrderStatus == 'APPROVED') {
                    $PAN = !empty($return_var['PAN'])?$return_var['PAN']:$return_var['Message']['PAN'];
                    $CardHolderName = !empty($return_var['CardHolderName'])?$return_var['CardHolderName']:$return_var['Message']['CardHolderName'];
                    $Brand = !empty($return_var['Brand'])?$return_var['Brand']:$return_var['Message']['Brand'];

                    $bank_desc = $PAN . '|' . $CardHolderName . '|' . $Brand;
                    //echo 'Success';

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
                            'pgw_name' => 'city bank',
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
                    $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
                    $bank_tokens_update_data = $wpdb->update($bank_tokens_table_name, array('process_status'=>'success', 'bank_data'=>$bank_desc,'modified_by' => $user_id, 'modified_at' => date('Y-m-d H:i:s')), array('bank_token'=>$session_id));
                    echo '<meta http-equiv="refresh" content="0;url=' . get_bloginfo('url') . '/my-account/add-vehicle/?msg=' . $success . '" />';
                } else {
                    echo '
                    <div id="loading">
                        <center><img src="' . get_bloginfo('url') . '/wp-content/uploads/2019/12/charging.gif"></center>
                    </div>
                ';
                    unset($_COOKIE['mmhfvehicle_data']);
                    $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
                    $bank_tokens_update_data = $wpdb->update($bank_tokens_table_name, array('process_status'=>'failed', 'modified_by' => $user_id, 'modified_at' => date('Y-m-d H:i:s')), array('bank_token'=>$session_id));
                    echo '<meta http-equiv="refresh" content="0;url=' . get_bloginfo('url') . '/my-account/add-vehicle/?msg=Payment process wrong or declined." />';
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


