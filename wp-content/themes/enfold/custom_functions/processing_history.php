<?php

function processing_history_endpoint_content(){
    echo '<h2>Processing History</h2>';
    global $wpdb;
    $processing_history_table_name = $wpdb->prefix . "user_process_fees";
    $info_table_name = $wpdb->prefix . "users_info";
    $processing_history_retrieve_data = $wpdb->get_results( "SELECT $processing_history_table_name.*, $info_table_name.vehicle_reg_no FROM $processing_history_table_name LEFT JOIN $info_table_name ON $info_table_name.id = $processing_history_table_name.user_info_id WHERE $processing_history_table_name.user_id = ".get_current_user_id() );
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
            $( "#example" ).DataTable();
        } );
    </script>
    <?php
    if(!empty($processing_history_retrieve_data)){
        echo '
            <table id="example" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Invoice ID</th>
                        <th>Date</th>
                        <th>Vehicle Reg. No.</th>
                        <th>Fees</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
			';

        foreach($processing_history_retrieve_data as $thisData){
            $url = get_bloginfo('url').'/invoice/?type=process&data='.base64_encode($thisData->invoice_id);
            echo '
                <tr>
                    <td>'.$thisData->invoice_id.'</td>
                    <td>'.$thisData->date_inserted.'</td>
                    <td>'.$thisData->vehicle_reg_no.'</td>
                    <td>'.$thisData->process_amount.'</td>
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
                    <th>Fees</th>
                    <th>Action</th>
                </tr>
            </tfoot>
        </table>
			';
    }
}
add_action( 'woocommerce_account_processing-history_endpoint', 'processing_history_endpoint_content' );

?>