<div class="wrap"><h2>Управление терминами статей — настройки</h2>
<div class="clear"></div>
<div class="postbox">
<h3 class="hndle"><span>Общие настройки</span></h3>
<div class="inside">
<form method="post">

<?php
if($_POST['abc_option_name']) {
update_option('abc_option_name', $_POST['abc_option_name']);
update_option('h1_option_name', $_POST['h1_option_name']);
update_option('at_size_option_name', $_POST['at_size_option_name']);
update_option('at_pageurl_option_name', $_POST['at_pageurl_option_name']);
update_option('at_pagename_option_name', $_POST['at_pagename_option_name']);
update_option('lt_option_name', $_POST['lt_option_name']);
update_option('ds_option_name', $_POST['ds_option_name']);


add_rewrite_endpoint($abc_option_name, EP_PERMALINK | EP_PAGES);    
flush_rewrite_rules();
echo '<div class="updated"><p>Настройки обновлены.</p></div>';
}
?>


    <table class="form-table">
        <tr valign="top">
        <th scope="row">Разделитель в url библиотеки терминов</th>
		<?php $abc_option_name = esc_attr( get_option('abc_option_name') ); if (!$abc_option_name) $abc_option_name = 'termabc'; ?>
        <td><input size=50 type="text" name="abc_option_name" value="<?= $abc_option_name; ?>" /></td>
        </tr>


    <th scope="row">Шаблон H1  библиотеки терминов</th>
		<?php $h1_option_name =  esc_attr( get_option('h1_option_name') ); if (!$h1_option_name) $h1_option_name = '%at_title% на букву %at_letter%'; ?>
        <td><input size=50 type="text" name="h1_option_name" value="<?= $h1_option_name; ?>" /></td>
        </tr>

    <th scope="row">Шаблон title для букв библиотеки терминов</th>
		<?php $lt_option_name =  esc_attr( get_option('lt_option_name') ); if (!$lt_option_name) $lt_option_name = 'Строительные термины на букву %lt_letter%'; ?>
        <td><textarea cols=50 name="lt_option_name" /><?= $lt_option_name; ?></textarea></td>
        </tr>

   <th scope="row">Шаблон description для букв библиотеки терминов</th>
		<?php $ds_option_name =  esc_attr( get_option('ds_option_name') ); if (!$ds_option_name) $ds_option_name = 'Список строительных терминов на букву %ds_letter%'; ?>
        <td><textarea cols=50 name="ds_option_name" /><?= $ds_option_name; ?></textarea></td>
        </tr>


   <th scope="row">Лимит количества терминов в статье</th>
		<?php $at_size_option_name = (int) esc_attr( get_option('at_size_option_name') ); if (!$at_size_option_name) $at_size_option_name = 15; ?>
        <td><input size=50 type="number" name="at_size_option_name" value="<?= $at_size_option_name; ?>" /></td>
        </tr>      

   <th scope="row">Имя страницы - библиотеки терминов</th>
		<?php $at_pagename_option_name = esc_attr( get_option('at_pagename_option_name') ); ?>
        <td><input size=50 type="text" name="at_pagename_option_name" value="<?= $at_pagename_option_name; ?>" /></td>
        </tr>      

   <th scope="row">Slug страницы - библиотеки терминов</th>
		<?php $at_pageurl_option_name = esc_attr( get_option('at_pageurl_option_name') ); ?>
        <td><input size=50 type="text" name="at_pageurl_option_name" value="<?= $at_pageurl_option_name; ?>" /></td>
        </tr>      
		

<!--         <tr valign="top">
        <th scope="row">Some Other Option</th>
        <td><input type="text" name="some_other_option" value="<?php echo esc_attr( get_option('some_other_option') ); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Options, Etc.</th>
        <td><input type="text" name="option_etc" value="<?php echo esc_attr( get_option('option_etc') ); ?>" /></td>
        </tr> -->
    </table>
    
    <?php submit_button(); ?>

</form>



</div>
</div>
</div>
