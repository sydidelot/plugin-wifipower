<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
log::add('wifipower', 'debug', 'Demande push recu de : ' . getClientIp());
if (!jeedom::apiAccess(init('apikey'))) {
	connection::failed();
	echo 'Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action';
	die();
}
$wifipower = wifipower::byLogicalId(getClientIp(), 'wifipower');

if (!is_object($wifipower)) {
	log::add('wifipower', 'debug', 'Wifipower inconnu');
	die();
}
$request = file_get_contents("php://input");
log::add('wifipower', 'debug', 'php input : ' . print_r($request, true));
log::add('wifipower', 'debug', 'POST : ' . print_r($_POST, true));
$xml_action = new SimpleXMLElement($request);
$wifipower->updateState($xml_action);
