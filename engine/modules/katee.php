
<?PHP
if(!defined('DATALIFEENGINE'))die("Hacking attempt!");
$cid = isset($parent)?intval($parent):false;
foreach($cat_info as $k=>$v){
    if($cid!==false){
        if($cid==$v['parentid']){
            if($category_id==$k OR $cat_info[$category_id]['parentid']==$k) echo "<li><a href=\"/".get_url($k)."/\">{$v['name']}</a></li>\n";    //подсветка открытой категории, при заданном параметре parent
            else echo "<li><a href=\"/".get_url($k)."/\">{$v['name']}</a></li>";    //просто категория, при заданном параметре parent
        }
    }else if($category_id==$v['parentid']) echo "<li><a href=\"/".get_url($k)."/\">{$v['name']}</a></li>";        //автоматический список подкатегорий из просматриваемой категории
}
?>