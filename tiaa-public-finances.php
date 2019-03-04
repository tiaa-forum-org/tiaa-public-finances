<?php

/**
 * Plugin Name: TiAA Public Finances Report
 */

add_shortcode('tiaa-public-finances', function(){
	global $wpdb;

	//fetch payments
	$payments = $wpdb->get_results('SELECT amount, sub_id, MONTH(created_at) month, YEAR(created_at) year FROM ' . $wpdb->prefix . 'frm_payments WHERE status = "complete" ORDER BY created_at DESC');

	//loop through and build aggregates
	$months = array();
	$total = array('sum' => 0, 'count' => 0, 'subs_array' => array());
	foreach ($payments as $payment) {
		$key = $payment->year . '-' . $payment->month;
		if (!array_key_exists($key, $months)) $months[$key] = array('sum' => 0, 'count' => 0);
		$total['count']++;
		$months[$key]['count']++;
		$total['sum'] += $payment->amount;
		$months[$key]['sum'] += $payment->amount;
		if ($payment->sub_id > 0) $months[$key]['subs_array'][$payment->sub_id]++;
	}

	return '
	<style>
		table#tiaa-public-finances th, td { text-align: right; }
		table#tiaa-public-finances th:first-child, td:first-child { text-align: left; }
		table#tiaa-public-finances tfoot td { 
			border-top: 2px solid #bbb;
			border-bottom: 0;
			font-weight: bold;
		}
	</style>
	<table id="tiaa-public-finances">
		<thead>
			<tr>
				<th>Month</th>
				<th># Contributions</th>
				<th># Subscriptions</th>
				<th>Total</th>
			</tr>
		</thead>
		<tbody>
		' . implode(array_map(function($key) use($months){
			list($year, $month) = explode('-', $key);
			return '<tr>
				<td>' . date('F Y', mktime(1, 1, 1, $month, 1, $year)) . '</td>
				<td>' . number_format($months[$key]['count']) . '</td>
				<td>' . number_format(count($months[$key]['subs_array'])) . '</td>
				<td>$' . number_format($months[$key]['sum'], 2) . '</td>
			</tr>';
		}, array_keys($months))) . '
		</tbody>
		<tfoot>
			<tr>
				<td>Total</td>
				<td>' . number_format($total['count']) . '</td>
				<td></td>
				<td>$' . number_format($total['sum'], 2) . '</td>
			</tr>
		</tfoot>
	</table>';
});