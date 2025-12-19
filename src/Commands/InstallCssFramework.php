<?php

namespace WPEmerge\Cli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\RuntimeException;
use WPEmerge\Cli\Presets\Bootstrap;
use WPEmerge\Cli\Presets\Bulma;
use WPEmerge\Cli\Presets\Foundation;
use WPEmerge\Cli\Presets\NormalizeCss;
use WPEmerge\Cli\Presets\Spectre;
use WPEmerge\Cli\Presets\Tachyons;
use WPEmerge\Cli\Presets\TailwindCss;

class InstallCssFramework extends Command {
	/**
	 * {@inheritDoc}
	 */
	protected function configure(): void {
		$this
			->setName( 'install:css-framework' )
			->setDescription( 'Install a CSS framework.' )
			->setHelp( 'Install a CSS framework from a list of options.' )
			->addArgument(
				'css-framework',
				InputArgument::REQUIRED,
				'CSS framework to install.'
			);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$css_framework = $input->getArgument( 'css-framework' );

		$preset = null;

		switch ( $css_framework ) {
			case 'Bootstrap':
				$preset = new Bootstrap();
				break;

			case 'Tailwind':
			case 'TailwindCss':
			case 'TailwindCSS':
			case 'Tailwind CSS':
				$preset = new TailwindCss();
				break;

			default:
				throw new RuntimeException( 'Unknown css framework selected: ' . $css_framework );
		}

		if ( $preset === null ) {
			$output->writeln( '<failure>Could not define a CSS Framework - skipped.</failure>' );
			return 1;
		}

		$preset->execute( getcwd(), $output );

		return 0;
	}
}
