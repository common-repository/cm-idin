<?php

/**
 * Scripts.
 */
class CM_IDIN_Scripts {
	/**
	 * Construct.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Setup.
	 */
	public function setup() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Register.
	 */
	public function register() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_style(
			'cm-idin',
			plugins_url( 'css/style' . $min . '.css', $this->plugin->file ),
			array(),
			$this->plugin->get_version()
		);
	}

	/**
	 * Enqueue.
	 */
	public function enqueue() {
		wp_enqueue_style( 'cm-idin' );
	}
}
