<?php

namespace WPEmerge\Cli\Presets;

use Symfony\Component\Console\Output\OutputInterface;

class TailwindCss implements PresetInterface {
	use FrontEndPresetTrait;
	use PresetEnablerTrait;

	/**
	 * {@inheritDoc}
	 */
	public function getName() {
		return 'Tailwind CSS';
	}

	/**
	 * {@inheritDoc}
	 */
	public function execute( $directory, OutputInterface $output ) {
		$this->installNodePackage( directory: $directory, output: $output, package: 'tailwindcss', dev: true );
		$this->installNodePackage( directory: $directory, output: $output, package: '@tailwindcss/postcss', dev: true );

		$frontend = $this->path( $directory, 'resources', 'styles', 'frontend' );

		$this->delete( $frontend );

		if ( ! file_exists( $frontend ) ) {
			mkdir( $frontend );
		}

		$copy_list = [
			$this->path( WPEMERGE_CLI_DIR, 'src', 'TailwindCss', 'frontend', 'index.css' )
				=> $this->path( $frontend, 'index.css' ),
		];

		$failures = $this->copy( $copy_list );

		foreach ( $failures as $source => $destination ) {
			$output->writeln( '<failure>File ' . $destination . ' already exists - skipped.</failure>' );
		}

		$postcss_js_filepath = $this->path( $directory, '.postcssrc.js' );
		$this->enablePreset( $postcss_js_filepath, 'Tailwind CSS' );
	}
}
