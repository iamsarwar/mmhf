<?php  
/* 
Template Name: Reload
*/
 
    the_post();
    get_header();
?>
<div class='container_wrap container_wrap_first main_color <?php avia_layout_class( 'main' ); ?>'>

    <div class='container'>
        <main class="template-page content  av-content-full alpha units" role="main" itemprop="mainContentOfPage">

            <article class="post-entry post-entry-type-page post-entry-43" itemscope="itemscope" itemtype="https://schema.org/CreativeWork">

                <div class="entry-content-wrapper clearfix">
                    <header class="entry-content-header"></header>
                    <div class="entry-content" itemprop="text">
                        <div class="woocommerce">

                            <!-- Script -->
                            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
                            <script src='https://makitweb.com/demo/dropdown_select2/select2/js/select2.min.js' type='text/javascript'></script>

                            <!-- CSS -->
                            <link href='https://makitweb.com/demo/dropdown_select2/select2/css/select2.min.css' rel='stylesheet' type='text/css'>

<?php
    echo '
        <script>
            jQuery(document).ready(function () {
                jQuery( "#main" ).removeClass( "all_colors" ).addClass( "all_colors myAccClass" );
            });
        </script>
        ';
    if(is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = ( array )$user->roles;

        if($roles[0] == 'reloads') {
            if(isset($_POST['task']) && $_POST['task'] == 'recharge' ){
                echo '
                    <div id="loading">
                        <center><img src="'.get_bloginfo('url').'/wp-content/uploads/2019/12/charging.gif"></center>
                    </div>
                ';
                global $wpdb;
                $trip_no = $wpdb->escape($_POST['trip_no']);
                $user_id = $wpdb->escape($_POST['user_id']);
                $user_info_id = $wpdb->escape($_POST['user_info_id']);
                $invoice_id = $wpdb->escape($_POST['last_invoice_id']);
                $total_cost = $wpdb->escape($_POST['total_cost']);
                //echo $total_cost;
                //exit;
                if(!empty($trip_no) && !empty($user_id) && !empty($user_info_id) && !empty($invoice_id) && !empty($total_cost)) {
                    $recharge_table_name = $wpdb->prefix . "users_recharge_histories";
                    $recharge_insert_data = $wpdb->insert(
                        $recharge_table_name,
                        array(
                            'user_id' => $user_id,
                            'user_info_id' => $user_info_id,
                            'invoice_id' => $invoice_id,
                            'pgw_name' => 'cash',
                            'trips' => $trip_no,
                            'created_by' => $user_id
                        ),
                        array(
                            '%d',
                            '%d',
                            '%s',
                            '%s',
                            '%d',
                            '%d'
                        )
                    );
                    $trip_table_name = $wpdb->prefix . "users_trips";
                    $trip_retrieve_data = $wpdb->get_row("SELECT $trip_table_name.* FROM $trip_table_name WHERE $trip_table_name.user_id = " . $user_id . " AND $trip_table_name.user_info_id=" . $user_info_id);
                    $total_trip = $trip_no;
                    if (!empty($trip_retrieve_data)) {
                        $total_trip = $trip_no + $trip_retrieve_data->total_trip;
                        $trip_update_data = $wpdb->update($trip_table_name, array('id' => $trip_retrieve_data->id, 'total_trip' => $total_trip, 'updated_by' => get_current_user_id(), 'date_updated' => date('Y-m-d H:i:s')), array('id' => $trip_retrieve_data->id));
                    } else {
                        $trip_insert_data = $wpdb->insert(
                            $trip_table_name,
                            array(
                                'user_id' => $user_id,
                                'user_info_id' => $user_info_id,
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
                    $url ="http://182.160.116.91/subsrevise.php";
                    $info_table_name = $wpdb->prefix . "users_info";
                    $info_retrieve_data = $wpdb->get_row( "SELECT $info_table_name.* FROM $info_table_name WHERE $info_table_name.id = $user_info_id" );
                    //$info_retrieve_data->gea_customer_id.'<br>';
                    $tariff_table_name = $wpdb->prefix . "tariff_rates";
                    $tariff_retrieve_data = $wpdb->get_row( "SELECT $tariff_table_name.* FROM $tariff_table_name WHERE $tariff_table_name.vehicle_type = '$info_retrieve_data->vehicle_type'" );
                    $data=array
                    (
                        "ID_PRODUCT_TYPE"       => $info_retrieve_data->gea_product_type_id,
                        "ID_PRODUCT"            => $info_retrieve_data->gea_product_id,
                        "AMOUNT"                => $total_cost,
                        "VAT_AMOUNT"            => $tariff_retrieve_data->vat_rate,
                        "ID_CUSTOMER"           => $info_retrieve_data->gea_customer_id,
                        "ID_SUBSCRIPTION"       => $info_retrieve_data->gea_subscription_id
                    );

                    $ch=curl_init($url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data) ;
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_COOKIEJAR, "my_cookies.txt");
                    curl_setopt($ch, CURLOPT_COOKIEFILE, "my_cookies.txt");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3");
                    $response = curl_exec( $ch );
                    curl_close($ch);

                    $re_url = get_bloginfo('url') . '/reloadpoints/?result=success';
                    echo '
                        <script>
                            window.location.href = "'.$re_url.'";
                        </script>
                    ';
                }
                else
                    echo '<br><br><center><h2>Something wrong, please try again</h2></center><br><br>';;
            }
            else {
                global $wpdb;
                $table_name = $wpdb->prefix . 'users_info';
                $user_table_name = $wpdb->prefix . 'users';
                $user_result = $wpdb->get_results("SELECT UI.id, UI.user_id, U.user_login FROM $table_name AS UI LEFT JOIN $user_table_name AS U ON U.ID = UI.user_id GROUP BY UI.user_id");
                ?>
                <form id="trip_form" name="trip_form" class="woocomerce-form woocommerce-form-login login" method="post" style="width:70%">
                    <h2 <?php if(empty($_REQUEST['result']) && $_REQUEST['result']!='success'){?>style="display:none;"<?php } else{?> style="color:green"<?php }?>>Successfully recharged</h2>
                    <h3>Recharge Trip now.</h3>
                    <p><label for="vehicle_type">Select User <span class="required" title="required" style="color:red;">*</span></label>
                    </p>
                    <p>
                        <select id='user_id' name="user_id" onchange="set_vehicle(this.value);">
                            <option value=''>Select</option>
                            <?php
                            foreach ($user_result as $thisResult) {
                                echo "<option value='" . $thisResult->user_id . "'>" . $thisResult->user_login . "</option>";
                            }
                            ?>
                        </select>
                    </p>
                    <span id="vehicle_area"></span>
                    <p><label for="vehicle_type">How many trips <span class="required" title="required"
                                                                      style="color:red;">*</span></label></p>
                    <p><select name="trip_no" class="select is_empty" id="trip_no" onchange="get_cost()">
                            <?php
                            for ($i = 1; $i < 101; $i++) {
                                echo '<option value="' . $i . '">' . $i . '</option>';
                            }
                            ?>
                        </select>
                    </p>
                    <p><label>Total Cost <span class="required" title="required" style="color:red;">*</span></label></p>
                    <p><input type="text" readonly value="0" name="total_cost" id="total_cost" required/></p>
                    <input type="hidden" name="unique_rate" id="unique_rate">
                    <input type="hidden" name="user_info_id" id="user_info_id">
                    <input type="hidden" name="task" value="recharge" />
                    <input type="hidden" name="last_invoice_id" id="last_invoice_id" value="<?php echo generateRechargeInvoiceID(); ?>">
                    <button name="btnRecharge" type="submit" class="button">Submit</button>
                </form>
                <script>
                    $(document).ready(function () {
                        // Initialize select2
                        $("#user_id").select2();

                    });

                    function set_vehicle(user_id) {
                        //alert(user_id);
                        $.ajax({
                            type: "POST",
                            url: "<?php echo get_bloginfo('url');?>/wp-admin/admin-ajax.php",
                            data: {
                                action: "get_vehicle",
                                user_id: user_id
                            },
                            success: function (data) {
                                //alert(data);
                                $("#vehicle_area").html(data);
                            },
                            error: function (errorThrown) {
                                alert(errorThrown);
                            }
                        });
                    }

                    function get_cost() {
                        var info = $("#info_id").val();
                        var vehicle = info.split('_');
                        var unique_rate = vehicle[1];
                        var trip_no = $("#trip_no").val();
                        var total_cost = parseInt(trip_no) * parseInt(unique_rate);
                        document.getElementById("total_cost").value = total_cost;
                        document.getElementById("unique_rate").value = unique_rate;
                        document.getElementById("user_info_id").value = vehicle[0];
                    }
                </script>
<?php
            }
        }
        else{
            echo '<br><br><center><h2>Not Authorize</h2></center><br><br>';
        }
    }
    else{
        // If we're here, they aren't logged in, show them a message
        $defaults = array(
            // message show to non-logged in users
            'msg'    => __('You must login to see this content.', 'wpAyub'),
            // Login page link
            'link'   => site_url('my-account'),
            // login link anchor text
            'anchor' => __('Login.', 'wpAyub')
        );
        $args = wp_parse_args($args, $defaults);

        $msg = sprintf(
            '<aside class="login-warning">%s <a href="%s">%s</a></aside>',
            esc_html($args['msg']),
            esc_url($args['link']),
            esc_html($args['anchor'])
        );
        echo '<br><br><center>'.$msg.'</center><br><br>';
    }
?>
                        </div>
                    </div>
                </div>
            </article>
        </main>
    </div>
</div>

<?php
    get_footer();
?>

