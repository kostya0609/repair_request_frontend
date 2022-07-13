<?
//$APPLICATION->SetTitle("Претензии НРТ");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$javascript_files = scandir(__DIR__.'/js/');
// echo "<pre>". print_r($javascript_files,1) . "</pre>";
// die();
$css_files = array_reverse(scandir(__DIR__.'/css/'));
foreach ($css_files as $key => $cssfile) {
    if(stripos($cssfile, 'css') !== false){
        echo "<link href=/repair_request/css/".$cssfile." rel=stylesheet>";
    }
}
?>

<script>
    var _userId = <?=$USER->GetID() ?>;
    var _token = "<?=$USER->API_TOKEN; ?>";
</script>

    <div id=app></div>
<?
    foreach ($javascript_files as $key => $jsfile) {
        if(stripos($jsfile, 'js') !== false && stripos($jsfile, 'map') === false){
            echo "<script src='/repair_request/js/".$jsfile."'></script>";
        }
    }

    ?>

    <?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");