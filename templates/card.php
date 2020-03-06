<!-- COMPONENT:START -->
<div class="wsu-c-card__wrapper">
	<div class="wsu-c-card__container">
		<div class="wsu-c-card__content">
			<?php if ( in_array('photo', $display_fields) && !empty($photo) ) : ?>
				<figure class="wsu-c-card__photo-frame">
					<img class="wsu-c-card__photo" src="<?php echo $photo; ?>" alt="Photo of <?php echo esc_attr( $name ); ?>" data-object-fit>
				</figure>
			<?php endif; ?>

			<?php if ( in_array('name', $display_fields) && !empty($name) ) : ?>
				<?php echo $opening_heading_tag . esc_html( $name ) . $closing_heading_tag; ?>
			<?php endif; ?>

			<?php if ( in_array('title', $display_fields) && !empty($titles) ) : ?>
				<?php foreach ( $titles as $title ) : ?>
					<p class="wsu-c-card__description"><?php echo esc_html( $title ); ?></p>
				<?php endforeach; ?>
			<?php endif; ?>

			<?php if (in_array('address', $display_fields) && !empty($address)) : ?>
				<div class="wsu-c-card__address">
					<span class="wsu-c-card__address-line-1"><?php echo $address; ?></span>
				</div>
			<?php endif; ?>

			<?php if (in_array('phone', $display_fields) && !empty($phone)) : ?>
				<div class="wsu-c-card__phone">
					<a href="tel:<?php echo esc_html( $phone ); ?>" class="wsu-c-card__phone-link"><?php echo esc_html( $phone ); ?></a>
				</div>
			<?php endif; ?>

			<?php if (in_array('email', $display_fields) && !empty($email)) : ?>
				<div class="wsu-c-card__email">
					<a href="mailto:<?php echo esc_attr( $email ); ?>" class="wsu-c-card__email-link"><?php echo esc_attr( $email ); ?></a>
				</div>
			<?php endif; ?>

			<?php if (in_array('website', $display_fields) && !empty($website)) : ?>
				<div class="wsu-c-card__website">
					<a href="<?php echo esc_url( $website ); ?>" class="wsu-c-card__website-link">Website</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<!-- COMPONENT:END -->
