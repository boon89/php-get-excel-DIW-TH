<meta http-equiv="Content-Type" content="text/html; charset=windows-874" />
<?php
header('Content-Type: text/html; charset=windows-874');
include('simple_html_dom.php');

function get_html($data = "") {
    $url = 'http://www2.diw.go.th/factory/tumbol.asp';
    $context = stream_context_create(array('http' => array('method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded', 'content' => $data)));
    return file_get_contents($url, false, $context); //html            
}

function download($path, $oldname, $newname) {
    set_time_limit(0);
    $url = 'http://www2.diw.go.th/factory/' . $oldname;
    $file = file_get_contents($url);
    file_put_contents($path . '/' . $newname, $file);
}

$html = get_html('test=0'); //level 0
$objectHtml = str_get_html($html);
$pathExcel = __DIR__ . '/excel';
$zone = array('Bangkok and Central', 'North', 'North East', 'East', 'West', 'Southern');
foreach ($objectHtml->find('a') as $taA) {
    $param_href = explode(',', str_replace(array('javascript:sclk(', ')'), "", $taA->href));
    $level = $param_href[0];
    $postdata = 'level0=' . $level . '&level1=0&level2=0&level3=0';
    $html_province = str_get_html(get_html($postdata));
    $zone_id = $level - 1;

    if (!is_dir($pathExcel . '/' . $zone[$zone_id])) {
        mkdir($pathExcel . '/' . $zone[$zone_id]);
    }
    $folder = $pathExcel . '/' . $zone[$zone_id];

    foreach ($html_province->find('a') as $obj_province) {
        $province_param = explode(',', str_replace(array('javascript:sclk(', ')'), "", $obj_province->href));
        if ($level == $province_param[0]) {
            $provinceName = $obj_province->innertext;
            $postdata = 'level0=' . $level . '&level1=' . $province_param[1] . '&level2=0&level3=0';
            $html_district = str_get_html(get_html($postdata));
            if (!is_dir($folder . '/' . $provinceName)) {
                mkdir($folder . '/' . $provinceName);
            }

            foreach ($html_district->find('a') as $obj_district) {
                $district_param = explode(',', str_replace(array('javascript:sclk(', ')'), "", $obj_district->href));
                if ($district_param[2] != 0) {
                    $districtName = $obj_district->innertext;
                    echo $districtName . '<br/>';
                    $excelName = $district_param[1] . '-' . $district_param[2] . '-.xls';
                    download($folder . '/' . $provinceName, $excelName, $districtName . '.xls');
                }
            }
        }
    }
}
?>


