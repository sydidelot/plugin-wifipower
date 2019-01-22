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
	
	public static function deamon_info() {
		$return = array();
		$return['log'] = '';
		$return['state'] = 'nok';
		$cron = cron::byClassAndFunction('wifipower', 'pull');
		if (is_object($cron) && $cron->running()) {
			$return['state'] = 'ok';
		}
		$return['launchable'] = 'ok';
		return $return;
	}

	public static function deamon_start() {
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') {
			throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
		}
		$cron = cron::byClassAndFunction('wifipower', 'pull');
		if (!is_object($cron)) {
			throw new Exception(__('Tâche cron introuvable', __FILE__));
		}
		$cron->setDeamonSleepTime(config::byKey('api::frequency', 'wifipower', 1));
		$cron->save();
		$cron->run();
	}

	public static function deamon_stop() {
		$cron = cron::byClassAndFunction('wifipower', 'pull');
		if (!is_object($cron)) {
			throw new Exception(__('Tâche cron introuvable', __FILE__));
		}
		$cron->halt();
	}

	public static function deamon_changeAutoMode($_mode) {
		$cron = cron::byClassAndFunction('wifipower', 'pull');
		if (!is_object($cron)) {
			throw new Exception(__('Tâche cron introuvable', __FILE__));
		}
		$cron->setEnable($_mode);
		$cron->save();
	}

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
		foreach (eqLogic::byType('wifipower', true) as $eqLogic) {
			if ($eqLogic->getConfiguration('pushOnly', 0) == 1) {
				continue;
			}
			$eqLogic->updateState();
		}
	}

	/*     * *********************Methode d'instance************************* */

	public function preUpdate() {
		if ($this->getConfiguration('ip') == '') {
			throw new Exception(__('Le champs IP ne peut être vide', __FILE__));
		}
		$this->setLogicalId($this->getConfiguration('ip'));
		try {
			$this->updateState();
		} catch (Exception $e) {

		}
	}

	public function preSave() {
		if ($this->getConfiguration('port') == '') {
			$this->setConfiguration('port', 2000);
		}
	}

	public function postSave() {
		if ($this->getConfiguration('device') != $this->getConfiguration('applyDevice')) {
			$this->applyModuleConfiguration();
		}
		if ($this->getConfiguration('ip') != '') {
			$this->updateState();
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
		if ($this->getConfiguration('complement') != '') {
			$url .= $this->getConfiguration('complement') . '/';
		}
		return $url;
	}

	public function updateState($xml = null) {
		if ($xml == null) {
			try {
				$request_http = new com_http($this->getUrl() . 'Q');
			} catch (Exception $e) {
				return;
			}
			$xml = new SimpleXMLElement($request_http->exec(30, 5));
		}
		$wifipower = json_decode(json_encode($xml), true);
		log::add('wifipower','debug',json_encode($wifipower));
		if (isset($wifipower['DIGOUT'])) {
			foreach ($wifipower['DIGOUT']['out'] as $relai => $state) {
				$state = ($state ===  '') ? 0 : $state;
				$this->checkAndUpdateCmd('DO' . $relai,$state);
			}
			foreach ($wifipower['DIGIN']['in'] as $relai => $state) {
				$state = ($state ===  '') ? 0 : $state;
				$this->checkAndUpdateCmd('DI' . $relai,$state);
			}
			foreach ($wifipower['ANAIN']['in'] as $relai => $state) {
				$state = ($state ===  '') ? 0 : $state;
				$this->checkAndUpdateCmd('AI' . $relai,$state);
			}
		} else {
			foreach ($wifipower['out'] as $relai => $state) {
				$state = ($state ===  '') ? 0 : $state;
				if (strpos($relai, 'FP') !== false) {
					switch ($state) {
						case 0:
							$state = __('Arrêt', __FILE__);
							break;
						case 1:
							$state = __('Eco', __FILE__);
							break;
						case 2:
							$state = __('Hors Gel', __FILE__);
							break;
						case 3:
							$state = __('Confort', __FILE__);
							break;
						case 4:
							$state = __('Confort-1', __FILE__);
							break;
						case 5:
							$state = __('Confort-2', __FILE__);
							break;
					}
				}
				$this->checkAndUpdateCmd($relai,$state);
			}
		}

		if (isset($wifipower['device']['type']) && $this->getConfiguration('type') != $wifipower['device']['type']) {
			$this->setConfiguration('type', $wifipower['device']['type']);
			$this->save();
		}
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
		$result = $request_http->exec(10, 2);
		try {
			$xml_action = new SimpleXMLElement($result);
			$eqLogic->updateState($xml_action);
		} catch (Exception $e) {

		}
	}

	/*     * **********************Getteur Setteur*************************** */
}

?>
