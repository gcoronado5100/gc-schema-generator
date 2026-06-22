<?php
/**
 * RED test for gcsg_assemble_graph() (OUT-03).
 *
 * Wave-0 (TDD RED): gcsg_assemble_graph() DOES NOT EXIST yet, so this test
 * exits 1 (RED). It locks the @graph wrapper contract — `@context` ===
 * 'https://schema.org', a numeric-list `@graph` preserving order, and a
 * list-shaped empty `@graph` when no nodes are passed — turning GREEN when
 * Plan 03-02 implements the assembler.
 *
 * Standalone (no PHPUnit): run with `php tests/schema/test-assemble-graph.php`.
 * Exit code 0 = all pass, 1 = a failure.
 *
 * @package GC_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

$root = dirname( __DIR__, 2 ); // tests/<area>/file.php → plugin root.
require_once $root . '/tests/wp-stubs.php';

$failures = array();
$tests    = 0;

function gcsg_assert( $cond, $message, &$tests, &$failures ) {
	$tests++;
	if ( ! $cond ) {
		$failures[] = $message;
		echo "FAIL: $message\n";
	} else {
		echo "ok:   $message\n";
	}
}

// Unit under test — DOES NOT EXIST in Wave 0. RED-guarded require (file_exists)
// so a missing file does not fatal; the first assertion drives a clean exit(1).
$f = $root . '/includes/schema/assemble-graph.php';
if ( file_exists( $f ) ) {
	require_once $f;
}

// --- RED guard ---
gcsg_assert( function_exists( 'gcsg_assemble_graph' ), 'gcsg_assemble_graph is defined', $tests, $failures );
$have = function_exists( 'gcsg_assemble_graph' );

$node_a = array( '@type' => 'AutoDealer', '@id' => 'https://example.com/#/schema/Organization' );
$node_b = array( '@type' => 'BreadcrumbList', '@id' => 'https://example.com/#/schema/Breadcrumb' );

$graph = $have ? gcsg_assemble_graph( array( $node_a, $node_b ) ) : array();

// --- OUT-03: @context === 'https://schema.org' ---
gcsg_assert(
	$have && isset( $graph['@context'] ) && 'https://schema.org' === $graph['@context'],
	'OUT-03: @context === "https://schema.org"',
	$tests,
	$failures
);

// --- OUT-03: @graph is a numeric list of length 2, order preserved ---
$g = isset( $graph['@graph'] ) && is_array( $graph['@graph'] ) ? $graph['@graph'] : null;
gcsg_assert(
	$have && null !== $g && array_values( $g ) === $g && 2 === count( $g ),
	'OUT-03: @graph is a 2-element numeric list',
	$tests,
	$failures
);
gcsg_assert(
	$have && null !== $g && isset( $g[0]['@type'] ) && 'AutoDealer' === $g[0]['@type']
		&& isset( $g[1]['@type'] ) && 'BreadcrumbList' === $g[1]['@type'],
	'OUT-03: @graph preserves node order',
	$tests,
	$failures
);

// --- OUT-03: empty input still yields the wrapper with list-shaped empty @graph ---
$empty = $have ? gcsg_assemble_graph( array() ) : array();
gcsg_assert(
	$have && isset( $empty['@context'] ) && 'https://schema.org' === $empty['@context']
		&& isset( $empty['@graph'] ) && array() === $empty['@graph'],
	'OUT-03: empty input → wrapper with @graph === array()',
	$tests,
	$failures
);

echo "\n$tests assertions, " . count( $failures ) . " failures\n";
exit( empty( $failures ) ? 0 : 1 );
