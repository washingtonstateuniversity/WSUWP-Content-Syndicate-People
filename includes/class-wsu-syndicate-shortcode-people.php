<?php

class WSU_Syndicate_Shortcode_People extends WSU_Syndicate_Shortcode_Base {
	/**
	 * @var string Script version for cache breaking.
	 */
	public $script_version = '1.2.5';

	/**
	 * @var array A list of defaults specific to people that will override the
	 *            base defaults set for all syndicate shortcodes.
	 */
	public $local_default_atts = array(
		'output' => 'basic',
		'host'   => 'people.wsu.edu',
		'query'  => 'people',
	);

	/**
	 * @var array A set of default attributes for this shortcode only.
	 */
	public $local_extended_atts = array(
		'classification'              => '',
		'display_fields'              => 'photo,name,title,office,email',
		'photo_size'                 => 'thumbnail',
		'filters'                     => '',
		'bg_image'                   => '',
		'search_filter_label'         => 'Type to search',
		'location_filter_label'       => 'Filter by location',
		'organization_filter_label'   => 'Filter by organization',
		'classification_filter_label'  => 'Filter by classification',
		'tag_filter_label'            => 'Filter by tag',
		'category_filter_label'       => 'Filter by category',
		'website_link_text'          => 'Website',
		'link'                       => '',
		'nid'                        => '',
		'profile_page_url'            => '', // Link to dynamic profile page
		'heading_tag'                => 'h2', // Heading tag used on profile page
		'wrapper_class_name'         => 'wsuwp-people-wrapper',
		'items_per_row'              => '4'
	);

	/**
	 * @var string Shortcode name.
	 */
	public $shortcode_name = 'wsuwp_people';

	/**
	 * @var array
	 */
	public $filter_terms = array();

	public function __construct() {
		parent::construct();
	}

	public function add_shortcode() {
		add_shortcode( 'wsuwp_people', array( $this, 'display_shortcode' ) );
	}

	/**
	 * Display people from people.wsu.edu in a structured format using the
	 * WP REST API.
	 *
	 * @since 1.0.0 Pulled from WSUWP Content Syndicate
	 *
	 * @param array $atts Attributes passed to the shortcode.
	 *
	 * @return string Content to display in place of the shortcode.
	 */
	public function display_shortcode( $atts ) {
		$atts = $this->process_attributes( $atts );

		$site_url = $this->get_request_url( $atts );

		if ( ! $site_url ) {
			return '<!-- wsuwp_people ERROR - an empty host was supplied -->';
		}

		if ( ! empty( $atts['filters'] ) && empty( $atts['nid'] ) ) {
			wp_enqueue_style( 'wsuwp-people-filter', plugins_url( 'css/filters.css', dirname( __FILE__ ) ), array(), $this->script_version );
			wp_enqueue_script( 'wsuwp-people-filter', plugins_url( 'js/filters.min.js', dirname( __FILE__ ) ), array( 'jquery' ), $this->script_version, true );
		}

		// @codingStandardsIgnoreStart
		if ( 'profile' === $atts['output'] && empty( $atts['nid'] ) && isset( $_REQUEST['nid'] ) ) {
		// @codingStandardsIgnoreEnd

			$nid_id = sanitize_text_field( $_REQUEST['nid'] );

			// Remove query string chars just to be sure someone doesn't throw in extra stuff and then escape.
			// TODO check how WP handles search input.
			$nid = esc_html( str_replace( array( '&', '?', '=', '[', ']', ',' ), '', $nid_id ) );

			// Add nid to atts
			$atts['nid'] = $nid;

		} // End if

		$content = $this->get_content_cache( $atts, 'wsuwp_people' );

		$content = false;

		if ( $content ) {
			return $content;
		}

		$request_url = esc_url( $site_url['host'] . $site_url['path'] . $this->default_path ) . $atts['query'];

		if ( $atts['nid'] ) {
			$request_url = add_query_arg( array(
				'wsu_nid' => sanitize_text_field( $atts['nid'] ),
			), $request_url );
		} elseif ( 'profile' === $atts['output'] && empty( $atts['nid'] ) ) {
			// Stop if trying to display profile but no nid - otherwise it will query unrelated profiles.
			// TODO probably a better way to handle this.
			return '';

		} else {
			$request_url = $this->build_taxonomy_filters( $atts, $request_url );

			if ( $atts['count'] ) {
				$count = ( 200 < absint( $atts['count'] ) ) ? 200 : $atts['count'];
				$request_url = add_query_arg( array(
					'per_page' => absint( $count ),
				), $request_url );
			}

			if ( ! empty( $atts['classification'] ) ) {
				$request_url = add_query_arg( array(
					'filter[classification]' => sanitize_key( $atts['classification'] ),
				), $request_url );
			}
		}

		$response = wp_remote_get( $request_url );

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$data = wp_remote_retrieve_body( $response );

		if ( empty( $data ) ) {
			return '';
		}

		$wrapper_class = $atts['wrapper_class_name'];

		if ( 'card' === $atts['output'] ){

			$wrapper_class .= ' wsu-c-card__items-row--' . $atts['items_per_row'];

		}

		$content = '<div class="' . $wrapper_class . '">';

		$people = json_decode( $data );

		if ( 'people.wsu.edu' !== $site_url['host'] ) {
			$people = $this->request_primary_profiles( $people, $count );
		}

		$people = $this->sort_items( $people, $atts );

		// html of profiles that's filtered before adding to content.
		$inner_content = '';

		$this->filter_terms = array(
			'wsuwp_university_location' => array(),
			'wsuwp_university_org' => array(),
			'wsuwp_university_category' => array(),
			'classification' => array(),
			'post_tag' => array(),
			'category' => array(),
		);

		foreach ( $people as $person ) {
			if ( ! empty( $atts['filters'] ) && empty( $atts['nid'] ) ) {
				$last_iteration = ( end( $people ) === $person );
				$content .= $this->generate_filter_html( $person, $atts, $last_iteration );
			}

			$inner_content .= $this->generate_item_html( $person, $atts['output'], $atts );
		}

		// Apply filters and add to content
		$content .= apply_filters( 'wsuwp_people_items_html', $inner_content, $people, $atts );

		$content .= '</div><!-- end wsuwp-people-wrapper -->';

		$this->set_content_cache( $atts, 'wsuwp_people', $content );

		return $content;
	}

