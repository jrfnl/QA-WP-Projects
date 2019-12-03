<?php
/**
 * WPQA Report for PHP_CodeSniffer.
 *
 * @package WPQA
 * @author  Juliette Reinders Folmer <qawp_projects_nospam@adviesenzo.nl>
 */

namespace WPQA;

use PHP_CodeSniffer\Reports\Report;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util;
use PHP_CodeSniffer\Util\Tokens;
use PHP_CodeSniffer\Exceptions\DeepExitException;

/**
 * WordPress Plugin/Theme Quality Analysis report.
 *
 * Future ideas:
 * -> Add colors to the results to indicate areas which need attention.
 *    https://stackoverflow.com/questions/5947742/how-to-change-the-output-color-of-echo-in-linux#answer-28938235
 *
 * -> Check whether the recommended config vars have been set and don't report in a category when they haven't + add a "Problems" section add the top of the report notifying about the missing config vars
 *
 * -> Add a mark for documentation
 *
 * -> Maybe add a column with nr of files affected by issues in each category ?
 */
class WPQA implements Report {

	/**
	 * Categorization of the sniffs in the WP QA Basic ruleset.
	 *
	 * {@internal Any time, the WP-QA-Basic ruleset is updated, this table needs updating too!}}
	 *
	 * @var array
	 */
	protected $categorization_basic = array(
		'Generic.PHP.Syntax'                                    => 'hard errors',
		'Generic.Files.ByteOrderMark'                           => 'hard errors',

		'Squiz.PHP.Eval'                                        => 'dangerous code',
		'PHPCompatibility.ParameterValues.RemovedPCREModifiers' => 'dangerous code',
		'Generic.PHP.BacktickOperator'                          => 'dangerous code',

		'Generic.Metrics.CyclomaticComplexity'                  => 'untestable code',
		'Generic.Metrics.NestingLevel'                          => 'untestable code',

		'Generic.Functions.CallTimePassByReference'             => 'outdated code',
		'Generic.PHP.DisallowShortOpenTag'                      => 'outdated code',
		'Generic.PHP.DisallowAlternativePHPTags'                => 'outdated code',
		'Generic.PHP.ForbiddenFunctions'                        => 'outdated code',
		'WordPress.PHP.RestrictedPHPFunctions'                  => 'outdated code',
		'WordPress.PHP.POSIXFunctions'                          => 'outdated code',
		'Generic.PHP.DeprecatedFunctions'                       => 'outdated code',

		'WordPress.PHP.DontExtract'                             => 'messy code',
		'WordPress.CodeAnalysis.AssignmentInCondition'          => 'messy code',
		'Generic.Classes.DuplicateClassName'                    => 'messy code',
		'Generic.CodeAnalysis.JumbledIncrementer'               => 'messy code',
		'Squiz.Functions.FunctionDuplicateArgument'             => 'messy code',
		'Generic.PHP.DiscourageGoto'                            => 'messy code',
		'Squiz.Scope.StaticThisUsage'                           => 'messy code',

		'PHPCompatibility'                                      => 'incompatible code - PHP',

		'WordPress.WP.DeprecatedFunctions'                      => 'incompatible code - WP',
		'WordPress.WP.DeprecatedClasses'                        => 'incompatible code - WP',
		'WordPress.WP.DeprecatedParameters'                     => 'incompatible code - WP',
		'WordPress.WP.DeprecatedParameterValues'                => 'incompatible code - WP',

		'WordPress.WP.GlobalVariablesOverrride'                 => 'potentially conflicting code',
		'WordPress.WP.EnqueuedResources'                        => 'potentially conflicting code',
		'WordPress.NamingConventions.PrefixAllGlobals'          => 'potentially conflicting code',
		'WordPress.PHP.IniSet'                                  => 'potentially conflicting code',
	);

