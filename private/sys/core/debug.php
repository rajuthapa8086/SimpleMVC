<?php

/**
 * Class debug
 */
 
class debug {
/**
 * CLASS PROPERTIES AND METHODS
 */
	private function __construct() {
	}

/**
 * STATIC PROPERTIES AND METHODS
 */
	private static $messages = array();
	public static function set($type, $key, $value) {
		if (DEBUG) {
			$check = self::_get($type);
			if (!empty($check)) {
				self::$messages[$type] = $check;
				if (array_key_exists($key, self::$messages[$type])) {
					unset(self::$messages[$type][$key]);
				}
				self::$messages[$type][$key] = $value;
			} else {
				self::$messages[$type][$key] = $value;
			}
			session::set("debug", self::$messages);	
		} else {
			session::destroy("debug");
		}
	}
			
	private static function _get($type) {
		$messages = session::get("debug");
		if ($messages != false) {
			if (array_key_exists($type, $messages)) {
				return $messages[$type];
			}
		}
		return array();
	}
	
	public static function destroy($type) {
		unset(self::$messages[$type]);
		session::set("debug", self::$messages);	
	}
	
	public static function show() {
		$sql_debug = self::_get('sql');
		$router_debug = self::_get('router');
		$session_debug = self::_get('session');
?>
<style type="text/css">
#d_e_b_u_g {
	background: #000;
	padding: 10px 0;
	font-size: 20px;
	color: #FFF;
	height: 200px;
	overflow: auto;
}
#d_e_b_u_g table {
	width: 100%;
}
#d_e_b_u_g p,
#d_e_b_u_g h1 {
	padding: 20px;
	background: #333;
	color: #AC0;
	border-bottom: solid 5px #555;
}
#d_e_b_u_g div {
	margin: 0px 10px 10px;
	border: solid 5px #555;
}
#d_e_b_u_g table tr th,
#d_e_b_u_g table tr td {
	text-align: left;
	background: none;
	border: none;
	padding: 10px;
	border: solid 1px #000;
	background: #444;
	color: #FFF;
	font-size: 16px;
}
#d_e_b_u_g table tr td {
	font-family: "Courier New", Courier;
}
#d_e_b_u_g table tr th {
	color: #ACE;
	font-weight: normal;
	background: #333;
	text-transform: uppercase;
}
#d_e_b_u_g table tr:hover td {
	background: #000;
	color: #ACE;
}
#d_e_b_u_g strong {
	color: #AC0;
	letter_spacing: 1px;
}
</style>
<div id="d_e_b_u_g">
<?php if (!empty($sql_debug)): ?>
<div>
	<h1>SQL Debug</h1>
	<table>
		<tr>
			<th>Called Method</th>
			<th>SQL</th>
		</tr>
		<?php foreach($sql_debug as $skey => $svalue): ?>
		<tr>
			<td><strong><?php echo $skey; ?></strong></td>
			<td><?php echo $svalue; ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>
<?php endif; ?>
<?php if (!empty($router_debug)): ?>
<div>
	<h1>Router Debug</h1>
	<table>
		<tr>
			<th>Router Key</th>
			<th>Router Value</th>
		</tr>
		<?php foreach($router_debug['router'] as $dkey => $dvalue): ?>
		<tr>
			<td><?php echo $dkey; ?></td>
			<td><?php echo nl2br(utils::text_array(array($dvalue))); ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>
<?php endif; ?>
<?php if (!empty($session_debug)): ?>
<div>
	<h1>Session Debug</h1>
	<table>
		<tr>
			<th>Session Key</th>
			<th>Session Value</th>
		</tr>
		<?php foreach($session_debug['session'] as $skey => $svalue): ?>
		<?php if ($skey != "debug"): ?>
		<tr>
			<td><?php echo $skey; ?></td>
			<td><?php echo nl2br(utils::text_array(array($svalue))); ?></td>
		</tr>
		<?php endif; ?>
		<?php endforeach; ?>
	</table>
</div>
<?php endif; ?>
</div>
<?php
	}
	
}
