<Link>;<Status>;
<? foreach ($data as $link => $row) :?>
<?=$link;?>;<?=isset($row["error"]) ? $row["error"] : $row["statusCode"];?>;
<? endforeach; ?>