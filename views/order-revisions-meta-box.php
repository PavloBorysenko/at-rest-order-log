<div class="at-rest-order-revisions-wrapper">
    <div class="at-rest-revisions-container">
        <?php
        foreach ( $revisions as $revision ) :
            $timestamp = $revision['timestamp'];
            $data = $revision['data'];
            $date_formatted = date_i18n( 'd.m.Y H:i:s', $timestamp );
            
            $status_class = 'status-default';
            if ( $data['status'] === 'completed' ) {
                $status_class = 'status-completed';
            } elseif ( $data['status'] === 'processing' ) {
                $status_class = 'status-processing';
            }
            ?>
            <div class="at-rest-revision-item">
                <input type="checkbox" id="revision-toggle-<?php echo esc_attr( $timestamp ); ?>" class="revision-toggle" />
                
                <label for="revision-toggle-<?php echo esc_attr( $timestamp ); ?>" class="at-rest-revision-header">
                    <div class="revision-header-content">
                        <span class="revision-date">
                            <?php echo esc_html__( 'Archive at:', 'at-rest-order-log' ); ?> 
                            <?php echo esc_html( $date_formatted ); ?>
                        </span>
                        <span class="revision-status <?php echo esc_attr( $status_class ); ?>">
                            <?php echo esc_html( wc_get_order_status_name( $data['status'] ) ); ?>
                        </span>
                    </div>
                    <span class="at-rest-revision-toggle-icon"></span>
                </label>
                
                <div class="at-rest-revision-content">
                    <?php if ( ! empty( $data['user_id'] ) ) { 
                        $user = get_userdata( $data['user_id'] );
                        if ( $user ) {
                    ?>
                        <div class="revision-user-info">
                            <span>
                                <?php echo esc_html__( 'Author of change:', 'at-rest-order-log' ); ?>
                            </span>
                            <strong><?php echo esc_html( $user->display_name ); ?></strong>
                            <span class="user-login">(<?php echo esc_html( $user->user_login ); ?>)</span>
                        </div>
                    <?php 
                        } 
                    } 
                    ?>
                    
                    <div class="revision-addresses">
                        <div class="address-block">
                            <h4 class="section-title"><?php echo esc_html__( 'Billing Address', 'at-rest-order-log' ); ?></h4>
                            <div class="address-content">
                                <?php
                                if ( ! empty( $data['billing'] ) ) {
                                    echo esc_html( implode( ', ', array_filter( $data['billing'] ) ) );
                                } else {
                                    echo '<em>' . esc_html__( 'Not provided', 'at-rest-order-log' ) . '</em>';
                                }
                                ?>
                            </div>
                        </div>

                    </div>

                    <?php if ( ! empty( $data['items'] ) ) : ?>
                        <div class="revision-items">
                            <h4 class="section-title"><?php echo esc_html__( 'Items', 'at-rest-order-log' ); ?></h4>
                            <table class="items-table">
                                <thead>
                                    <tr>
                                        <th class="col-product"><?php echo esc_html__( 'Product', 'at-rest-order-log' ); ?></th>
                                        <th class="col-qty"><?php echo esc_html__( 'Qty', 'at-rest-order-log' ); ?></th>
                                        <th class="col-price"><?php echo esc_html__( 'Price', 'at-rest-order-log' ); ?></th>
                                        <th class="col-total"><?php echo esc_html__( 'Total', 'at-rest-order-log' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $data['items'] as $item ) : ?>
                                        <tr>
                                            <td class="col-product">
                                                <?php echo esc_html( $item['name'] ); ?>
                                                <?php if ( ! empty( $item['meta'] ) ) : ?>
                                                    <div class="item-meta">
                                                        <?php
                                                        foreach ( $item['meta'] as $meta ) {
                                                            if ( is_object( $meta ) && isset( $meta->key, $meta->value ) ) {
                                                                echo esc_html( $meta->key . ': ' . $meta->value ) . '<br>';
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="col-qty">
                                                <?php echo esc_html( $item['qty'] ); ?>
                                            </td>
                                            <td class="col-price">
                                                <?php echo wc_price( $item['subtotal'] / max( $item['qty'], 1 ) ); ?>
                                            </td>
                                            <td class="col-total">
                                                <?php echo wc_price( $item['total'] ); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $data['fees'] ) ) : ?>
                        <div class="revision-fees">
                            <h4 class="section-title"><?php echo esc_html__( 'Fees', 'at-rest-order-log' ); ?></h4>
                            <table class="items-table">
                                <thead>
                                    <tr>
                                        <th class="col-product"><?php echo esc_html__( 'Fee Name', 'at-rest-order-log' ); ?></th>
                                        <th class="col-total"><?php echo esc_html__( 'Amount', 'at-rest-order-log' ); ?></th>
                                        <th class="col-total"><?php echo esc_html__( 'Tax', 'at-rest-order-log' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $data['fees'] as $fee ) : ?>
                                        <tr>
                                            <td class="col-product"><?php echo esc_html( $fee['name'] ); ?></td>
                                            <td class="col-total"><?php echo wc_price( $fee['total'] ); ?></td>
                                            <td class="col-total"><?php echo wc_price( $fee['tax'] ); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $data['totals'] ) ) : ?>
                        <div class="revision-totals">
                            <h4 class="section-title"><?php echo esc_html__( 'Totals', 'at-rest-order-log' ); ?></h4>
                            <table class="totals-table">
                                <tr>
                                    <td class="totals-label"><?php echo esc_html__( 'Subtotal:', 'at-rest-order-log' ); ?></td>
                                    <td class="totals-value">
                                        <?php echo wc_price( $data['totals']['subtotal'] ); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="totals-label"><?php echo esc_html__( 'Total:', 'at-rest-order-log' ); ?></td>
                                    <td class="totals-value totals-total">
                                        <?php echo wc_price( $data['totals']['total'] ); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $data['custom_meta'] ) ) : ?>
                        <div class="revision-meta">
                            <h4 class="section-title"><?php echo esc_html__( 'Additional Data', 'at-rest-order-log' ); ?></h4>
                            <div class="meta-content">
                                <?php
                                foreach ( $data['custom_meta'] as $meta ) {
                                    if ( is_object( $meta ) && isset( $meta->key, $meta->value ) ) {
                                        if ( strpos( $meta->key, '_revision/' ) === 0 ) {
                                            continue;
                                        }
                                        $value = is_string( $meta->value ) ? $meta->value : print_r( $meta->value, true );
                                        echo '<div class="meta-item"><strong>' . esc_html( $meta->key ) . ':</strong> ' . esc_html( $value ) . '</div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