	/**
	 * Categorization of the sniffs in the WP QA Strict ruleset.
	 *
	 * {@internal Any time, the WP-QA-Strict ruleset is updated, this table needs updating too!}}
	 *
	 * @var array
	 */
	protected $categorization_strict = array(
		'WordPress.DB.PreparedSQL'                         => 'potentially insecure code',
		'WordPress.Security.EscapeOutput'                  => 'potentially insecure code',
		'WordPress.Security.NonceVerification'             => 'potentially insecure code',
		'WordPress.Security.ValidatedSanitizedInput'       => 'potentially insecure code',
		'WordPress.Security.PluginMenuSlug'                => 'potentially insecure code',
		'WordPress.Security.SafeRedirect'                  => 'potentially insecure code',

		'WordPress.WP.I18n'                                => 'localization issues',
		'WordPress.CodeAnalysis.EscapedNotTranslated'      => 'localization issues',

		'Squiz.Scope.MethodScope'                          => 'outdated code',
		'Squiz.Scope.MemberVarScope'                       => 'outdated code',
		'WordPress.PHP.TypeCasts'                          => 'outdated code',

		'WordPress.PHP.StrictComparisons'                  => 'potentially buggy code',
		'WordPress.PHP.StrictInArray'                      => 'potentially buggy code',
		'WordPress.DB.PreparedSQLPlaceholders'             => 'potentially buggy code',
		'WordPress.PHP.PregQuoteDelimiter'                 => 'potentially buggy code',
		'WordPress.PHP.NoSilencedErrors'                   => 'potentially buggy code',
		'WordPress.NamingConventions.ValidPostTypeSlug'    => 'potentially buggy code',

		'WordPress.CodeAnalysis.EmptyStatement'            => 'sloppy code',
		'Generic.CodeAnalysis.EmptyStatement'              => 'sloppy code',
		'Generic.CodeAnalysis.ForLoopWithTestFunctionCall' => 'sloppy code',
		'Squiz.PHP.DisallowSizeFunctionsInLoops'           => 'sloppy code',
		'Generic.CodeAnalysis.UnconditionalIfStatement'    => 'sloppy code',

		'WordPress.DB.RestrictedFunctions'                 => 'discouraged code',
		'WordPress.DB.RestrictedClasses'                   => 'discouraged code',
		'WordPress.PHP.DevelopmentFunctions'               => 'discouraged code',
		'WordPress.PHP.DiscouragedPHPFunctions'            => 'discouraged code',
		'WordPress.WP.AlternativeFunctions'                => 'discouraged code',
		'WordPress.WP.DiscouragedConstants'                => 'discouraged code',
		'WordPress.WP.DiscouragedFunctions'                => 'discouraged code',

		'Squiz.Functions.GlobalFunction'                   => 'outdated code',
	);

	/**
	 * The categorization used for this report.
	 *
	 * This will either be basic or basic + strict when the WP-QA-Strict ruleset is used.
	 * The array is set the first time categorization is needed.
	 *
	 * @var array
	 */
	protected $categorization = array();

	/**
	 * Base array for file stats.
	 *
	 * @var array
	 */
	protected $baseFileStats = array(
		'TotalLines'   => 0,
		'CodeLines'    => 0,
		'CommentLines' => 0,
		'BlankLines'   => 0,
		'Files'        => 0,
	);

	/**
	 * Base array for category stats.
	 *
	 * @var array
	 */
	protected $baseCatStats = array(
		'errors'   => 0,
		'warnings' => 0,
	);

	/**
	 * Report width default.
	 *
	 * @var int
	 */
	protected $report_width = 80;

