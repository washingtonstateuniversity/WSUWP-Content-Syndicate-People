<!-- COMPONENT:START -->
<div class="wsu-c-card wsu-c-card__items-per-row--<?php echo $this->local_extended_atts['items_per_row']; ?>">

	<?php if (!empty($photo)) : ?>
		<div class="wsu-c-card__photo-frame">
			<img class="wsu-c-card__photo" src="<?php echo $photo; ?>" alt="#" data-object-fit>
		</div>
	<?php endif; ?>

	<?php if (!empty($person->first_name) && !empty($person->last_name)) : ?>
		<h3 class="wsu-c-card__heading"><?php echo $person->first_name; ?> <?php echo $person->last_name; ?></h3>
	<?php endif; ?>

	<?php if (!empty($person->position_title)) : ?>
		<p class="wsu-c-card__description"><?php echo $person->position_title; ?></p>
	<?php endif; ?>

	<?php if (!empty($person->address)) : ?>
		<div class="wsu-c-card__address">
			<span class="wsu-c-card__address-line-1"><?php echo $person->address; ?></span>
		</div>
	<?php endif; ?>

	<?php if (!empty($person->phone)) : ?>
		<div class="wsu-c-card__phone">
			<a href="tel:<?php echo $person->phone; ?>" class="wsu-c-card__phone-link"><?php echo $person->phone; ?></a>
		</div>
	<?php endif; ?>

	<?php if (!empty($person->email)) : ?>
		<div class="wsu-c-card__email">
			<a href="mailto:<?php echo $person->email; ?>" class="wsu-c-card__email-link"><?php echo $person->email; ?></a>
		</div>
	<?php endif; ?>

	<?php if (!empty($person->website)) : ?>
		<div class="wsu-c-card__website">
			<a href="<?php echo $person->website; ?>" class="wsu-c-card__website-link">Website</a>
		</div>
	<?php endif; ?>

</div>
<!-- COMPONENT:END -->
