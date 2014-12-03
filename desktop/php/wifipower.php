<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'wifipower');
?>

<div class="row row-overflow">
    <div class="col-lg-2 col-md-3 col-sm-4">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter un wifipower}}</a>
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
                <?php
                foreach (eqLogic::byType('wifipower') as $eqLogic) {
                    echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName() . '</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>
    <div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
        <form class="form-horizontal">
            <fieldset>
                <legend>{{Général}}</legend>
                <div class="form-group">
                    <label class="col-sm-3 control-label">{{Nom de l'équipement wifipower}}</label>
                    <div class="col-sm-3">
                        <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                        <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement wifipower}}"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" >{{Objet parent}}</label>
                    <div class="col-sm-3">
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
                    <div class="col-sm-3">
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
                    <div class="col-sm-1">
                        <input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" size="16" checked/>
                    </div>
                    <label class="col-sm-3 control-label" >{{Visible}}</label>
                    <div class="col-sm-1">
                        <input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">{{Modèle}}</label>
                    <div class="col-sm-3">
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
                <div class="form-group">
                    <label class="col-sm-3 control-label">{{IP}}</label>
                    <div class="col-sm-3">
                        <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ip" />
                    </div>
                </div>
                 <div class="form-group">
                    <label class="col-sm-3 control-label">{{Nom d'utilisateur}}</label>
                    <div class="col-sm-3">
                        <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="username" />
                    </div>
                </div>
                 <div class="form-group">
                    <label class="col-sm-3 control-label">{{Mot de passe}}</label>
                    <div class="col-sm-3">
                        <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="password" />
                    </div>
                </div>
                <div class="form-group expertModeVisible">
                    <label class="col-sm-3 control-label">{{Port}}</label>
                    <div class="col-sm-3">
                        <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="port" />
                    </div>
                </div>
            </fieldset> 
        </form>

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

<?php include_file('desktop', 'wifipower', 'js', 'wifipower'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>