<?php
/**
 * Minimal, dependency-free XLSX writer for branded church reports.
 * Pure PHP + ZipArchive. Supports: several sheets, logo image per sheet,
 * title band, styled header row, zebra rows, frozen header, auto-filter,
 * column widths, landscape print setup with repeat header and
 * "Confidential" header/footer.
 *
 * @package DCK_Core
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'DCK_XLSX_CLI' ) ) {
	exit;
}

if ( ! class_exists( 'DCK_Xlsx' ) ) {
	class DCK_Xlsx {
		/* Style ids (see styles()). */
		const S_HEAD = 1, S_TITLE = 2, S_SUB = 3, S_BODY = 4, S_ZEBRA = 5, S_GOLD = 6, S_LABEL = 7, S_NUM = 8, S_NUMZ = 9, S_NOTE = 10, S_SECTION = 11;

		private $sheets = array();
		private $logo   = '';
		private $logo_w = 0;
		private $logo_h = 0;
		public $org     = '';
		public $footer  = '';

		public function set_logo( $file ) {
			if ( is_readable( $file ) && function_exists( 'getimagesize' ) ) {
				$i = @getimagesize( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
				if ( $i && IMAGETYPE_PNG === $i[2] ) {
					$this->logo   = $file;
					$this->logo_w = $i[0];
					$this->logo_h = $i[1];
				}
			}
		}

		/**
		 * @param string $name     Sheet tab name.
		 * @param string $title    Big title.
		 * @param string $subtitle Line under the title.
		 * @param array  $head     Column headings.
		 * @param array  $rows     Rows (arrays of scalars; numbers stay numbers).
		 * @param array  $widths   Column widths (characters).
		 * @param array  $blocks   Optional summary blocks: [ [heading, [[label, value], ...]], ... ] rendered instead of a table.
		 */
		public function add_sheet( $name, $title, $subtitle, $head, $rows, $widths = array(), $blocks = array() ) {
			$name = trim( preg_replace( '/[\[\]\*\?\/\\\\:]/', ' ', (string) $name ) );
			$name = function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 31 ) : substr( $name, 0, 31 );
			$base = $name ? $name : 'Sheet';
			$n    = 2;
			$used = array_map( 'strtolower', wp_list_pluck_safe( $this->sheets, 'name' ) );
			while ( in_array( strtolower( $name ), $used, true ) ) {
				$name = substr( $base, 0, 27 ) . ' (' . $n++ . ')';
			}
			$this->sheets[] = compact( 'name', 'title', 'subtitle', 'head', 'rows', 'widths', 'blocks' );
		}

		private static function x( $s ) {
			$s = (string) $s;
			$s = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $s );
			return htmlspecialchars( $s, ENT_XML1 | ENT_QUOTES, 'UTF-8' );
		}

		private static function col( $i ) {
			$s = '';
			for ( $i++; $i > 0; $i = intdiv( $i - 1, 26 ) ) {
				$s = chr( 65 + ( ( $i - 1 ) % 26 ) ) . $s;
			}
			return $s;
		}

		private static function cell( $ref, $v, $style ) {
			if ( is_int( $v ) || is_float( $v ) ) {
				return '<c r="' . $ref . '" s="' . $style . '"><v>' . $v . '</v></c>';
			}
			$v = (string) $v;
			if ( '' === $v ) {
				return '<c r="' . $ref . '" s="' . $style . '"/>';
			}
			// Neutralise spreadsheet formula injection.
			if ( preg_match( '/^[=+\-@\t\r]/', $v ) ) {
				$v = "'" . $v;
			}
			return '<c r="' . $ref . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">' . self::x( $v ) . '</t></is></c>';
		}

		private function sheet_xml( $i, $s ) {
			$cols  = max( 4, count( $s['head'] ), $s['blocks'] ? 4 : 0 );
			$last  = self::col( $cols - 1 );
			$xml   = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
			$xml  .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
			$xml  .= '<sheetPr><tabColor rgb="FF0E1C4D"/><pageSetUpPr fitToPage="1"/></sheetPr>';
			$hr    = 6; // Header row.
			$xml  .= '<dimension ref="A1:' . $last . max( $hr, $hr + count( $s['rows'] ) ) . '"/>';
			$xml  .= '<sheetViews><sheetView workbookViewId="0" showGridLines="0"' . ( 0 === $i ? ' tabSelected="1"' : '' ) . '>';
			if ( ! $s['blocks'] ) {
				$xml .= '<pane ySplit="' . $hr . '" topLeftCell="A' . ( $hr + 1 ) . '" activePane="bottomLeft" state="frozen"/><selection pane="bottomLeft" activeCell="A' . ( $hr + 1 ) . '" sqref="A' . ( $hr + 1 ) . '"/>';
			}
			$xml .= '</sheetView></sheetViews><sheetFormatPr defaultRowHeight="18" customHeight="1"/><cols>';
			for ( $c = 0; $c < $cols; $c++ ) {
				$w    = isset( $s['widths'][ $c ] ) ? (float) $s['widths'][ $c ] : 18;
				$xml .= '<col min="' . ( $c + 1 ) . '" max="' . ( $c + 1 ) . '" width="' . $w . '" customWidth="1"/>';
			}
			$xml .= '</cols><sheetData>';
			$fill = function ( $r, $style ) use ( $cols ) {
				$o = '';
				for ( $c = 1; $c < $cols; $c++ ) {
					$o .= '<c r="' . self::col( $c ) . $r . '" s="' . $style . '"/>';
				}
				return $o;
			};
			// Brand band: logo sits over rows 1-3 (column A-B), title from column C.
			$tc   = $this->logo ? 2 : 0;
			$xml .= '<row r="1" ht="34" customHeight="1">' . self::cell( self::col( $tc ) . '1', $s['title'], self::S_TITLE ) . '</row>';
			$xml .= '<row r="2" ht="20" customHeight="1">' . self::cell( self::col( $tc ) . '2', $s['subtitle'], self::S_SUB ) . '</row>';
			$xml .= '<row r="3" ht="18" customHeight="1">' . self::cell( self::col( $tc ) . '3', $this->org, self::S_NOTE ) . '</row>';
			$xml .= '<row r="4" ht="5" customHeight="1">' . self::cell( 'A4', '', self::S_GOLD ) . $fill( 4, self::S_GOLD ) . '</row>';
			$xml .= '<row r="5" ht="10" customHeight="1"/>';
			$merges = array();
			if ( $tc < $cols - 1 ) {
				foreach ( array( 1, 2, 3 ) as $r ) {
					$merges[] = self::col( $tc ) . $r . ':' . $last . $r;
				}
			}
			$r = $hr;
			if ( $s['blocks'] ) {
				foreach ( $s['blocks'] as $b ) {
					$xml     .= '<row r="' . $r . '" ht="24" customHeight="1">' . self::cell( 'A' . $r, $b[0], self::S_SECTION ) . $fill( $r, self::S_SECTION ) . '</row>';
					$merges[] = 'A' . $r . ':' . $last . $r;
					$r++;
					$z = 0;
					foreach ( $b[1] as $pair ) {
						$st   = ( $z++ % 2 ) ? self::S_ZEBRA : self::S_BODY;
						$xml .= '<row r="' . $r . '" ht="20" customHeight="1">' . self::cell( 'A' . $r, $pair[0], self::S_LABEL ) . self::cell( 'B' . $r, '', $st ) . self::cell( 'C' . $r, $pair[1], ( $z % 2 ) ? self::S_NUM : self::S_NUMZ ) . '</row>';
						$merges[] = 'A' . $r . ':B' . $r;
						$r++;
					}
					$xml .= '<row r="' . $r . '" ht="10" customHeight="1"/>';
					$r++;
				}
			} else {
				$xml .= '<row r="' . $r . '" ht="30" customHeight="1">';
				foreach ( $s['head'] as $c => $h ) {
					$xml .= self::cell( self::col( $c ) . $r, $h, self::S_HEAD );
				}
				$xml .= '</row>';
				foreach ( $s['rows'] as $k => $row ) {
					$r++;
					$st   = ( $k % 2 ) ? self::S_ZEBRA : self::S_BODY;
					$xml .= '<row r="' . $r . '" ht="20" customHeight="1">';
					foreach ( array_values( $row ) as $c => $v ) {
						$xml .= self::cell( self::col( $c ) . $r, $v, $st );
					}
					$xml .= '</row>';
				}
				if ( ! $s['rows'] ) {
					$r++;
					$xml     .= '<row r="' . $r . '">' . self::cell( 'A' . $r, 'No members in this category yet.', self::S_NOTE ) . '</row>';
				}
			}
			$xml .= '</sheetData>';
			if ( ! $s['blocks'] && $s['rows'] ) {
				$xml .= '<autoFilter ref="A' . $hr . ':' . self::col( count( $s['head'] ) - 1 ) . ( $hr + count( $s['rows'] ) ) . '"/>';
			}
			if ( $merges ) {
				$xml .= '<mergeCells count="' . count( $merges ) . '">';
				foreach ( $merges as $m ) {
					$xml .= '<mergeCell ref="' . $m . '"/>';
				}
				$xml .= '</mergeCells>';
			}
			$xml .= '<printOptions horizontalCentered="1"/><pageMargins left="0.4" right="0.4" top="0.6" bottom="0.6" header="0.3" footer="0.3"/>';
			$xml .= '<pageSetup paperSize="9" orientation="landscape" fitToWidth="1" fitToHeight="0"/>';
			$xml .= '<headerFooter><oddHeader>' . self::x( '&L&8' . $this->org . '&C&8&"-,Bold"CONFIDENTIAL - CHURCH MEMBERSHIP DATA&R&8' . $s['name'] ) . '</oddHeader><oddFooter>' . self::x( '&L&8' . $this->footer . '&R&8Page &P of &N' ) . '</oddFooter></headerFooter>';
			if ( $this->logo ) {
				$xml .= '<drawing r:id="rId1"/>';
			}
			$xml .= '</worksheet>';
			return $xml;
		}

		private function drawing_xml() {
			$hpx = 64; // Logo height in px.
			$wpx = (int) round( $this->logo_w * ( $hpx / max( 1, $this->logo_h ) ) );
			$emu = 9525;
			return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
				. '<xdr:oneCellAnchor><xdr:from><xdr:col>0</xdr:col><xdr:colOff>76200</xdr:colOff><xdr:row>0</xdr:row><xdr:rowOff>57150</xdr:rowOff></xdr:from><xdr:ext cx="' . ( $wpx * $emu ) . '" cy="' . ( $hpx * $emu ) . '"/>'
				. '<xdr:pic><xdr:nvPicPr><xdr:cNvPr id="2" name="Logo" descr="Church logo"/><xdr:cNvPicPr><a:picLocks noChangeAspect="1"/></xdr:cNvPicPr></xdr:nvPicPr><xdr:blipFill><a:blip r:embed="rId1"/><a:stretch><a:fillRect/></a:stretch></xdr:blipFill>'
				. '<xdr:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . ( $wpx * $emu ) . '" cy="' . ( $hpx * $emu ) . '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></xdr:spPr></xdr:pic><xdr:clientData/></xdr:oneCellAnchor></xdr:wsDr>';
		}

		private static function styles() {
			$navy = 'FF0E1C4D';
			$gold = 'FFC9A227';
			return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
				. '<fonts count="7">'
				. '<font><sz val="10.5"/><color rgb="FF1F2937"/><name val="Calibri"/><family val="2"/></font>'
				. '<font><b/><sz val="10.5"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>'
				. '<font><b/><sz val="20"/><color rgb="' . $navy . '"/><name val="Georgia"/><family val="1"/></font>'
				. '<font><sz val="11"/><color rgb="FF8A6D12"/><name val="Calibri"/><family val="2"/></font>'
				. '<font><b/><sz val="10.5"/><color rgb="' . $navy . '"/><name val="Calibri"/><family val="2"/></font>'
				. '<font><i/><sz val="9"/><color rgb="FF6B7280"/><name val="Calibri"/><family val="2"/></font>'
				. '<font><b/><sz val="12"/><color rgb="FFFFFFFF"/><name val="Georgia"/><family val="1"/></font>'
				. '</fonts>'
				. '<fills count="6"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
				. '<fill><patternFill patternType="solid"><fgColor rgb="' . $navy . '"/><bgColor indexed="64"/></patternFill></fill>'
				. '<fill><patternFill patternType="solid"><fgColor rgb="' . $gold . '"/><bgColor indexed="64"/></patternFill></fill>'
				. '<fill><patternFill patternType="solid"><fgColor rgb="FFF4F6FB"/><bgColor indexed="64"/></patternFill></fill>'
				. '<fill><patternFill patternType="solid"><fgColor rgb="FF24409A"/><bgColor indexed="64"/></patternFill></fill>'
				. '</fills>'
				. '<borders count="3"><border><left/><right/><top/><bottom/><diagonal/></border>'
				. '<border><left/><right/><top/><bottom style="thin"><color rgb="FFE3E6EE"/></bottom><diagonal/></border>'
				. '<border><left style="thin"><color rgb="FF24409A"/></left><right style="thin"><color rgb="FF24409A"/></right><top/><bottom style="medium"><color rgb="' . $gold . '"/></bottom><diagonal/></border>'
				. '</borders>'
				. '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
				. '<cellXfs count="12">'
				. '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
				. '<xf numFmtId="0" fontId="1" fillId="2" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1" indent="1"/></xf>'
				. '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="center"/></xf>'
				. '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="center"/></xf>'
				. '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1" indent="1"/></xf>'
				. '<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1" indent="1"/></xf>'
				. '<xf numFmtId="0" fontId="0" fillId="3" borderId="0" xfId="0" applyFill="1"/>'
				. '<xf numFmtId="0" fontId="4" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" indent="1"/></xf>'
				. '<xf numFmtId="3" fontId="4" fillId="4" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
				. '<xf numFmtId="3" fontId="4" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
				. '<xf numFmtId="0" fontId="5" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="center"/></xf>'
				. '<xf numFmtId="0" fontId="6" fillId="5" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment vertical="center" indent="1"/></xf>'
				. '</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
		}

		/** Build the workbook to a file path. Returns true on success. */
		public function save( $path ) {
			if ( ! class_exists( 'ZipArchive' ) || ! $this->sheets ) {
				return false;
			}
			$z = new ZipArchive();
			if ( true !== $z->open( $path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
				return false;
			}
			$n   = count( $this->sheets );
			$ct  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/>';
			$ct .= '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/><Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/><Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>';
			$wb   = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><bookViews><workbookView activeTab="0"/></bookViews><sheets>';
			$wbr  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
			$defs = '';
			foreach ( $this->sheets as $i => $s ) {
				$k   = $i + 1;
				$ct .= '<Override PartName="/xl/worksheets/sheet' . $k . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
				$wb .= '<sheet name="' . self::x( $s['name'] ) . '" sheetId="' . $k . '" r:id="rId' . $k . '"/>';
				$wbr .= '<Relationship Id="rId' . $k . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $k . '.xml"/>';
				$z->addFromString( 'xl/worksheets/sheet' . $k . '.xml', $this->sheet_xml( $i, $s ) );
				if ( ! $s['blocks'] ) {
					$defs .= '<definedName name="_xlnm.Print_Titles" localSheetId="' . $i . '">\'' . self::x( str_replace( "'", "''", $s['name'] ) ) . '\'!$6:$6</definedName>';
				}
				if ( $this->logo ) {
					$ct .= '<Override PartName="/xl/drawings/drawing' . $k . '.xml" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>';
					$z->addFromString( 'xl/worksheets/_rels/sheet' . $k . '.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" Target="../drawings/drawing' . $k . '.xml"/></Relationships>' );
					$z->addFromString( 'xl/drawings/drawing' . $k . '.xml', $this->drawing_xml() );
					$z->addFromString( 'xl/drawings/_rels/drawing' . $k . '.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/logo.png"/></Relationships>' );
				}
			}
			$wb  .= '</sheets>' . ( $defs ? '<definedNames>' . $defs . '</definedNames>' : '' ) . '</workbook>';
			$wbr .= '<Relationship Id="rId' . ( $n + 1 ) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
			$ct  .= '</Types>';
			$z->addFromString( '[Content_Types].xml', $ct );
			$z->addFromString( '_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/></Relationships>' );
			$z->addFromString( 'xl/workbook.xml', $wb );
			$z->addFromString( 'xl/_rels/workbook.xml.rels', $wbr );
			$z->addFromString( 'xl/styles.xml', self::styles() );
			$now = gmdate( 'Y-m-d\TH:i:s\Z' );
			$z->addFromString( 'docProps/core.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:title>' . self::x( $this->sheets[0]['title'] ) . '</dc:title><dc:creator>' . self::x( $this->org ) . '</dc:creator><cp:keywords>Confidential</cp:keywords><dcterms:created xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:created></cp:coreProperties>' );
			$z->addFromString( 'docProps/app.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"><Application>' . self::x( $this->org ) . '</Application></Properties>' );
			if ( $this->logo ) {
				$z->addFile( $this->logo, 'xl/media/logo.png' );
			}
			return $z->close();
		}
	}
}

if ( ! function_exists( 'wp_list_pluck_safe' ) ) {
	function wp_list_pluck_safe( $list, $field ) {
		$o = array();
		foreach ( (array) $list as $i ) {
			$o[] = isset( $i[ $field ] ) ? $i[ $field ] : '';
		}
		return $o;
	}
}
