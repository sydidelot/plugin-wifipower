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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class wifipower extends eqLogic {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	public static function devicesParameters($_device = '') {
		$path = dirname(__FILE__) . '/../config/devices';
		if (isset($_device) && $_device != '') {
			$files = ls($path, $_device . '.json', false, array('files', 'quiet'));
			if (count($files) == 1) {
				try {
					$content = file_get_contents($path . '/' . $files[0]);
					if (is_json($content)) {
						$deviceConfiguration = json_decode($content, true);
						return $deviceConfiguration[$_device];
					}
					return array();
				} catch (Exception $e) {
					return array();
				}
			}
		}
		$files = ls($path, '*.json', false, array('files', 'quiet'));
		$return = array();
		foreach ($files as $file) {
			try {
				$content = file_get_contents($path . '/' . $file);
				if (is_json($content)) {
					$return += json_decode($content, true);
				}
			} catch (Exception $e) {

			}
		}

		if (isset($_device) && $_device != '') {
			if (isset($return[$_device])) {
				return $return[$_device];
			}
			return array();
		}
		return $return;
	}

	public static function pull() {
		foreach (eqLogic::byType('wifipower') as $eqLogic) {
			if ($eqLogic->getIsEnable() == 1) {
				$eqLogic->updateState();
				$eqLogic->save();
			}
		}
	}

	/*     * *********************Methode d'instance************************* */

	public function preUpdate() {
		if ($this->getConfiguration('ip') == '') {
			throw new Exception(__('Le champs IP ne peut être vide', __FILE__));
		}
		try {
			$this->updateState();
		} catch (Exception $e) {

		}
	}

	public function preSave() {
		if ($this->getConfiguration('port') == '') {
			$this->getConfiguration('port', 2000);
		}
	}

	public function postSave() {
		if ($this->getConfiguration('device') != $this->getConfiguration('applyDevice')) {
			$this->applyModuleConfiguration();
		}
	}

	public function applyModuleConfiguration() {
		$this->setConfiguration('applyDevice', $this->getConfiguration('device'));
		$this->save();
		if ($this->getConfiguration('device') == '') {
			return true;
		}
		$device = self::devicesParameters($this->getConfiguration('device'));
		if (!is_array($device)) {
			return true;
		}

		if (isset($device['configuration'])) {
			foreach ($device['configuration'] as $key => $value) {
				$this->setConfiguration($key, $value);
			}
		}
		if (isset($device['category'])) {
			foreach ($device['category'] as $key => $value) {
				$this->setCategory($key, $value);
			}
		}

		$cmd_order = 0;
		$link_cmds = array();
		$link_actions = array();
		if (isset($device['commands'])) {

			foreach ($device['commands'] as $command) {
				$cmd = null;
				foreach ($this->getCmd() as $liste_cmd) {
					if ($liste_cmd->getLogicalId() == $command['logicalId']) {
						$cmd = $liste_cmd;
						break;
					}
				}
				try {
					if ($cmd == null || !is_object($cmd)) {
						$cmd = new wifipowerCmd();
						$cmd->setOrder($cmd_order);
						$cmd->setEqLogic_id($this->getId());
					} else {
						$command['name'] = $cmd->getName();
					}
					utils::a2o($cmd, $command);

					$cmd->save();

					if (isset($command['value'])) {
						$link_cmds[$cmd->getId()] = $command['value'];
					}
					if (isset($command['configuration']) && isset($command['configuration']['updateCmdId'])) {
						$link_actions[$cmd->getId()] = $command['configuration']['updateCmdId'];
					}
					$cmd_order++;
				} catch (Exception $exc) {

				}
			}
		}

		if (count($link_cmds) > 0) {
			foreach ($this->getCmd() as $eqLogic_cmd) {
				foreach ($link_cmds as $cmd_id => $link_cmd) {
					if ($link_cmd == $eqLogic_cmd->getName()) {
						$cmd = cmd::byId($cmd_id);
						if (is_object($cmd)) {
							$cmd->setValue($eqLogic_cmd->getId());
							$cmd->save();
						}
					}
				}
			}
		}
		if (count($link_actions) > 0) {
			foreach ($this->getCmd() as $eqLogic_cmd) {
				foreach ($link_actions as $cmd_id => $link_action) {
					if ($link_action == $eqLogic_cmd->getName()) {
						$cmd = cmd::byId($cmd_id);
						if (is_object($cmd)) {
							$cmd->setConfiguration('updateCmdId', $eqLogic_cmd->getId());
							$cmd->save();
						}
					}
				}
			}
		}
		$this->save();
	}

	public function getUrl() {
		$url = 'http://';
		$ip = str_replace('http://', '', $this->getConfiguration('ip'));
		if ($this->getConfiguration('username') != '' && $this->getConfiguration('password') != '') {
			$url .= $this->getConfiguration('username') . ':' . $this->getConfiguration('password') . '@';
		}
		if ($this->getConfiguration('port', 2000) != 80) {
			$url .= $ip . ':' . $this->getConfiguration('port', 2000) . '/';
		} else {
			$url .= $ip . '/';
		}
		return $url;
	}

	public function updateState($_xml = null) {
		if ($_xml == null) {
			$request_http = new com_http($this->getUrl() . 'Q');
			$xml = new SimpleXMLElement($request_http->exec(10, 2));
		} else {
			$xml = new SimpleXMLElement($_xml);
		}
		$wifipower = json_decode(json_encode($xml), true);
		foreach ($wifipower['out'] as $relai => $state) {
			$cmd = $this->getCmd(null, $relai);
			if (is_object($cmd)) {
				if ($cmd->execCmd() != $cmd->formatValue($state)) {
					$cmd->setCollectDate('');
					$cmd->event($state);
				}
			}
		}
		$this->setConfiguration('type', $wifipower['device']['type']);
		$this->setConfiguration('XMLversion', $wifipower['device']['XMLversion']);
	}

	/*     * **********************Getteur Setteur*************************** */
}

class wifipowerCmd extends cmd {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	public function execute($_options = array()) {
		if ($this->getType() == 'info') {
			return '';
		}
		$eqLogic = $this->getEqLogic();
		$url = $eqLogic->getUrl();
		$url .= $this->getLogicalId();
		$request_http = new com_http($url);
		$request_http->exec(10, 2);
		$eqLogic->updateState($request_http->exec(10, 2));
		$eqLogic->save();
	}

	/*     * **********************Getteur Setteur*************************** */
}

?>
