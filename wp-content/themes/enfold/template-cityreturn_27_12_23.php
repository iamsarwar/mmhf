<?php
/* 
Template Name: CityReturn
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
    //echo $_SESSION['mmhfreg_data'];
    //echo $_COOKIE['mmhfreg_data'];
    //$trip_data = json_decode(stripslashes($_COOKIE['mmhftrip_data']));
    //echo '<pre>';
    //var_dump($reg_data);
    //echo '</pre>';
    //echo $reg_data->user_email;
    //echo $_COOKIE['mmhfreg_data'];

	global $wpdb, $PasswordHash, $current_user, $user_ID;
    if (!empty($_REQUEST['xmlmsg'])) {
        $_REQUEST['xmlmsg'] = stripslashes($_REQUEST['xmlmsg']);
        $xmlResponse = simplexml_load_string($_REQUEST['xmlmsg']);
        $json = json_encode($xmlResponse);
        $return_var = json_decode($json,TRUE);
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

        if (isset($OrderStatus) && !empty($OrderStatus)) {
            if ($OrderStatus == 'APPROVED') {
                $PAN = !empty($return_var['PAN'])?$return_var['PAN']:$return_var['Message']['PAN'];
                $CardHolderName = !empty($return_var['CardHolderName'])?$return_var['CardHolderName']:$return_var['Message']['CardHolderName'];
                $Brand = !empty($return_var['Brand'])?$return_var['Brand']:$return_var['Message']['Brand'];

                $bank_desc = $PAN . '|' . $CardHolderName . '|' . $Brand;
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
                            <center><img src="'.get_bloginfo('url').'/wp-content/uploads/2019/12/charging.gif"></center>
                        </div>
                    ';
                    $success = 'Successfully registered';
                    unset($_COOKIE['mmhfreg_data']);
                    $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
                    $bank_tokens_update_data = $wpdb->update($bank_tokens_table_name, array('process_status'=>'success', 'bank_data'=>$bank_desc,'modified_by' => $user_id, 'modified_at' => date('Y-m-d H:i:s')), array('bank_token'=>$session_id));
                    echo '<meta http-equiv="refresh" content="0;url='.get_bloginfo('url').'/my-account/?msg='.$success.'" />';
                }
            } else {
                echo '
                        <div id="loading">
                            <center><img src="'.get_bloginfo('url').'/wp-content/uploads/2019/12/charging.gif"></center>
                        </div>
                    ';
                unset($_COOKIE['mmhfreg_data']);
                $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
                $bank_tokens_update_data = $wpdb->update($bank_tokens_table_name, array('process_status'=>'failed', 'modified_by' => $user_id, 'modified_at' => date('Y-m-d H:i:s')), array('bank_token'=>$session_id));
                echo '<meta http-equiv="refresh" content="0;url='.get_bloginfo('url').'/registration/?msg=Payment process wrong or declined, can not be registered." />';
            }
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


