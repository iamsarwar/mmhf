<?php

function deduction_history_endpoint_content(){
    echo '<h2>Deduction History</h2>';
    global $wpdb;
    $info_table_name = $wpdb->prefix . "users_info";
    $user_retrieve_data = $wpdb->get_row( "SELECT $info_table_name.gea_customer_id FROM $info_table_name WHERE $info_table_name.user_id = ".get_current_user_id() );

    //$url = 'http://192.168.174.251/get_deduct_history.php?c='.$user_retrieve_data->gea_customer_id;
    $url = 'http://182.163.122.66/get_deduct_history.php?c='.$user_retrieve_data->gea_customer_id;
    //exit;
    //send SMS

    $curl = curl_init();

    $timeout = 5;
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    $get_data = json_decode($response);
    //$get_data = file_get_contents($url);
    //print_r($get_data);
    //exit;

    //$deduction_history_table_name = $wpdb->prefix . "user_trip_deductions";
    //$info_table_name = $wpdb->prefix . "users_info";
    //$deduction_history_retrieve_data = $wpdb->get_results( "SELECT $deduction_history_table_name.*, $info_table_name.vehicle_reg_no FROM $deduction_history_table_name LEFT JOIN $info_table_name ON $info_table_name.id = $deduction_history_table_name.user_info_id WHERE $deduction_history_table_name.user_id = ".get_current_user_id() );
    ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js"></script>
    <script type="text/javascript">
        $.extend( $.fn.dataTable.defaults, {
            responsive: true
        } );

        $(document).ready(function() {
            $( "#main" ).removeClass( "all_colors" ).addClass( "all_colors myAccClass" );
            $('#example').DataTable({
                "order": [[ 0, "desc" ]],
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                dom: 'Blfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
        } );
    </script>
    <?php
    if(!empty($get_data)){
        echo '
            <table id="example" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Vehicle Reg. No.</th>
                        <th>Lane No.</th>
                        <th>Deducted Amount</th>
                        <th>Balance After Deduction</th>
                        <th>Remaining Trips</th>
                    </tr>
                </thead>
                <tbody>
			';

        foreach($get_data as $thisData){
            echo '
                <tr>
                    <td>'.$thisData->passage_time.'</td>
                    <td>'.$thisData->reg_no.'</td>
                    <td>'.$thisData->lane_number.'</td>
                    <td>'.$thisData->deducted_fare.'</td>
                    <td>'.$thisData->balance_after_pass.'</td>
                    <td>'.$thisData->balance_trip.'</td>
                </tr>
            ';
        }
        echo '
            </tbody>
            <tfoot>
                <tr>
                    <th>Date</th>
                    <th>Vehicle Reg. No.</th>
                    <th>Lane No.</th>
                    <th>Deducted Amount</th>
                    <th>Balance After Deduction</th>
                    <th>Remaining Trips</th>
                </tr>
            </tfoot>
        </table>
			';
    }
}
add_action( 'woocommerce_account_deduction-history_endpoint', 'deduction_history_endpoint_content' );

?>
