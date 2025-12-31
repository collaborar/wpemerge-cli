<?php

namespace WPEmerge\Cli\Presets;

trait FilesystemTrait {
	/**
	 * Join path pieces with appropriate directory separator.
	 *
	 * @param  string ,...$path
	 * @return string
	 */
	protected function path() {
		return implode( DIRECTORY_SEPARATOR, func_get_args() );
	}

	/**
	 * Copy a list of files, returning an array of failures.
	 *
	 * @param  array $files
	 * @return array
	 */
	protected function copy( $files ) {
		$failures = [];

		foreach ( $files as $source => $destination ) {
			if ( file_exists( $destination ) ) {
				$failures[ $source ] = $destination;
				continue;
			}

			$directory = dirname( $destination );
			if ( ! file_exists( $directory ) ) {
				mkdir( $directory, 0777, true );
			}

			copy( $source, $destination );
		}

		return $failures;
	}

	/**
	 * Get the EOL character(s) for a given string from the its first line.
	 * Returns PHP_EOL if no newline characters are found.
	 *
	 * @param  string  $string
	 * @return string
	 */
	protected function getEol( $string ) {
		$pattern = '~[\s\S]*?([\r\n]+)[\s\S]*~m';
		$eol = preg_replace( $pattern, '$1', $string );
		return strlen( $eol ) > 0 ? $eol : PHP_EOL;
	}

	/**
	 * Get whether a string has a given statement present.
	 *
	 * @param  string  $haystack
	 * @param  string  $needle
	 * @return boolean
	 */
	protected function stringHasStatement( $haystack, $needle ) {
		$pattern = '~^\s*(' . preg_quote( $needle, '~' ) . ')\s*$~m';
		return (bool) preg_match( $pattern, $haystack );
	}

	/**
	 * Append a statement to file.
	 *
	 * @param  string $filepath
	 * @param  string $statement
	 * @return void
	 */
	protected function appendUniqueStatement( $filepath, $statement ) {
		$contents = file_get_contents( $filepath );

		if ( $this->stringHasStatement( $contents, $statement ) ) {
			return;
		}

		$eol = $this->getEol( $contents );
		$content_lines = explode( "\n", $contents );
		$last_line = trim( $content_lines[ count( $content_lines ) - 1 ] );

		if ( empty( $last_line ) ) {
			$contents .= $statement . $eol;
		} else {
			$contents .= $eol . $statement;
		}

		file_put_contents( $filepath, $contents );
	}

	/**
	 * Delete files or directories, returning an array of failures.
	 *
	 * @param  array|string $files
	 * @return array
	 */
	protected function delete( $files ) {
		$failures = [];

		// Normalize input to array
		if ( ! is_array( $files ) ) {
			$files = [ $files ];
		}

		foreach ( $files as $file ) {
			if ( ! file_exists( $file ) ) {
				continue;
			}

			$success = false;

			if ( is_dir( $file ) ) {
				$success = $this->deleteDirectory( $file );
			} else {
				$success = @unlink( $file );
			}

			if ( ! $success ) {
				$failures[] = $file;
			}
		}

		return $failures;
	}

	/**
	 * Recursively delete a directory and its contents.
	 *
	 * @param  string $directory
	 * @return boolean
	 */
	protected function deleteDirectory( $directory ) {
		if ( ! is_dir( $directory ) ) {
			return false;
		}

		$files = array_diff( scandir( $directory ), [ '.', '..' ] );

		foreach ( $files as $file ) {
			$path = $this->path( $directory, $file );

			if ( is_dir( $path ) ) {
				$this->deleteDirectory( $path );
			} else {
				@unlink( $path );
			}
		}

		return @rmdir( $directory );
	}
}
