<?php
namespace SysLog;

use SysLog\Helpers\Functions\PHPFunctions as PHPFunc;
use SysLog\Helpers\General\Config;
use SysLog\Helpers\General\Logger;
use \PDO;

/**
 * Class Importer
 *
 * @package SysLog
 */
class Importer {
	private $configData = array(), $csvHeader, $importFile, $fileType, $importFileHandle; /* resource */
	private $donorData = array();

	private $databaseConn;
	public $csvCounter = 0, $errors = array(); /* array */

	public $quiet = false;
	public $perm_count = 0;
	public $perm_combinations = array();
	/**
	 * Importer constructor.
	 *
	 */
	public function __construct($quiet) {
		$this->quiet = $quiet;

		$this->setConfigData();
		$this->setDatabaseConnection();
	}

	/**
	 * [setConfiguration description]
	 * @param [type] $settings [description]
	 */
	public function setConfiguration($settings) {
		if (!is_array($settings) && empty($settings)) {
			throw new Exception(sprintf('Invalid option settings %s: Must be identified by a array', $settings));
		}

		$this->importFile = $settings["importFile"];
		$this->fileType = $settings["fileType"];
		$this->actionType = $settings["actionType"];
		$this->studyID = $settings["studyID"];
		if ($this->quiet !== true) {
			Logger::write()->addInfo("==> Collecting donor information:");
			Logger::write()->addInfo('Import Action Type: ' . $this->actionType);
			Logger::write()->addInfo('Import file: ' . $this->importFile);
		}

	}