	/**
	 * Request the primary profiles for results from a site other than people.wsu.edu.
	 *
	 * @since 1.0.2
	 *
	 * @param array $people Items returned by the REST request.
	 * @param array $count  The number of results to request.
	 *
	 * @return array
	 */
	public function request_primary_profiles( $people, $count ) {
		$request_url = esc_url( $this->local_default_atts['host'] . '/' . $this->default_path . $this->local_default_atts['query'] );

		if ( $count ) {
			$request_url = add_query_arg( array(
				'per_page' => absint( $count ),
			), $request_url );
		}

		foreach ( $people as $person ) {
			if ( isset( $person->primary_profile_id ) ) {
				$request_url = add_query_arg( 'include[]', $person->primary_profile_id, $request_url );
			}
		}

		$response = wp_remote_get( $request_url );

		if ( is_wp_error( $response ) ) {
			return $people;
		}

		$data = wp_remote_retrieve_body( $response );

		if ( empty( $data ) ) {
			return $people;
		}

		$primary_people = json_decode( $data );

		// Recursively cast the results from the host site as an array.
		$host_people_array = json_decode( wp_json_encode( $people ), true );

		// Reindex the host site results by `primary_profile_id`.
		$host_people = array_column( $host_people_array, null, 'primary_profile_id' );

		foreach ( $primary_people as $index => $person ) {
			$id = $person->id;

			// Replace the primary profile link with the host site profile link.
			$primary_people[ $index ]->link = $host_people[ $id ]['link'];

			// Add the photo, title, and bio display options from the host site profile.
			$primary_people[ $index ]->display_photo = $host_people[ $id ]['display_photo'];
			$primary_people[ $index ]->display_title = $host_people[ $id ]['display_title'];
			$primary_people[ $index ]->display_bio = $host_people[ $id ]['display_bio'];
		}

		return $primary_people;
	}

	/**
	 * Sort the results of the REST request.
	 *
	 * @since 1.0.2
	 *
	 * @param array $people Items returned by the REST request.
	 * @param array $atts   Attributes passed to the shortcode.
	 *
	 * @return array Sorted items.
	 */
	public function sort_items( $people, $atts ) {
		usort( $people, array( $this, 'sort_alpha' ) );

		return apply_filters( 'wsuwp_people_sort_items', $people, $atts );
	}

