<?php
/**
 * Template 1 Login Form - Side by Side Layout
 *
 * Integrated from Blaze My Account plugin into Blocksy child theme.
 *
 * @package Blocksy_Child
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'woocommerce_before_customer_login_form' );
?>

<div class="blaze-login-register template1">
	<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>
		<div class="blaze-columns">
			<div class="blaze-column">
	<?php endif; ?>

				<h2><?php esc_html_e( 'Sign In', 'blocksy' ); ?></h2>

				<form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>
					<?php do_action( 'woocommerce_login_form_start' ); ?>

					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="username"><?php esc_html_e( 'Username or email address', 'blocksy' ); ?>&nbsp;<span class="required">*</span></label>
						<input class="woocommerce-Input input-text" type="text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" placeholder="<?php esc_attr_e( 'Username or email address', 'blocksy' ); ?>" required />
					</p>
					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="password"><?php esc_html_e( 'Password', 'blocksy' ); ?>&nbsp;<span class="required">*</span></label>
						<input class="woocommerce-Input input-text" type="password" name="password" id="password" autocomplete="current-password" placeholder="<?php esc_attr_e( 'Password', 'blocksy' ); ?>" required />
					</p>

					<?php do_action( 'woocommerce_login_form' ); ?>

					<div class="login-form-footer">
						<label class="woocommerce-form__label woocommerce-form__label-for-checkbox remember-me">
							<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
							<span><?php esc_html_e( 'Remember me', 'blocksy' ); ?></span>
						</label>
						<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'blocksy' ); ?></a>
					</div>

					<p class="form-row">
						<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
						<button type="submit" class="woocommerce-button button" name="login" value="<?php esc_attr_e( 'Log in', 'blocksy' ); ?>"><?php esc_html_e( 'Log in', 'blocksy' ); ?></button>
					</p>

					<?php do_action( 'woocommerce_login_form_end' ); ?>
				</form>

	<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>
			</div>

			<div class="blaze-column">
				<h2><?php esc_html_e( 'Register', 'blocksy' ); ?></h2>

				<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?>>
					<?php do_action( 'woocommerce_register_form_start' ); ?>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_username"><?php esc_html_e( 'Username', 'blocksy' ); ?>&nbsp;<span class="required">*</span></label>
							<input type="text" class="woocommerce-Input input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" placeholder="<?php esc_attr_e( 'Username', 'blocksy' ); ?>" required />
						</p>
					<?php endif; ?>

					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_email"><?php esc_html_e( 'Email address', 'blocksy' ); ?>&nbsp;<span class="required">*</span></label>
						<input type="email" class="woocommerce-Input input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" placeholder="<?php esc_attr_e( 'Email address', 'blocksy' ); ?>" required />
					</p>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_password"><?php esc_html_e( 'Password', 'blocksy' ); ?>&nbsp;<span class="required">*</span></label>
							<input type="password" class="woocommerce-Input input-text" name="password" id="reg_password" autocomplete="new-password" placeholder="<?php esc_attr_e( 'Password', 'blocksy' ); ?>" required />
						</p>
					<?php else : ?>
						<p><?php esc_html_e( 'A password will be sent to your email address.', 'blocksy' ); ?></p>
					<?php endif; ?>

					<?php do_action( 'woocommerce_register_form' ); ?>

					<p class="woocommerce-privacy-policy-text">
						<?php wc_get_privacy_policy_text( 'registration' ); ?>
					</p>

					<p class="form-row">
						<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
						<button type="submit" class="woocommerce-button button" name="register" value="<?php esc_attr_e( 'Register', 'blocksy' ); ?>"><?php esc_html_e( 'Register', 'blocksy' ); ?></button>
					</p>

					<?php do_action( 'woocommerce_register_form_end' ); ?>
				</form>
			</div>
		</div>
	<?php endif; ?>
</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