	/**
	 * [setDatabaseConnection description]
	 */
	private function setDatabaseConnection() {
		$dsn = "mysql:host=" . Config::conf()->import["database"]["hostname"] . ";dbname=" . Config::conf()->import["database"]["name"] . ";charset=utf8";
		$opt = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];
		$this->_pdo = new PDO($dsn, Config::conf()->import["database"]["username"], Config::conf()->import["database"]["password"], $opt);
		if (!$this->_pdo) {
			throw new Exception('Error Connecting to Database Server');
		}
	}

	/**
	 * [clearDatabase description]
	 * @return [type] [description]
	 */
	public function clearDatabase() {
		$tables = ['donors', 'donor_visits', 'experimental_data'];
		foreach ($tables as $table) {
			try {
				$truncate_tables = $this->_pdo->prepare("TRUNCATE TABLE " . $table . ";");
				$truncate_tables->execute();
			} catch (PDOException $e) {
				echo $e->getMessage();
			}
		}
	}

	/**
	 * Loop file and import/update
	 *
	 * @return void
	 */
	public function mainDataImport() {

		// For simple stats

		$timeStart = microtime(true);
		$importFile = basename($this->importFile);
		// open file for reading

		$this->openFileHandle();

		// gets header of csv file for line mapping

		$this->csvHeader = str_getcsv(fgets($this->importFileHandle), ",", "\"");

		// Check if we have all required values in this header
		if ($this->verifyImportHeader() === false) {
			Logger::write()->addInfo('ERROR: verifyImportHeader skipping.. ' . $this->importFile);
			return;
		}

		while (($csvLine = fgetcsv($this->importFileHandle, 0, ",", "\"")) !== false) {

			// Maps header of the file with a current line!

			$item = PHPFunc::array_real_combine($this->csvHeader, $csvLine);
			$item["import_file_name"] = basename($this->importFile);
			if (!isset($item["project"])) {
				$item["project"] = $this->studyID;
			}

			if (isset($item["person"])) {
				$donorId = $item["person"];

				if (!isset($this->donorData[$this->studyID]) && is_numeric($this->studyID)) {
					$this->donorData[$this->studyID] = array();
				}
				if (!isset($this->donorData[$this->studyID][$donorId]) && $donorId !== "") {
					$this->donorData[$this->studyID][$donorId] = array();
				}
				array_push($this->donorData[$this->studyID][$donorId], $item);
				$this->csvCounter++;
			} else {
				Logger::write()->addInfo('ERROR: no index "person" defined in import file: ' . $this->importFile);
				var_dump($item);
				exit;
			}
		}

		$this->closeFileHandle();
	}

	/**
	 * [processVisitInformation description]
	 * @return [type] [description]
	 */
	public function processVisitInformation() {
		if ($this->quiet !== true) {
			Logger::write()->addInfo('Started Processing samples..');
			Logger::write()->addInfo('Number of samples: ' . $this->csvCounter);
			Logger::write()->addInfo('Number of studies: ' . count($this->donorData));
		}
		ksort($this->donorData);
		$this->donorData = array_reverse($this->donorData, true);

		// Loop Each donor and Insert data into database

		foreach ($this->donorData as $studyID => $studyDonors) {
			if (isset($this->configData["mapping"][$studyID])) {
				$mapping = $this->configData["mapping"][$studyID];
			} else {
				Logger::write()->addInfo('INFO: Mapping for Study is not defined!');
			}

			Logger::write()->addInfo("Processing study: " . $studyID);
			Logger::write()->addInfo("Number of donors: " . count($studyDonors));
			foreach ($studyDonors as $donorId => $donorVisits) {
				$this->donor = new Donor($donorId, $donorVisits, $this->_pdo, $mapping, $studyID);
				$this->donor->generateDonorData();
				if (!isset($this->donor->insertedDataTracker["donors.donor_visits"])) {
					Logger::write()->addError("INFO: Missing information for donor_visits. Not inserting visits for donor.");
				}

				if (!isset($this->donor->insertedDataTracker["donors.experimental_data"])) {
					Logger::write()->addError("INFO: Missing information for experimental_data. Not inserting experimental_data for donor.");
				}
			}
		}
	}

	/**
	 * [uniqueCombination description]
	 * @param  [type]  $in        [description]
	 * @param  integer $minLength [description]
	 * @param  integer $max       [description]
	 * @return [type]             [description]
	 */
	public function uniqueCombination($in, $minLength = 1, $max = 100000) {
		$count = count($in);
		$members = pow(2, $count);
		$return = array();
		for ($i = 0; $i < $members; $i++) {
			$b = sprintf("%0" . $count . "b", $i);
			$out = array();
			for ($j = 0; $j < $count; $j++) {
				$b{$j} == '1' and $out[] = $in[$j];
			}

			count($out) >= $minLength && count($out) <= $max and $return[] = $out;
		}
		return $return;
	}

	/**
	 * Closes a CSV feed file
	 *
	 * @return void
	 */
	public function closeFileHandle() {
		if (is_resource($this->importFileHandle)) {
			fclose($this->importFileHandle);
		}
	}

	/**
	 * Opens a feed file for reading
	 *
	 * @return void
	 */
	public function openFileHandle() {
		if (!file_exists($this->importFile)) {
			throw new Exception('File not found.');
		}

		$this->importFileHandle = fopen($this->importFile, 'r');
		if (!$this->importFileHandle) {
			throw new Exception('File open failed.');
		}
	}

	/**
	 * @throws \Exception if language is not allowed
	 *
	 * @return void
	 */
	public function setConfigData() {
		if ($this->quiet !== true) {
			Logger::write()->addInfo('Setting configData ');
		}

		$this->configData = $this->getConfigurationData();
		if ($this->configData === false) {
			throw new Exception(sprintf('ERROR: configData is not set.'));
			die(0);
		}
	}

	/**
	 * Configuration based
	 *
	 * @param string $lng
	 * @return bool|mixed
	 */
	public function getConfigurationData() {
		$conf = false;
		$conf = Config::conf()->import;
		return $conf;
	}

	/**
	 * Check if CSV File have all necessary headers that are defined in configuration
	 *
	 * @return bool
	 */
	public function verifyImportHeader() {
		$check = true;
		$a = $this->csvHeader;
		$a = array_map('strtolower', $a);
		$b = Config::conf()->required_fields;
		foreach ($b as $field) {
			$field = strtolower($field);
			if (!in_array($field, $a)) {
				$check = false;
				array_push($this->errors, "ERROR_IM: FIELD: (" . $field . ") is missing in CSV File and its required by JSON configuration.");
			}
		}

		return $check;
	}
}