	/**
	 * Generate a partial report for a single processed file.
	 *
	 * Function should return TRUE if it printed or stored data about the file
	 * and FALSE if it ignored the file. Returning TRUE indicates that the file and
	 * its data should be counted in the grand totals.
	 *
	 * @param array                 $report      Prepared report data.
	 * @param \PHP_CodeSniffer\File $phpcsFile   The file being reported on.
	 * @param bool                  $showSources Show sources? Defaults to false.
	 * @param int                   $width       Maximum allowed line width. Defaults to 80.
	 *
	 * @throws DeepExitException When this report is not used with one of the QA rulesets.
	 *
	 * @return bool
	 */
	public function generateFileReport( $report, File $phpcsFile, $showSources = false, $width = 80 ) {
		/*
		 * Set the $categorization property if needed.
		 */
		if ( empty( $this->categorization ) ) {
			$standards = $phpcsFile->config->standards;
			if ( in_array( 'WP-QA-Strict', $standards, true ) ) {
				$this->categorization = $this->categorization_basic + $this->categorization_strict;
			} elseif ( in_array( 'WP-QA-Basic', $standards, true ) ) {
				$this->categorization = $this->categorization_basic;
			} else {
				$error = 'ERROR: The "WPQA" report can only be used with the "WP-QA-Basic" and "WP-QA-Strict" standards.' . PHP_EOL;
				throw new DeepExitException( $error, 3 );
			}
		}

		/*
		 * Determine the file type.
		 *
		 * @TODO change from plugin to... ?
		 * Maybe using $phpcsFile->config->files ?
		 */
		$fileType = 'plugin';
		if ( stripos( $report['filename'], DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR ) !== false ) {
			$fileType = 'vendor';
		} elseif ( stripos( $report['filename'], DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR ) !== false ) {
			$fileType = 'test';
		} elseif ( stripos( $report['filename'], DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR ) !== false ) {
			$fileType = 'test';
		}

		/*
		 * Prepare basic LOC stats for the current file.
		 */
		$tokens       = $phpcsFile->getTokens();
		$totalLines   = $tokens[ ( $phpcsFile->numTokens - 1 ) ]['line'];
		$blankLines   = 0;
		$commentLines = 0;
		$codeLines    = 0;
		$hasCode      = false;
		$hasComment   = false;
		$lastLine     = 0;
		foreach ( $tokens as $token ) {
			if ( $token['line'] !== $lastLine ) {
				if ( $hasCode === true ) {
					++$codeLines;
				} elseif ( $hasComment === true ) {
					++$commentLines;
				} else {
					++$blankLines;
				}

				$hasCode    = false;
				$hasComment = false;
				$lastLine   = $token['line'];
			}

			if ( $token['code'] === T_WHITESPACE ) {
				continue;
			}

			if ( isset( Tokens::$emptyTokens[ $token['code'] ] ) === false ) {
				$hasCode = true;
			} else {
				$hasComment = true;
			}
		}

		// Record the last line if there was anything on it.
		if ( $hasCode === true ) {
			++$codeLines;
		} elseif ( $hasComment === true ) {
			++$commentLines;
		}

		echo 'File info>>',
			$fileType, '>>',
			$totalLines, '>>',
			$codeLines, '>>',
			$commentLines, '>>',
			$blankLines, PHP_EOL;

		/*
		 * Prepare info on errors/warnings per category.
		 */
		$categories = array();
		foreach ( $report['messages'] as $line => $lineErrors ) {
			foreach ( $lineErrors as $column => $colErrors ) {
				foreach ( $colErrors as $error ) {
					foreach ( $this->categorization as $sniff => $qaCategory ) {
						if ( strpos( $error['source'], $sniff ) === 0 ) {
							$category = $qaCategory;
							break;
						}
					}

					if ( ! isset( $category ) ) {
						continue;
					}

					if ( isset( $categories[ $category ] ) === false ) {
						$categories[ $category ] = array(
							'ERROR'   => 0,
							'WARNING' => 0,
						);
					}

					++$categories[ $category ][ $error['type'] ];
				}
			}
		}

		foreach ( $categories as $category => $data ) {
			echo 'Cat info>>',
				$fileType, '>>',
				$category, '>>',
				$data['ERROR'], '>>',
				$data['WARNING'], PHP_EOL;
		}

		return true;
	}

