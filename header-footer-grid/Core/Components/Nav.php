<?php
/**
 * Custom Component class for Header Footer Grid.
 *
 * Name:    Header Footer Grid
 * Author:  Bogdan Preda <bogdan.preda@themeisle.com>
 *
 * @version 1.0.0
 * @package HFG
 */

namespace HFG\Core\Components;

use HFG\Core\Settings;
use HFG\Core\Settings\Manager as SettingsManager;
use HFG\Main;

/**
 * Class Nav
 *
 * @package HFG\Core\Components
 */
class Nav extends Abstract_Component {
	const COMPONENT_ID    = 'primary-menu';
	const STYLE_ID        = 'style';
	const COLOR_ID        = 'color';
	const HOVER_COLOR_ID  = 'hover_color';
	const ACTIVE_COLOR_ID = 'active_color';
	const LAST_ITEM_ID    = 'neve_last_menu_item';
	const NAV_MENU_ID     = 'nv-primary-navigation';

	/**
	 * Nav constructor.
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public function init() {
		$this->set_property( 'label', __( 'Primary Menu', 'neve' ) );
		$this->set_property( 'component_slug', 'hfg-primary-menu' );
		$this->set_property( 'id', $this->get_class_const( 'COMPONENT_ID' ) );
		$this->set_property( 'width', 6 );
		$this->set_property( 'icon', 'tagcloud' );
		$this->set_property( 'section', 'header_menu_primary' );
		$this->set_property( 'has_font_family_control', true );
		$this->set_property( 'has_typeface_control', true );
		$this->set_property( 'default_typography_selector', $this->default_typography_selector . '.builder-item--' . $this->get_id() . ' li > a' );
		$this->default_align = 'right';
		add_filter(
			'neve_last_menu_setting_slug_' . $this->get_class_const( 'COMPONENT_ID' ),
			array(
				$this,
				'filter_neve_last_menu_setting_slug',
			)
		);
	}

	/**
	 * Filter the setting slug for last menu.
	 *
	 * @param string $slug The setting slug.
	 *
	 * @return string
	 * @since   2.3.7
	 * @access  public
	 */
	public function filter_neve_last_menu_setting_slug( $slug ) {
		if ( $slug !== $this->get_class_const( 'LAST_ITEM_ID' ) ) {
			return $this->get_class_const( 'LAST_ITEM_ID' );
		}

		return $slug;
	}