	/**
	 * Sort people alphabetically by their last name.
	 *
	 * @since 1.0.2
	 *
	 * @param stdClass $a Object representing a person.
	 * @param stdClass $b Object representing a person.
	 *
	 * @return int Whether person a's last name is alphabetically smaller or greater than person b's.
	 */
	public function sort_alpha( $a, $b ) {
		return strcasecmp( $a->last_name, $b->last_name );
	}

	/**
	 * Generate the HTML used for individual people when called with the shortcode.
	 *
	 * @since 1.0.0 Pulled from WSUWP Content Syndicate
	 *
	 * @param stdClass $person Data returned from the WP REST API.
	 * @param string   $type   The type of output expected.
	 * @param array    $atts   The shortcode attributes.
	 *
	 * @return string The generated HTML for an individual person.
	 */
	private function generate_item_html( $person, $type, $atts ) {
		// Determine which fields to display.
		if ( ! empty( $atts['display_fields'] ) ) {
			$display_fields = array_map( 'trim', explode( ',', $atts['display_fields'] ) );
		} else {
			$display_fields = explode( ',', $this->local_extended_atts['display_fields'] );
		}

		// Build out the profile container classes.
		$classes = 'wsuwp-person-container';
		$classes .= ' ' . $person->slug;

		if ( ! empty( $atts['filters'] ) && empty( $atts['nid'] ) && ! empty( $person->taxonomy_terms ) ) {
			foreach ( $person->taxonomy_terms as $taxonomy => $terms ) {
				$prefix = ( 'wsuwp_university_org' === $taxonomy ) ? 'organization' : array_pop( explode( '_', $taxonomy ) );
				foreach ( $terms as $term ) {
					$classes .= ' ' . $prefix . '-' . $term->slug;
				}
			}
		}

		// Cast the collection as an array to account for scenarios
		// where it can sometimes come through as an object.
		$photo_collection = (array) $person->photos;
		$photo = false;

		// Determine the photo size to display.
		// A note about the photo collection:
		// if the uploaded image wasn't big enough to have generated a large or medium size,
		// the full size image is assigned as the value for those keys.
		$photo_size = ( in_array( $atts['photo_size'], array( 'medium', 'large' ), true ) ) ? $atts['photo_size'] : 'thumbnail';

		// Get the URL of the display photo.
		if ( in_array( 'photo', $display_fields, true ) && ! empty( $photo_collection ) ) {
			if ( ! empty( $person->display_photo ) && isset( $photo_collection[ $person->display_photo ] ) ) {
				$photo = $photo_collection[ $person->display_photo ]->$photo_size;
			} elseif ( isset( $photo_collection[0] ) ) {
				$photo = $photo_collection[0]->$photo_size;
			}
		}

		// Get the legacy profile photo URL if the person's collection is empty.
		if ( ! $photo && isset( $person->profile_photo ) ) {
			$photo = $person->profile_photo;
		}

		if ( $photo ) {
			$classes .= ' profile-has-photo';
		}

		// Get the display title(s).
		if ( ! empty( $person->working_titles ) ) {
			if ( ! empty( $person->display_title ) ) {
				$display_titles = explode( ',', $person->display_title );
				foreach ( $display_titles as $display_title ) {
					if ( isset( $person->working_titles[ $display_title ] ) ) {
						$titles[] = $person->working_titles[ $display_title ];
					}
				}
			} else {
				$titles = $person->working_titles;
			}
		} else {
			$titles = array( $person->position_title );
		}

		$office = ( ! empty( $person->office_alt ) ) ? $person->office_alt : $person->office;
		$address = ( ! empty( $person->address_alt ) ) ? $person->address_alt : $person->address;
		$email = ( ! empty( $person->email_alt ) ) ? $person->email_alt : $person->email;
		$phone = ( ! empty( $person->phone_alt ) ) ? $person->phone_alt : $person->phone;

		$website = $person->website;

		// Set the bio if needed.
		// TODO The var $bio is uses and set below for the link. It would be worth while to consolidate these to only set the bio once.

		$bio_fields = array_intersect( array( 'bio', 'bio-unit', 'bio-university' ), $display_fields );

		$about = '';

		// Check which bio to display if has fields or is profile. Default is $person->content->rendered
		if ( ! empty( $bio_fields ) || 'profile' === $atts['output'] ) {

			if ( in_array( 'bio-unit', $display_fields, true ) ) {

				$about = isset( $person->bio_unit ) ? $person->bio_unit : '';

			} elseif ( in_array( 'bio-university', $display_fields, true ) ) {

				$about = isset( $person->bio_university ) ? $person->bio_university : '';

			} else {

				$about = $person->content->rendered;

			} // End if
		} // End if

		$name = $person->title->rendered;

		// Set up profile URL.
		$link = false;

		if ( ! empty( $atts['link'] ) && 'people.wsu.edu' !== $atts['host'] ) {
			if ( 'has-bio' === $atts['link'] ) {

				switch ( $person->display_bio ) {
					case 'university':
						$bio = $person->bio_university;
						break;
					case 'unit':
						$bio = $person->bio_unit;
						break;
					default:
						$bio = $person->content->rendered;
				}
				if ( ! empty( $bio ) ) {
					$link = $person->link;
				}
			} elseif ( 'yes' === $atts['link'] ) {
				$link = $person->link;
			}
		} elseif ( ! empty( $atts['link'] ) && ! empty( $atts['profile_page_url'] ) ) {

			// If has link attr and has profile_page_url -> link to dynamic profile page

			$profile_link = $atts['profile_page_url'] . '?nid=' . $person->nid;

			if ( 'has-bio' === $atts['link'] ) {

				$bio = $person->content->rendered;

				if ( ! empty( $bio ) ) {

					$link = $profile_link;

				} // End if
			} elseif ( 'yes' === $atts['link'] ) {

				$link = $profile_link;

			} // End if
		} // End if

		if ( 'basic' === $type ) {
			ob_start();
			?>
			<div class="<?php echo esc_attr( $classes ); ?>">

				<?php if ( $photo && in_array( 'photo', $display_fields, true ) ) { ?>
				<figure class="wsuwp-person-photo" aria-hidden="true" <?php if ( ! empty( $atts['bg_image'] ) ) : ?>style="background-image:url(<?php echo esc_url( $photo ); ?>)"<?php endif; ?>>
						<?php if ( $link ) { ?><a href="<?php echo esc_url( $link ); ?>"><?php } ?>
						<img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $person->title->rendered ); ?>" />
						<?php if ( $link ) { ?></a><?php } ?>
					</figure>
				<?php } ?>

				<?php if ( in_array( 'name', $display_fields, true ) ) { ?>
				<div class="wsuwp-person-name">
					<?php if ( $link ) { ?><a href="<?php echo esc_url( $link ); ?>"><?php } ?>
					<?php echo esc_html( $person->title->rendered ); ?>
					<?php if ( $link ) { ?></a><?php } ?>
					</div>
				<?php } ?>

				<?php if ( in_array( 'degree', $display_fields, true ) ) { ?>
					<?php foreach ( $person->degree as $degree ) { ?>
					<div class="wsuwp-person-degree"><?php echo esc_html( $degree ); ?></div>
					<?php } ?>
				<?php } ?>

				<?php if ( in_array( 'title', $display_fields, true ) ) { ?>
					<?php foreach ( $titles as $title ) { ?>
					<div class="wsuwp-person-position"><?php echo esc_html( $title ); ?></div>
					<?php } ?>
				<?php } ?>

				<?php if ( in_array( 'office', $display_fields, true ) ) { ?>
				<div class="wsuwp-person-office"><?php echo esc_html( $office ); ?></div>
				<?php } ?>

				<?php if ( in_array( 'address', $display_fields, true ) ) { ?>
				<div class="wsuwp-person-address"><?php echo esc_html( $address ); ?></div>
				<?php } ?>

				<?php if ( in_array( 'email', $display_fields, true ) ) { ?>
				<div class="wsuwp-person-email">
					<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
				</div>
				<?php } ?>

				<?php if ( in_array( 'phone', $display_fields, true ) ) { ?>
				<div class="wsuwp-person-phone"><?php echo esc_html( $phone ); ?></div>
				<?php } ?>

				<?php if ( in_array( 'website', $display_fields, true ) && ! empty( $person->website ) ) { ?>
				<div class="wsuwp-person-website">
					<a href="<?php echo esc_url( $person->website ); ?>"><?php echo esc_html( $atts['website_link_text'] ); ?></a>
				</div>
				<?php } ?>

				<?php if ( ! empty( $about ) ) { ?>
				<div class="wsuwp-person-bio">
					<?php echo wp_kses_post( $about ); ?>
				</div>
				<?php } ?>

			</div>
			<?php
			$html = ob_get_contents();
			ob_end_clean();

			return $html;
		} elseif ( 'profile' === $type ) {

			$heading_tag = ( ! empty( $atts['heading_tag'] ) ) ? $atts['heading_tag'] : 'h2';

			ob_start();

			include dirname( __DIR__ ) . '/templates/profile.php';

			$html = ob_get_clean();

			return $html;

		} elseif ( 'card' === $type ) {

			ob_start();

			include dirname( __DIR__ ) . '/templates/card.php';

			$html = ob_get_clean();

			return $html;

		} // End if