	/**
	 * Generates a summary of errors and warnings for each file processed.
	 *
	 * @param string $cachedData    Any partial report data that was returned from
	 *                              generateFileReport during the run.
	 * @param int    $totalFiles    Total number of files processed during the run.
	 * @param int    $totalErrors   Total number of errors found during the run.
	 * @param int    $totalWarnings Total number of warnings found during the run.
	 * @param int    $totalFixable  Total number of problems that can be fixed.
	 * @param bool   $showSources   Show sources? Defaults to false.
	 * @param int    $width         Maximum allowed line width.
	 * @param bool   $interactive   Are we running in interactive mode? Defaults to false.
	 * @param bool   $toScreen      Is the report being printed to screen? Defaults to true.
	 *
	 * @return void
	 */
	public function generate(
		$cachedData,
		$totalFiles,
		$totalErrors,
		$totalWarnings,
		$totalFixable,
		$showSources = false,
		$width = 80,
		$interactive = false,
		$toScreen = true
	) {

		$this->report_width = max( $width, 78 );

		$lines = explode( PHP_EOL, $cachedData );
		array_pop( $lines );

		if ( empty( $lines ) ) {
			return;
		}

		/*
		 * Parse the data collected about the individual files and put it in arrays.
		 */
		$catStats  = array();
		$totals    = $this->baseFileStats;
		$fileStats = array(
			'plugin' => $this->baseFileStats,
			'test'   => $this->baseFileStats,
			'vendor' => $this->baseFileStats,
		);

		foreach ( $lines as $line ) {
			$parts = explode( '>>', $line );
			if ( $parts[0] === 'File info' ) {
				$fileStats[ $parts[1] ]['TotalLines']   += $parts[2];
				$fileStats[ $parts[1] ]['CodeLines']    += $parts[3];
				$fileStats[ $parts[1] ]['CommentLines'] += $parts[4];
				$fileStats[ $parts[1] ]['BlankLines']   += $parts[5];
				++$fileStats[ $parts[1] ]['Files'];
			} elseif ( $parts[0] === 'Cat info' ) {
				if ( isset( $catStats[ $parts[1] ][ $parts[2] ] ) === false ) {
					$catStats[ $parts[1] ][ $parts[2] ] = $this->baseCatStats;
				}

				$catStats[ $parts[1] ][ $parts[2] ]['errors']   += $parts[3];
				$catStats[ $parts[1] ][ $parts[2] ]['warnings'] += $parts[4];
			}
		}

		/*
		 * Output the report header.
		 */
		echo PHP_EOL;
		$this->echo_line( strtoupper( 'WordPress Project QA Report' ) );
		$this->echo_line( str_repeat( '=', $this->report_width ) );

		$this->echo_line( 'This report highlights potential problem areas in the scanned code.' );
		$this->echo_line( 'It is advisable to let an experienced developer assess whether the highlighted issues are actually problematic.' );
		$this->echo_line( 'This report is intended solely as soft advise, not as a hard judgement.' );

		$this->echo_line( str_repeat( '-', $this->report_width ) );

		echo PHP_EOL . PHP_EOL;

		/*
		 * Output the file stats.
		 */
		$this->echo_line( str_repeat( '=', $this->report_width ) );
		$this->echo_line( strtoupper( 'General information about the analysed code base.' ) );
		$this->echo_line( str_repeat( '=', $this->report_width ) );
		echo PHP_EOL;

		$this->echo_line( 'File Type |  Files  |     Lines *  |        Code         |       Comments' );
		$this->echo_line( str_repeat( '-', 78 ) );

		// @TODO: account for case where there are no comments and no blank lines, i.e. 100%
		$show_test_notice = false;
		foreach ( $fileStats as $type => $stats ) {
			$realTotalLOC                        = ( $stats['TotalLines'] - $stats['BlankLines'] );
			$fileStats[ $type ]['TotalNonBlank'] = $realTotalLOC;
			if ( $realTotalLOC > 0 ) {
				$line = sprintf(
					'%-9s | %7d | %12d | %11d (%4.1f%%) | %11d (%4.1f%%)',
					$type,
					$stats['Files'],
					$realTotalLOC,
					$stats['CodeLines'],
					( ( $stats['CodeLines'] / $realTotalLOC ) * 100 ),
					$stats['CommentLines'],
					( ( $stats['CommentLines'] / $realTotalLOC ) * 100 )
				);
			} else {
				$line = sprintf(
					'%-9s | %7d | %12d | %19s |',
					$type,
					$stats['Files'],
					$stats['TotalLines'],
					' '
				);
			}

			$this->echo_line( $line );

			if ( $type === 'test' && $realTotalLOC === 0 ) {
				$show_test_notice = true;
			}

			$totals['TotalLines']   += $stats['TotalLines'];
			$totals['CodeLines']    += $stats['CodeLines'];
			$totals['CommentLines'] += $stats['CommentLines'];
			$totals['BlankLines']   += $stats['BlankLines'];
			$totals['Files']        += $stats['Files'];
		}

		$this->echo_line( str_repeat( '=', 78 ) );

		$realTotalLOC            = ( $totals['TotalLines'] - $totals['BlankLines'] );
		$totals['TotalNonBlank'] = $realTotalLOC;
		if ( $realTotalLOC > 0 ) {
			$line = sprintf(
				'%-9s | %7d | %12d | %11d (%4.1f%%) | %11d (%4.1f%%)',
				'Totals',
				$totals['Files'],
				$realTotalLOC,
				$totals['CodeLines'],
				( ( $totals['CodeLines'] / $realTotalLOC ) * 100 ),
				$totals['CommentLines'],
				( ( $totals['CommentLines'] / $realTotalLOC ) * 100 )
			);
		} else {
			$line = sprintf(
				'%-9s | %7d | %12d | %19s |',
				'Totals',
				$totals['Files'],
				$totals['TotalLines'],
				' '
			);
		}

		$this->echo_line( $line );
		echo PHP_EOL;

		$this->echo_line( '* These stats exclude all blank lines.', 2, "\033[3m" );
		if ( $show_test_notice === true ) {
			$this->echo_line( '* If there are no test files, this doesn\'t necessarily mean that the code isn\'t fully tested. It just means that the copy of the code you are analyzing does not include the test files.', 2, "\033[3m" );
			$this->echo_line( '  You may want to try and find the theme/plugin on GitHub and check if there is a "test(s)" directory in the GH repository.', 2, "\033[3m" );
		}
		echo PHP_EOL . PHP_EOL;

		/*
		 * Output the findings.
		 */
		$this->echo_line( str_repeat( '=', $this->report_width ) );
		$this->echo_line( strtoupper( 'Issues found per category *' ) );
		$this->echo_line( str_repeat( '=', $this->report_width ) );
		echo PHP_EOL;

		$categories = array_unique( $this->categorization );

		$maxCatLen = 0;
		foreach ( $categories as $category ) {
			$maxCatLen = max( strlen( $category ), $maxCatLen );
		}

		$table_header        = ' | Errors   | % of LOC | Warnings | % of LOC';
		$table_header_length = $maxCatLen + strlen( $table_header );

		foreach ( $catStats as $type => $issueCats ) {
			// Print table header.
			$this->echo_line( str_pad( strtoupper( $type . ' Files' ), $maxCatLen ) . $table_header );
			$this->echo_line( str_repeat( '-', $table_header_length ) );

			$typeTotals   = $this->baseCatStats;
			$realTotalLOC = $fileStats[ $type ]['TotalNonBlank'];

			foreach ( $categories as $category ) {
				if ( isset( $issueCats[ $category ] ) === false ) {
					// Print 0 line.
					$line = sprintf(
						'%1$s | %2$s | %3$s | %2$s | %3$s',
						str_pad( $category, $maxCatLen ),
						str_pad( '-', 8, ' ', STR_PAD_BOTH ),
						str_repeat( ' ', 8 )
					);
				} else {
					// Print error line.
					$line   = array();
					$line[] = str_pad( $category, $maxCatLen );
					if ( isset( $issueCats[ $category ]['errors'] ) && $issueCats[ $category ]['errors'] > 0 ) {
						$line[] = str_pad( $issueCats[ $category ]['errors'], 8, ' ', STR_PAD_LEFT );
						$line[] = sprintf( '%7.2f%%', ( ( $issueCats[ $category ]['errors'] / $realTotalLOC ) * 100 ) );

						$typeTotals['errors'] += $issueCats[ $category ]['errors'];
					} else {
						$line[] = str_pad( '-', 8, ' ', STR_PAD_BOTH );
						$line[] = str_repeat( ' ', 8 );
					}

					if ( isset( $issueCats[ $category ]['warnings'] ) && $issueCats[ $category ]['warnings'] > 0 ) {
						$line[] = str_pad( $issueCats[ $category ]['warnings'], 8, ' ', STR_PAD_LEFT );
						$line[] = sprintf( '%7.2f%%', ( ( $issueCats[ $category ]['warnings'] / $realTotalLOC ) * 100 ) );

						$typeTotals['warnings'] += $issueCats[ $category ]['warnings'];
					} else {
						$line[] = str_pad( '-', 8, ' ', STR_PAD_BOTH );
						$line[] = str_repeat( ' ', 8 );
					}

					$line = implode( ' | ', $line );
				}

				$this->echo_line( $line );
			}

			// Print totals.
			$this->echo_line( str_repeat( '=', $table_header_length ) );

			$line   = array();
			$line[] = str_pad( 'Total', $maxCatLen );
			if ( $typeTotals['errors'] > 0 ) {
				$line[] = str_pad( $typeTotals['errors'], 8, ' ', STR_PAD_LEFT );
				$line[] = sprintf( '%7.2f%%', ( ( $typeTotals['errors'] / $realTotalLOC ) * 100 ) );
			} else {
				$line[] = str_pad( '-', 8, ' ', STR_PAD_BOTH );
				$line[] = str_repeat( ' ', 8 );
			}

			if ( $typeTotals['warnings'] > 0 ) {
				$line[] = str_pad( $typeTotals['warnings'], 8, ' ', STR_PAD_LEFT );
				$line[] = sprintf( '%7.2f%%', ( ( $typeTotals['warnings'] / $realTotalLOC ) * 100 ) );
			} else {
				$line[] = str_pad( '-', 8, ' ', STR_PAD_BOTH );
				$line[] = str_repeat( ' ', 8 );
			}

			$line = implode( ' | ', $line );

			$this->echo_line( $line );

			echo PHP_EOL . PHP_EOL;
		}

		$this->echo_line( '* To see the details of all identified issues, run the report with "--report-full" added to the command.', 2, "\033[3m" );
		$this->echo_line( str_repeat( '-', $this->report_width ) );

		/*
		 * Show a notice if very few problems are found...
		 */
		$totalIssues         = ( $totalErrors + $totalWarnings );
		$relativeTotalIssues = ( ( $totalIssues / $totals['TotalNonBlank'] ) * 100 );
		if ( $totalIssues === 0 || $relativeTotalIssues < 0.2 ) {
			echo PHP_EOL;
			$this->echo_line( 'The amount of potential issues found is extraordinarily low.', 0, "\033[31m" );
			$this->echo_line( 'Are you sure you are scanning a project containing PHP code ?', 0, "\033[3m" );
			$this->echo_line( 'Try running the report again with "--ignore-annotations" added to the command.' );
			$this->echo_line( 'If the difference between the report you see then and now is small, that\'s ok. If the difference is large, the devs are probably "cheating"...' );
			$this->echo_line( str_repeat( '-', $this->report_width ) );
		}

		echo PHP_EOL;

		if ( $toScreen === true && $interactive === false ) {
			Util\Timing::printRunTime();
		}
	}

	/**
	 * Print text to screen.
	 *
	 * @param string $line   The text to print.
	 * @param int    $indent Whether the lines after the first line of a multi-line text
	 *                       should be indented and by how much.
	 * @param string $style  Linux console styling to apply to the text.
	 *
	 * @return void
	 */
	protected function echo_line( $line, $indent = 0, $style = "\033[1m" ) {

		$lines = array();
		$width = ( $this->report_width - $indent );

		if ( strlen( $line ) > $width ) {
			$line  = wordwrap( $line, $width, '>>>' );
			$lines = explode( '>>>', $line );

			$count = count( $lines );
			if ( $count > 1 ) {
				for ( $i = 1; $i < $count; $i++ ) {
					$lines[ $i ] = str_repeat( ' ', $indent ) . $lines[ $i ];
				}
			}
		} else {
			$lines[] = $line;
		}

		foreach ( $lines as $line ) {
			echo $style, $line, "\033[0m", PHP_EOL;
		}
	}
}