	/**
	 * Called to register component controls.
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public function add_settings() {
		SettingsManager::get_instance()->add(
			[
				'id'                 => self::STYLE_ID,
				'group'              => $this->get_class_const( 'COMPONENT_ID' ),
				'tab'                => SettingsManager::TAB_STYLE,
				'transport'          => 'post' . $this->get_class_const( 'COMPONENT_ID' ),
				'sanitize_callback'  => 'wp_filter_nohtml_kses',
				'default'            => 'style-plain',
				'conditional_header' => true,
				'label'              => __( 'Skin Mode', 'neve' ),
				'type'               => '\Neve\Customizer\Controls\React\Radio_Buttons',
				'section'            => $this->section,
				'options'            => [
					'large_buttons' => true,
					'is_for'        => 'menu',
				],
			]
		);

		SettingsManager::get_instance()->add(
			[
				'id'                 => self::COLOR_ID,
				'group'              => $this->get_class_const( 'COMPONENT_ID' ),
				'tab'                => SettingsManager::TAB_STYLE,
				'transport'          => 'post' . $this->get_class_const( 'COMPONENT_ID' ),
				'sanitize_callback'  => 'sanitize_hex_color',
				'default'            => '#404248',
				'label'              => __( 'Items Color', 'neve' ),
				'type'               => 'neve_color_control',
				'section'            => $this->section,
				'conditional_header' => true,
			]
		);
		SettingsManager::get_instance()->add(
			[
				'id'                 => self::ACTIVE_COLOR_ID,
				'group'              => $this->get_class_const( 'COMPONENT_ID' ),
				'tab'                => SettingsManager::TAB_STYLE,
				'transport'          => 'post' . $this->get_class_const( 'COMPONENT_ID' ),
				'sanitize_callback'  => 'sanitize_hex_color',
				'default'            => '#0366d6',
				'label'              => __( 'Active Item Color', 'neve' ),
				'type'               => 'neve_color_control',
				'section'            => $this->section,
				'conditional_header' => true,
			]
		);
		SettingsManager::get_instance()->add(
			[
				'id'                 => self::HOVER_COLOR_ID,
				'group'              => $this->get_class_const( 'COMPONENT_ID' ),
				'tab'                => SettingsManager::TAB_STYLE,
				'transport'          => 'post' . $this->get_class_const( 'COMPONENT_ID' ),
				'sanitize_callback'  => 'sanitize_hex_color',
				'default'            => '#0366d6',
				'label'              => __( 'Items Hover Color', 'neve' ),
				'type'               => 'neve_color_control',
				'section'            => $this->section,
				'conditional_header' => true,
			]
		);


		$order_default_components = array(
			'search',
		);
		$components               = array(
			'search' => __( 'Search', 'neve' ),
		);

		if ( class_exists( 'WooCommerce', false ) ) {
			array_push( $order_default_components, 'cart' );
			$components['cart'] = __( 'Cart', 'neve' );
		}

		$components = apply_filters( 'neve_last_menu_item_components', $components );

		/**
		 * Last menu item removed for new users and users who didn't have it set.
		 *
		 * @since 2.5.3
		 */
		$old_last_menu_item = json_decode( get_theme_mod( 'neve_last_menu_item' ) );
		if ( $old_last_menu_item !== false && ! empty( $old_last_menu_item ) ) {
			SettingsManager::get_instance()->add(
				[
					'id'                => $this->get_class_const( 'LAST_ITEM_ID' ),
					'group'             => $this->get_class_const( 'COMPONENT_ID' ),
					'tab'               => SettingsManager::TAB_GENERAL,
					'noformat'          => true,
					'transport'         => 'post' . $this->get_class_const( 'COMPONENT_ID' ),
					'sanitize_callback' => array( $this, 'sanitize_last_menu_item' ),
					'default'           => json_encode( $order_default_components ),
					'label'             => __( 'Last Menu Item', 'neve' ),
					'type'              => 'Neve\Customizer\Controls\Ordering',
					'options'           => [
						'components' => $components,
					],
					'section'           => $this->section,
				]
			);
		}
		SettingsManager::get_instance()->add(
			[
				'id'                => 'shortcut',
				'group'             => $this->get_class_const( 'COMPONENT_ID' ),
				'tab'               => SettingsManager::TAB_GENERAL,
				'transport'         => 'postMessage',
				'sanitize_callback' => 'esc_attr',
				'type'              => '\Neve\Customizer\Controls\Button',
				'options'           => [
					'button_text'  => __( 'Primary Menu', 'neve' ),
					'button_class' => 'nv-top-bar-menu-shortcut',
					'icon_class'   => 'menu',
					'shortcut'     => true,
				],
				'section'           => $this->section,
			]
		);

	}

	/**
	 * Sanitize last menu item.
	 *
	 * @param string $value Json value of the control.
	 *
	 * @return string
	 */
	public function sanitize_last_menu_item( $value ) {
		$allowed = array(
			'cart',
			'search',
			'wish_list',
		);

		if ( empty( $value ) ) {
			return json_encode( $allowed );
		}

		$decoded = json_decode( $value, true );

		foreach ( $decoded as $val ) {
			if ( ! in_array( $val, $allowed, true ) ) {
				return json_encode( $allowed );
			}
		}

		return $value;
	}

	/**
	 * The render method for the component.
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public function render_component() {
		Main::get_instance()->load( 'components/component-nav' );
	}

	/**
	 * Add styles to the component.
	 *
	 * @param array $css_array rules array.
	 *
	 * @return array
	 */
	public function add_style( array $css_array = array() ) {
		$color    = SettingsManager::get_instance()->get( $this->get_id() . '_' . self::COLOR_ID );
		$selector = '.builder-item--' . $this->get_id() . ' .nav-menu-primary > .primary-menu-ul ';
		if ( ! empty( $color ) ) {
			$css_array[ $selector . 'li:not(.woocommerce-mini-cart-item) > a, 
			' . $selector . 'li > a .caret-wrap .caret' ] = array( 'color' => sanitize_hex_color( $color ) );
		}

		$hover_color = SettingsManager::get_instance()->get( $this->get_id() . '_hover_color' );
		if ( ! empty( $hover_color ) ) {
			$css_array[ $selector . 'li:not(.woocommerce-mini-cart-item) > a:after' ] = array( 'background-color' => sanitize_hex_color( $hover_color ) );
			if ( SettingsManager::get_instance()->get( $this->get_id() . '_style' ) !== 'style-full-height' ) {
				$css_array[ $selector . 'li:not(.woocommerce-mini-cart-item):hover > a,' . $selector . 'li:hover > a > .caret-wrap .caret' ] = array( 'color' => sanitize_hex_color( $hover_color ) );
			}
		}

		$active_color = SettingsManager::get_instance()->get( $this->get_id() . '_active_color' );
		if ( ! empty( $active_color ) ) {
			$css_array[ $selector . 'li.current-menu-item a, ' . $selector . 'li.current-menu-item a  .caret-wrap .caret' ] = array( 'color' => sanitize_hex_color( $active_color ) );
		}

		return parent::add_style( $css_array );
	}
}
