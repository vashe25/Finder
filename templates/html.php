<!DOCTYPE html>
<html>
<head>
	<title>Finder: Results</title>
	<meta charset="utf-8" />
</head>
<body>
	<h1><?/*=htmlspecialchars($this->pattern);*/?></h1>
	<table border="1px" cellspacing="0px">
		<? foreach ($data as $link => $row) :?>
			<tr>
				<td><a target="_blank" href="<?=$link;?>"><?=$link;?></a></td>
				<? if (isset($row["error"])) :?>
					<td colspan="2"><?=$row["error"];?></td>
				<? else :?>
					<td>
						<ol>
							<? foreach ($row["matches"] as $match) :?>
								<? foreach ($match as $value) :?>
									<li><?=htmlspecialchars($value);?></li>
								<? endforeach; ?>
							<? endforeach; ?>
						</ol>
					</td>
					<td>
						<div>Status Code: <?=$row["statusCode"];?></div>
						<? foreach ($row["headers"] as $header => $hvalue) :?>
							<div><?=$header;?>: <?=$hvalue[0];?></div>
						<? endforeach; ?>
					</td>
				<? endif; ?>
			</tr>
		<? endforeach; ?>
	</table>
</body>
</html>