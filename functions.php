// Adding a Form Part to a Fragment 
 
function awoohc_add_update_form_billing( $fragments ) {

	$checkout = WC()->checkout();

	parse_str( $_POST['post_data'], $fields_values );

	ob_start();

	echo '<div class="woocommerce-billing-fields__field-wrapper">';

	$fields = $checkout->get_checkout_fields( 'billing' );

	foreach ( $fields as $key => $field ) {
		$value = $checkout->get_value( $key );

		if ( isset( $field['country_field'], $fields[ $field['country_field'] ] ) ) {
			$field['country'] = $checkout->get_value( $field['country_field'] );
		}

		if ( ! $value && ! empty( $fields_values[ $key ] ) ) {
			$value = $fields_values[ $key ];
		}

		woocommerce_form_field( $key, $field, $value );
	}

	echo '</div>';

	$fragments['.woocommerce-billing-fields__field-wrapper'] = ob_get_clean();

	return $fragments;
}

add_filter( 'woocommerce_update_order_review_fragments', 'awoohc_add_update_form_billing', 99 );


// Hiding fields for the free shipping method

function awoohc_override_checkout_fields( $fields ) {

	// receive the selected delivery methods.
	$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

	// check the current method and remove unnecessary fields.
	if ( false !== strpos( $chosen_methods[0], 'free_shipping' ) ) {
		unset(
			$fields['billing']['billing_company'],
			$fields['billing']['billing_address_1'],
			$fields['billing']['billing_address_2'],
			$fields['billing']['billing_city'],
			$fields['billing']['billing_postcode'],
			$fields['billing']['billing_state'],
			$fields['billing']['billing_phone'],
			$fields['billing']['billing_email']
		);
	}

	return $fields;
}

add_filter( 'woocommerce_checkout_fields', 'awoohc_override_checkout_fields' );


// Preload when switching delivery

function awoohc_add_script_update_shipping_method() {

	if ( is_checkout() ) {
		?>
		<!--Hiding the Country field. If the Country field is used, then hide should be removed-->
		<style>
			#billing_country_field {
				display: none !important;
			}
		</style>

		<!--updating fields when switching delivery-->
		<script>
			  jQuery( document ).ready( function( $ ) {
				  $( document.body ).on( 'updated_checkout updated_shipping_method', function( event, xhr, data ) {
					  $( 'input[name^="shipping_method"]' ).on( 'change', function() {
						  $( '.woocommerce-billing-fields__field-wrapper' ).block( {
							  message: null,
							  overlayCSS: {
								  background: '#fff',
								  'z-index': 10000,
								  opacity: 0.3
							  }
						  } );
					  } );
				  } );
			  } );
		</script>
		<?php
	}
}

add_action( 'wp_footer', 'awoohc_add_script_update_shipping_method' );
