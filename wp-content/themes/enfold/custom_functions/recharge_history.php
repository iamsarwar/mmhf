<?php

//global $wpdb, $PasswordHash, $current_user, $user_ID;

function RHHttpGet($url)
{
    $ch = curl_init();
    $timeout = 10;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/0 (Windows; U; Windows NT 0; zh-CN; rv:3)");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $file_contents = curl_exec($ch);
    echo curl_error($ch);
    curl_close($ch);
    return $file_contents;
}

function recharge_history_endpoint_content(){
    global $wpdb;
    $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
    //echo "SELECT * FROM $bank_tokens_table_name WHERE bank_name = 'nagad' AND process_status = 'pending' AND payment_for = 'add trip' AND created_by = '".get_current_user_id()."' AND DATE_FORMAT(created_at, '%Y-%m-%d') = '".date('Y-m-d')."'";
    //exit;
    $bank_tokens_retrieve_data = $wpdb->get_results("SELECT * FROM $bank_tokens_table_name WHERE bank_name = 'nagad' AND process_status = 'pending' AND payment_for = 'add trip' AND created_by = '".get_current_user_id()."' AND DATE_FORMAT(created_at, '%Y-%m-%d') = '".date('Y-m-d')."'");
    if(!empty($bank_tokens_retrieve_data)){
        foreach($bank_tokens_retrieve_data as $thisBankData){
            $paymentRefId = $thisBankData->bank_token;
            $url = "https://api.mynagad.com/api/dfs/verify/payment/".$paymentRefId;
            $json = RHHttpGet($url);
            $arr = json_decode($json, true);
//		echo '<pre>';
//		print_r($arr);
//		echo '</pre>';
//exit;
            if (!empty($arr)) {

                $trans_id = $arr['orderId'];
                $paymentRefId = $arr['paymentRefId'];
                $pgw_transaction_id = $trans_id.'||'.$paymentRefId;
                $amount = $arr['amount'];

                //echo $bank_tokens_retrieve_data->payment_for;
                //exit;
                $string = substr($thisBankData->user_data, 1, -1);
		//echo $string = $thisBankData->user_data;
                if (isset($arr['status']) && !empty($arr['status'])) {
                    if ($arr['status'] == 'Success') {
                        $bank_desc = $paymentRefId;
                        $trip_data = json_decode(stripslashes($string));
//print_r($trip_data);
                        $trip_no = $wpdb->escape($trip_data->trip_no);
                        $user_id = $wpdb->escape($trip_data->user_id);
                        $gea_subscription_id = $wpdb->escape($trip_data->gea_subscription_id);
                        $invoice_id = $wpdb->escape($trip_data->invoice_id);
                        $vehicle_type = $wpdb->escape($trip_data->vehicle_type);
                        $tellamount = $wpdb->escape($trip_data->amount);

                        $recharge_table_name = $wpdb->prefix . "users_recharge_histories";
                        $recharge_retrieve_data = $wpdb->get_row("SELECT $recharge_table_name.id FROM $recharge_table_name WHERE $recharge_table_name.pgw_transaction_id = '$pgw_transaction_id'");
                        //echo 'ASI';
                        //echo "SELECT $recharge_table_name.id FROM $recharge_table_name WHERE $recharge_table_name.pgw_transaction_id = '$pgw_transaction_id'";

                        if (empty($recharge_retrieve_data->id)) {
                            $info_table_name = $wpdb->prefix . "users_info";
                            $info_retrieve_data = $wpdb->get_row("SELECT $info_table_name.* FROM $info_table_name WHERE $info_table_name.gea_subscription_id = $gea_subscription_id");
                            $tariff_table_name = $wpdb->prefix . "tariff_rates";
                            $tariff_retrieve_data = $wpdb->get_row("SELECT $tariff_table_name.* FROM $tariff_table_name WHERE $tariff_table_name.vehicle_type = '$vehicle_type'");
                            $actual_trip_no = floor($total_cost / $tariff_retrieve_data->toll_rates);
                            $total_cost = $tariff_retrieve_data->toll_rates * $trip_no;
                            $recharge_insert_data = $wpdb->insert(
                                $recharge_table_name,
                                array(
                                    'user_id' => $user_id,
                                    'gea_subscription_id' => $gea_subscription_id,
                                    'invoice_id' => $invoice_id,
                                    'pgw_name' => 'nagad',
                                    'pgw_transaction_id' => $pgw_transaction_id,
                                    'pgw_payment_id' => $trans_id,
                                    'pgw_amount' => $tellamount,
                                    'trips' => $trip_no,
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
                                    '%d'
                                )
                            );
                            $trip_table_name = $wpdb->prefix . "users_trips";
                            $trip_retrieve_data = $wpdb->get_row("SELECT $trip_table_name.* FROM $trip_table_name WHERE $trip_table_name.user_id = " . $user_id . " AND $trip_table_name.gea_subscription_id=" . $gea_subscription_id);
                            $total_trip = $actual_trip_no;
                            if (!empty($trip_retrieve_data)) {
                                echo $total_trip = $trip_no + $trip_retrieve_data->total_trip;
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


	                $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
	                $bank_tokens_update_data = $wpdb->update($bank_tokens_table_name, array('process_status'=>'success', 'bank_data'=>$bank_desc,'modified_by' => $user_id, 'modified_at' => date('Y-m-d H:i:s')), array('bank_token'=>$paymentRefId));

                        }
                    }
                }
            }
        }
    }

    echo '<h2>Recharge History</h2>';
    //global $wpdb;
    $recharge_history_table_name = $wpdb->prefix . "users_recharge_histories";
    $info_table_name = $wpdb->prefix . "users_info";
    $recharge_history_retrieve_data = $wpdb->get_results( "SELECT $recharge_history_table_name.*, $info_table_name.vehicle_reg_no FROM $recharge_history_table_name LEFT JOIN $info_table_name ON $info_table_name.id = $recharge_history_table_name.user_info_id WHERE $recharge_history_table_name.user_id = ".get_current_user_id() );
    if(!empty($recharge_history_retrieve_data)){
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
                $('#example').DataTable();
            } );
        </script>
        <?php
        echo '
            <table id="example" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Invoice ID</th>
                        <th>Date</th>
                        <th>Vehicle Reg. No.</th>
                        <th>Trips</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
			';

        foreach($recharge_history_retrieve_data as $thisData){
            $url = get_bloginfo('url').'/invoice/?data='.base64_encode($thisData->invoice_id);
            echo '
                <tr>
                    <td>'.$thisData->invoice_id.'</td>
                    <td>'.date('Y-m-d', strtotime($thisData->date_inserted)).'</td>
                    <td>'.$thisData->vehicle_reg_no.'</td>
                    <td>'.$thisData->trips.'</td>
                    <td><button name="btnAdd" onclick="window.location.href=\''.$url.'\';" class="button">View</button> </td>
                </tr>
            ';
        }
        echo '
            </tbody>
            <tfoot>
                <tr>
                    <th>Invoice ID</th>
                    <th>Date</th>
                    <th>Vehicle Reg. No.</th>
                    <th>Trips</th>
                    <th>Action</th>
                </tr>
            </tfoot>
        </table>
			';
    }
}
add_action( 'woocommerce_account_recharge-history_endpoint', 'recharge_history_endpoint_content' );

?>
