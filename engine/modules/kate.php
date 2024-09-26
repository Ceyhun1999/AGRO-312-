

<?PHP
if(!defined('DATALIFEENGINE'))die("Hacking attempt!");
$ll = $_REQUEST['lang'];
$cid = isset($parent)?intval($parent):false;
foreach($cat_info as $k=>$v){
    if($cid!==false){
        if($cid==$v['parentid']){
            if($category_id==$k OR $cat_info[$category_id]['parentid']==$k) echo " <div class='categories__list-item'>
                                <a href=\"/".get_url($k)."/\" style='background: url({$v['icon']})center/cover;' >
                                    <div style='display: flex; flex-direction: column; gap: 10px'>
                                        <h3 class='categories__list-title first'>{lang_АГРО 312}</h3>
                                        <h3 class='categories__list-title second'>{$v['name']}</h3>
                                    </div>
                                </a>
                            </div>\n";    //подсветка открытой категории, при заданном параметре parent
            else echo "<div class='categories__list-item'>
                                <a href=\"/".get_url($k)."/\" style='background: url({$v['icon']})center/cover;' >
                                    <div style='display: flex; flex-direction: column; gap: 10px'>
                                        <h3 class='categories__list-title first'>{lang_АГРО 312}</h3>
                                        <h3 class='categories__list-title second'>{$v['name']}</h3>
                                    </div>
                                </a>
                            </div>\n";    //просто категория, при заданном параметре parent
        }
    }else if($category_id==$v['parentid']) echo "<div class='categories__list-item'>
                                <a href=\"/".get_url($k)."/\" style='background: url({$v['icon']})center/cover;' >
                                    <div style='display: flex; flex-direction: column; gap: 10px'>
                                        <h3 class='categories__list-title first'>{lang_АГРО 312}</h3>
                                        <h3 class='categories__list-title second'>{$v['name']}</h3>
                                    </div>
                                </a>
                            </div>\n";        //автоматический список подкатегорий из просматриваемой категории
}
?>