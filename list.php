<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>WordCamp Sofia</title>
	<style>
	body {
		font-family: 'Helvetica Neue';
	}
	table {
		border-spacing: 0;
	}
	th {
		text-align: left;
	}
	td.coupon {
		font-size: 75%;
		color: #aaa;
	}
	th, td {
		padding: 4px 10px;
	}
	tr:nth-child(even) {background: #ddd}
	</style>
</head>
<body>
<table>
	<thead>
		<tr>
			<th>ID</th>
			<th>Име</th>
			<th>Тениска</th>
			<th>Трябва да плати?</th>
			<th>Купон</th>
		</tr>
	</thead>
	<tbody>
<?php foreach( $people as $person ): ?>
		<tr>
			<td><?php echo $person->id; ?></td>
			<td><?php echo $person->first . ' ' . $person->last; ?></td>
			<td><?php echo $person->size; ?></td>
			<td><?php echo false === strpos( $person->coupon, 'not-paid' )? '' : '<b>$$$</b>'; ?></td>
			<td class="coupon"><?php echo $person->coupon; ?></td>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>
</body>
</html>