		return apply_filters( 'wsuwp_people_item_html', '', $person, $type, $atts );
	}

	/**
	 * Generate the HTML used for filter inputs.
	 *
	 * @since 1.2.0
	 *
	 * @param stdClass $person         Data returned from the WP REST API.
	 * @param string   $atts           The shortcode attributes.
	 * @param boolean  $last_iteration If this is the last iteration of WP REST API data.
	 *
	 * @return string The generated HTML for filter inputs.
	 */
	private function generate_filter_html( $person, $atts, $last_iteration ) {
		$filters = array_map( 'trim', explode( ',', $atts['filters'] ) );

		if ( ! empty( array_intersect( array( 'location', 'organization', 'category', 'tag', 'classification' ), $filters ) ) ) {
			foreach ( $person->taxonomy_terms as $taxonomy => $terms ) {
				foreach ( $terms as $term ) {
					if ( ! in_array( $term->slug, $this->filter_terms[ $taxonomy ], true ) ) {
						$this->filter_terms[ $taxonomy ][ $term->slug ] = $term->name;
					}
				}
			}
		}

		if ( $last_iteration ) {

			ob_start();
			?>
			<div class="wsuwp-people-filters">
			<?php
			foreach ( $filters as $filter ) {

				if ( 'search' === $filter ) {
					?>
					<div class="wsuwp-people-filter search">
						<label>
							<span class="screen-reader-text">Start typing to search</span>
							<input type="search" value="" placeholder="<?php echo esc_attr( $atts['search_filter_label'] ); ?>" autocomplete="off" />
						</span>
					</div>
					<?php
				}

				if ( 'location' === $filter && ! empty( $this->filter_terms['wsuwp_university_location'] ) ) {
					$this->term_options_html( $filter, $this->filter_terms['wsuwp_university_location'], $atts['location_filter_label'] );
				}

				if ( 'organization' === $filter && ! empty( $this->filter_terms['wsuwp_university_org'] ) ) {
					$this->term_options_html( $filter, $this->filter_terms['wsuwp_university_org'], $atts['organization_filter_label'] );
				}

				if ( 'classification' === $filter && ! empty( $this->filter_terms['classification'] ) ) {
					$this->term_options_html( $filter, $this->filter_terms['classification'], $atts['classification_filter_label'] );
				}

				if ( 'tag' === $filter && ! empty( $this->filter_terms['post_tag'] ) ) {
					$this->term_options_html( $filter, $this->filter_terms['post_tag'], $atts['tag_filter_label'] );
				}

				if ( 'category' === $filter ) {
					$categories = array_merge( $this->filter_terms['wsuwp_university_category'], $this->filter_terms['category'] );
					$categories = array_unique( $categories );

					if ( ! empty( $categories ) ) {
						$this->term_options_html( $filter, $categories, $atts['category_filter_label'] );
					}
				}
			}
			?>
			</div>
			<?php
			$html = ob_get_clean();

			return $html;
		}
	}

	/**
	 * Generate the HTML used for taxonomy term options.
	 *
	 * @since 1.2.0
	 *
	 * @param string $option   The current filter being displayed.
	 * @param string $taxonomy The taxonomy to output terms for.
	 * @param string $label    Label text.
	 */
	private function term_options_html( $option, $taxonomy, $label ) {
		ksort( $taxonomy );
		?>
		<div class="wsuwp-people-filter <?php echo esc_attr( $option ); ?>">
			<button type="button" class="wsuwp-people-filter-label" aria-expanded="false"><?php echo esc_html( $label ); ?></button>
			<ul class="wsuwp-people-filter-terms">
				<?php foreach ( $taxonomy as $slug => $name ) { ?>
				<li>
					<label>
						<input type="checkbox" value="<?php echo esc_attr( $option ) . '-' . esc_attr( $slug ); ?>">
						<span><?php echo esc_html( $name ); ?></span>
					</label>
				</li>
				<?php } ?>
			</ul>
		</div>
		<?php
	}
}
