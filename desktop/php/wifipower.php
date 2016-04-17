<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'wifipower');
$eqLogics = eqLogic::byType('wifipower');
?>

<div class="row row-overflow">
    <div class="col-lg-2 col-md-3 col-sm-4">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter un Wifipower}}</a>
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
                <?php
foreach ($eqLogics as $eqLogic) {
	$opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
	echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '" style="' . $opacity . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
}
?>
           </ul>
       </div>
   </div>

   <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
    <legend>{{Mes wifipower}}
    </legend>
    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
         <center>
            <i class="fa fa-plus-circle" style="font-size : 7em;color:#94ca02;"></i>
        </center>
        <span style="font-size : 1.1em;position:relative; top : 23px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#94ca02"><center>Ajouter</center></span>
    </div>
    <?php
foreach ($eqLogics as $eqLogic) {
	$opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
	echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
	echo "<center>";
	echo '<img src="plugins/wifipower/doc/images/wifipower_icon.png" height="105" width="95" />';
	echo "</center>";
	echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
	echo '</div>';
}
?>
</div>
</div>

<div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
    <div class="row">
        <div class="col-sm-6">
            <form class="form-horizontal">
                <fieldset>
                    <legend><i class="fa fa-arrow-circle-left eqLogicAction cursor" data-action="returnToThumbnailDisplay"></i> {{Général}}  <i class='fa fa-cogs eqLogicAction pull-right cursor expertModeVisible' data-action='configure'></i></legend>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Nom de l'équipement wifipower}}</label>
                        <div class="col-sm-6">
                            <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                            <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement wifipower}}"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" >{{Objet parent}}</label>
                        <div class="col-sm-6">
                            <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                                <option value="">{{Aucun}}</option>
                                <?php
foreach (object::all() as $object) {
	echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
}
?>
                           </select>
                       </div>
                   </div>
                   <div class="form-group">
                    <label class="col-sm-3 control-label">{{Catégorie}}</label>
                    <div class="col-sm-6">
                        <?php
foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
	echo '<label class="checkbox-inline">';
	echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
	echo '</label>';
}
?>
                   </div>
               </div>
               <div class="form-group">
                <label class="col-sm-3 control-label" >{{Activer}}</label>
                <div class="col-sm-9">
                    <input type="checkbox" class="eqLogicAttr bootstrapSwitch" data-label-text="{{Activer}}" data-l1key="isEnable" checked/>
                    <input type="checkbox" class="eqLogicAttr bootstrapSwitch" data-label-text="{{Visible}}" data-l1key="isVisible" checked/>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label">{{Address}}</label>
                <div class="col-sm-9">
                   <div class="input-group">
                    <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ip" />
                    <span class="input-group-addon">:</span>
                    <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="port" value="2000"/>
                    <span class="input-group-addon">/</span>
                    <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="complement" />
                </div>

            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Nom d'utilisateur}}</label>
            <div class="col-sm-3">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="username" />
            </div>
            <label class="col-sm-2 control-label">{{Mot de passe}}</label>
            <div class="col-sm-3">
                <input type="password" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="password" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" >{{Désactiver le pull (push uniquement)}}</label>
            <div class="col-sm-9">
            <input type="checkbox" class="eqLogicAttr bootstrapSwitch" data-l1key="configuration" data-l2key="pushOnly" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{URL de push}}</label>
            <div class="col-sm-9">
                <?php echo network::getNetworkAccess('internal') ?>/plugins/wifipower/core/php/jeeWifipower.php?apikey=<?php echo config::byKey('api'); ?>
            </div>
        </div>
    </fieldset>
</form>
</div>
<div class="col-sm-6">
    <legend>{{Modèle}}</legend>
    <div class="form-group">
        <label class="col-sm-3 control-label">{{Modèle}}</label>
        <div class="col-sm-6">
            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="device">
                <option value="">{{Aucun}}</option>
                <?php
foreach (wifipower::devicesParameters() as $id => $info) {
	echo '<option value="' . $id . '">' . $info['name'] . '</option>';
}
?>
           </select>
       </div>
   </div>
   <center style="height : 255px;">
     <br/><br/><br/>
     <img src="plugins/wifipower/doc/images/wifipower_icon.png" id="img_device" class="img-responsive" style="max-height : 250px;" />
 </center>
</div>
</div>

<legend>{{Commande}}</legend>
<table id="table_cmd" class="table table-bordered table-condensed">
    <thead>
        <tr>
            <th style="width: 300px;">{{Nom}}</th>
            <th style="width: 130px;" class="expertModeVisible">{{Type}}</th>
            <th class="expertModeVisible">{{Logical ID}}</th>
            <th style="width: 200px;">{{Options}}</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<form class="form-horizontal">
    <fieldset>
        <div class="form-actions">
            <a class="btn btn-danger eqLogicAction" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
            <a class="btn btn-success eqLogicAction" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
        </div>
    </fieldset>
</form>

</div>
</div>

<?php include_file('desktop', 'wifipower', 'js', 'wifipower');?>
<?php include_file('core', 'plugin.template', 'js');?>