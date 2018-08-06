<?php

/**
 * Template for single profile view
 * 
 * @param string $photo Profile image
 * @param string $name Person name
 * @param array $titles Position titles
 * @param string $email Person email
 * @param string $phone Person phone
 * @param string $office Person office
 * @param string $address Person address
 * @param string $website Person link to website
 * @param string $about Person bio
 * @param string $heading_tag Tag to use for profile name
 */

?><div class="card">
	<?php if ( $photo ) : ?>
	<figure class="photo">
		<img src="<?php echo esc_url( $photo ); ?>"
			alt="<?php echo esc_attr( $name ); ?>" />
	</figure>
	<?php endif ?>

	<div class="contact">
		<<?php echo esc_html( $heading_tag ); ?>><?php echo esc_html( $name ); ?></<?php echo esc_html( $heading_tag ); ?>>
		<?php
		if ( $titles ) {
			foreach ( $titles as $title ) {
				?><span class="title"><?php echo esc_html( $title ); ?></span><?php
			}
		}
		?>
		<span class="email"><a href="mailto:<?php echo esc_attr( $email ); ?>">
			<?php echo esc_html( $email ); ?></a>
		</span>
		<span class="phone"><?php echo esc_html( $phone ); ?></span>
		<span class="office"><?php echo esc_html( $office ); ?></span>
		<?php if ( ! empty( $address ) ) : ?>
		<span class="address"><?php echo esc_html( $address ); ?></span>
		<?php endif ?>
		<?php if ( ! empty( $website ) ) : ?>
		<span class="website">
			<a href="<?php echo esc_url( $website ); ?>"><?php echo esc_url( $website ); ?></a>
		</span>
		<?php endif ?>
	</div>
</div>

<?php if ( ! empty( $about ) ) : ?>
<div class="about">
	<?php echo wp_kses_post( $about ); ?>
</div>
<?php endif; ?>
