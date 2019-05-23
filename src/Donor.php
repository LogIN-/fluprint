<?php
/**
 * @Author: LogIN
 * @Date:   2017-04-02 14:19:30
 * @Last Modified by:   LogIN-
 * @Last Modified time: 2019-05-22 08:56:34
 */
namespace SysLog;

use SysLog\Helpers\Functions\PHPFunctions as PHPFunc;
use SysLog\Helpers\Functions\RemapFunctions as RempFunc;
use SysLog\Helpers\General\Logger;

/**
 * Class Donor
 * @package SysLog
 */
class Donor {
	/**
	 * [$donorId description]
	 * @var [type]
	 */
	public $donorId;

	/**
	 * [$donorDatabaseId description]
	 * @var integer
	 */
	protected $donorDatabaseId = 0;

	/**
	 * New Final Insert Item
	 * @var array $data
	 */
	private $data = array();

	/**
	 * [$_pdo description]
	 * @var [type]
	 */
	private $_pdo;

	public $insertedDataTracker = array();

	/**
	 * [$database description]
	 * @var array
	 */
	private $database = array(

		'donors' => array(
			'main_data' => array(),
		),
		'donor_visits' => array(),
		'experimental_data' => array(),
		'medical_history' => array(),
	);

	/**
	 * [__construct description]
	 * @param [type] $donorId      [description]
	 * @param [type] $visitData    [description]
	 * @param [type] $_pdo         [description]
	 * @param [type] $fieldMapping [description]
	 * @param [type] $studyID      [description]
	 */
	public function __construct($donorId, $visitData, $_pdo, $fieldMapping, $studyID) {
		$this->_pdo = $_pdo;
		$this->_mapping = $fieldMapping;
		$this->_studyID = $studyID;

		$this->setDonorId($donorId);
		$this->setData($visitData);
	}

	/**
	 * [generateDonorData description]
	 * @return [type] [description]
	 */
	public function generateDonorData() {
		Logger::write()->addNotice("==> Donor: " . $this->donorId);
		Logger::write()->addInfo("Processing: DB => main_data");

		// 1. insert into donor and get ID
		$this->setDonorDatabaseTables();

		if (count($this->database["donors"]["main_data"]) > 0) {
			$this->insertedDataTracker["donors.main_data"] = true;
			$this->insertData('donors.main_data');
		}

		// 2. Generate Visits and Experimental data
		$this->setDonorVisitsDatabaseTables();
		$this->database["experimental_data"] = RempFunc::removeExperimentalDataFeatures($this->database["experimental_data"]);

		Logger::write()->addInfo("Processing: DB => donor_visits");
		// 2. inset Donor Visit and get ID
		if (count($this->database["donor_visits"]) > 0) {

			$this->calculateAdditionalValues();

			$this->database["experimental_data"] = RempFunc::removeExperimentalDataPostFeatures($this->database["experimental_data"]);

			// Remove visits that doesn't have any experimental data
			$this->cleanDonorVisitsTable();

			$this->insertedDataTracker["donors.donor_visits"] = true;
			// Calculate real visits IDs
			// Add number of experimental data collected
			$this->insertData('donor_visits');
		}

		Logger::write()->addInfo("Processing: DB => experimental_data");
		if (count($this->database["experimental_data"]) > 0) {

			$this->insertedDataTracker["donors.experimental_data"] = true;
			$this->insertData('experimental_data');
		}

		Logger::write()->addInfo("Processing: DB => medical_history");
		if (count($this->database["medical_history"]) > 0) {

			$this->insertedDataTracker["donors.medical_history"] = true;
			$this->insertData('medical_history');
		}
	}

	/**
	 * [calculateAdditionalValues description]
	 * @return [type] [description]
	 */
	private function calculateAdditionalValues() {
		// Remove visits that doesn't have any experimental data
		$this->cleanDonorVisitsTable();

		// After we removed missing values lets calculate incremental visit IDs
		$this->database["donor_visits"] = PHPFunc::calculateRealVisitValues($this->database["donor_visits"]);

		$this->database = PHPFunc::calculateDonorResponseData($this->database);
	}

	/**
	 * [cleanDonorVisitsTable description]
	 * @return [type] [description]
	 */
	private function cleanDonorVisitsTable() {

		// $debug_ids = [""];

		foreach ($this->database["donor_visits"] as $donor_visits_key => $donor_visits_value) {
			$donor_id = $this->database["donor_visits"][$donor_visits_key]['donor_id'];

			// Check if there is no experimental data saved for specific visit
			if (empty($this->database["experimental_data"][$donor_visits_key])) {
				Logger::write()->addError("INFO: Missing Experimental data for visit! Donor ID: " . $donor_id . " Removing Visit data: \r\n", $donor_visits_value);

				// if (in_array($this->donorId, $debug_ids)) {
				// 	echo "===============================> " . $donor_visits_key . " medical_history \r\n";
				// 	var_dump($this->database["medical_history"][$donor_visits_key]);
				// 	echo "===============================> " . $donor_visits_key . " donor_visits \r\n";
				// 	var_dump($this->database["donor_visits"][$donor_visits_key]);
				// 	echo "===============================> " . $donor_visits_key . " experimental_data \r\n";
				// 	var_dump($this->database["experimental_data"]);
				// }

				if (isset($this->database["donor_visits"][$donor_visits_key])) {
					unset($this->database["donor_visits"][$donor_visits_key]);
				}

				if (isset($this->database["experimental_data"][$donor_visits_key])) {
					unset($this->database["experimental_data"][$donor_visits_key]);
				}

				if (isset($this->database["medical_history"][$donor_visits_key])) {
					unset($this->database["medical_history"][$donor_visits_key]);
				}

				continue;
			}

			if (isset($this->database["experimental_data"][$donor_visits_key])) {
				$this->database["donor_visits"][$donor_visits_key]['total_data'] = count($this->database["experimental_data"][$donor_visits_key]);
			} else {
				$this->database["donor_visits"][$donor_visits_key]['total_data'] = 0;
			}

		}
	}

	/**
	 * [insertData description]
	 * @param  [type] $type [description]
	 * @return [type]       [description]
	 */
	private function insertData($type) {

		if ($type === 'donors.main_data') {
			$this->database["donors"]["main_data"]["donor_id"] = $this->insert('donors', $this->database["donors"]["main_data"], true);

		} else if ($type === 'donor_visits') {
			foreach ($this->database["donor_visits"] as $visitKey => $visitValue) {
				$this->database["donor_visits"][$visitKey]["donor_visits_id"] = $this->insert('donor_visits', $visitValue, true);
			}
		} else if ($type === 'experimental_data') {
			foreach ($this->database["experimental_data"] as $experimental_key => $experimental_value) {

				// Map inserted donor_visits IDs to experimental_data
				foreach ($experimental_value as $experimental_data_key => $experimental_unused_value) {
					$experimental_value[$experimental_data_key]['donor_visits_id'] = $this->database["donor_visits"][$experimental_key]["donor_visits_id"];
					$this->database["experimental_data"][$experimental_key][$experimental_data_key]['donor_visits_id'] = $experimental_value[$experimental_data_key]['donor_visits_id'];
				}

				if (!empty($experimental_value)) {
					$this->insertBulk('experimental_data', $experimental_value);
				}
			}
		} else if ($type === 'medical_history') {

			foreach ($this->database["medical_history"] as $medical_history_key => $medical_history_value) {

				if (isset($this->database["donor_visits"][$medical_history_key])) {

					$medical_history_value['donor_visits_id'] = $this->database["donor_visits"][$medical_history_key]["donor_visits_id"];
					$this->database["medical_history"][$medical_history_key]['donor_visits_id'] = $medical_history_value['donor_visits_id'];

					if (count($medical_history_value) <= 2) {
						unset($this->database["medical_history"][$medical_history_key]);
					}

				} else {
					var_dump($medical_history_key);
					var_dump($medical_history_key);
					var_dump($medical_history_value);
					var_dump($this->donorId);
					var_dump($this->_studyID);
					echo "===========> donor_visits \r\n";
					var_dump($this->database["donor_visits"]);
					echo "===========> medical_history \r\n";
					var_dump($this->database["medical_history"]);
					exit;
				}
			}
			if (!empty($this->database["medical_history"])) {
				$this->insertBulk('medical_history', $this->database["medical_history"]);
			}

		} else {
			echo "\r\nINFO: Not inserting in Database. Type: " . $type . " not defined!";
		}
	}

	/**
	 * [insertBulk description]
	 * @param  [type] $table      [description]
	 * @param  array  $insertData [description]
	 * @return [type]             [description]
	 */
	private function insertBulk($table, array $insertData) {

		$fields = "";

		$prep = array();
		foreach ($insertData as $data) {
			if ($fields === "") {
				$fields = array_keys($data);
			} else {
				foreach (array_keys($data) as $columnName) {
					if (!in_array($columnName, $fields)) {
						$fields[] = $columnName;
					}
				}
			}
			$prep[] = $data;

		}

		$dataToInsert = array();
		foreach ($prep as $row => $data) {
			foreach ($data as $val) {
				$dataToInsert[] = $val;
			}
		}

		if ($fields === "") {
			throw new \Exception("At least one field and value is required.");
		}

		$rowPlaces = '(' . implode(', ', array_fill(0, count($fields), '?')) . ')';
		$allPlaces = implode(', ', array_fill(0, count($prep), $rowPlaces));

		$fields = '`' . implode('`,`', $fields) . '`';
		$sql = "INSERT IGNORE INTO {$table} ($fields) VALUES " . $allPlaces;

		$stmt = $this->_pdo->prepare($sql);

		$this->_pdo->beginTransaction();
		try {
			$stmt->execute($dataToInsert);
		} catch (PDOException $e) {
			throw $e->getMessage();
		}
		$this->_pdo->commit();
	}

	/**
	 * [insert description]
	 * @param  [type]  $table      [description]
	 * @param  array   $insertData [description]
	 * @param  boolean $databaseID [description]
	 * @return [type]              [description]
	 */
	private function insert($table, array $insertData, $databaseID = false) {
		$id = 0;

		$fields = array_keys($insertData);
		$values = array_values($insertData);

		$numFields = count($fields);
		$numValues = count($values);

		if ($numFields === 0 or $numValues === 0) {
			throw new \Exception("At least one field and value is required.");
		}

		if ($numFields !== $numValues) {
			throw new \Exception("Mismatched number of field and value arguments.");
		}

		$fields = '`' . implode('`,`', $fields) . '`';
		$values = "'" . implode("','", $values) . "'";
		$sql = "INSERT INTO {$table} ($fields) VALUES($values)";

		try {
			$stmt = $this->_pdo->prepare($sql);
			$stmt->execute();
			if ($databaseID === true) {
				$id = (int) $this->_pdo->lastInsertId();
			}

		} catch (PDOException $e) {
			throw $e->getMessage();
		}

		return $id;
	}
	/**
	 * [setDonorDatabaseTables description]
	 */
	private function setDonorDatabaseTables() {

		$data = array();

		foreach ($this->data as $dataItem) {

			if (isset($dataItem[$this->_mapping["donor_id"]["field_name"]])) {
				if (!isset($data["study_donor_id"])) {
					$data["study_donor_id"] = $dataItem[$this->_mapping["donor_id"]["field_name"]];
				}
			}

			if (isset($this->_studyID)) {
				if (!isset($data["study_id"])) {
					$data["study_id"] = $this->_studyID;
				}
			}

			if (isset($dataItem["project"])) {
				if (!isset($data["study_internal_id"])) {
					$data["study_internal_id"] = $dataItem["project"];
				}
			}

			if (isset($dataItem["gender"])) {
				if (!isset($data["gender"]) && trim($dataItem["gender"]) !== "") {
					$data["gender"] = $dataItem["gender"];
				}
			}

			if (isset($dataItem["ethnicity"])) {
				if (!isset($data["race"]) && trim($dataItem["ethnicity"]) !== "") {
					$data["race"] = PHPFunc::sanitizeRace($dataItem["ethnicity"]);
					if ($data["race"] === "Unknown") {
						unset($data["race"]);
					}
				}
			}

			// Only study 30
			// if ($this->_studyID === 30) {
			// 	if (isset($dataItem["birth_age"])) {
			// 		if (!isset($data["birth_age_weeks"]) && trim($dataItem["birth_age"]) !== "") {
			// 			$data["birth_age_weeks"] = intval($dataItem["birth_age"]);
			// 		}
			// 	}
			// 	if (isset($dataItem["breastfed"])) {
			// 		if (!isset($data["breastfed"]) && trim($dataItem["breastfed"]) !== "") {
			// 			$data["breastfed"] = PHPFunc::sanitizeBoolean($dataItem["breastfed"]);
			// 		}
			// 	}
			// 	if (isset($dataItem["breastfed_period"])) {
			// 		if (!isset($data["breastfed_period"]) && trim($dataItem["breastfed_period"]) !== "") {
			// 			$data["breastfed_period"] = $dataItem["breastfed_period"];
			// 		}
			// 	}
			// 	if (isset($dataItem["daycare"])) {
			// 		if (!isset($data["daycare"]) && trim($dataItem["daycare"]) !== "") {
			// 			$data["daycare"] = PHPFunc::sanitizeBoolean($dataItem["daycare"]);
			// 		}
			// 	}
			// 	if (isset($dataItem["daycare_age"])) {
			// 		if (!isset($data["daycare_age"]) && trim($dataItem["daycare_age"]) !== "") {
			// 			$data["daycare_age"] = PHPFunc::sanitizeDaycareAge($dataItem["daycare_age"]);
			// 		}
			// 	}
			// }
		}
		$this->database["donors"]["main_data"] = $data;
	}

	/**
	 * [setDonorVisitsDatabaseTables description]
	 */
	private function setDonorVisitsDatabaseTables() {
		$donor_visits = array();
		$experimental_data = array();
		$medical_history = array();

		$i = 0;

		foreach ($this->data as $visitData) {

			$visitId = false;
			$visitSampleId = 0;

			if (!isset($visitData[$this->_mapping["visit_id"]["field_name"]]) && $this->_studyID === 30) {
				if (isset($visitData['visit'])) {
					$visit = $visitData['visit'];
					$visitData[$this->_mapping["visit_id"]["field_name"]] = $visit;
				}
			}

			if ($visitId === false && isset($visitData[$this->_mapping["visit_id"]["field_name"]])) {
				$visitIdTemp = $visitData[$this->_mapping["visit_id"]["field_name"]];

				if (isset($this->_mapping["visit_id"]["regex"])) {
					preg_match_all($this->_mapping["visit_id"]["regex"],
						$visitIdTemp,
						$matches);

					if (isset($matches[$this->_mapping["visit_id"]["group"]]) && count($matches[$this->_mapping["visit_id"]["group"]]) > 0) {
						$visitId = reset($matches[$this->_mapping["visit_id"]["group"]]);
						$visitId = filter_var($visitId, FILTER_SANITIZE_NUMBER_INT);
						$visitId = ltrim($visitId, '0');
						$visitId = intval($visitId);
					} else {
						// take the last number from the Visit Field
						if (preg_match_all('/\d+/', $visitIdTemp, $numbers)) {
							$lastnum = end($numbers[0]);
							if (is_numeric($lastnum)) {
								$visitId = intval($lastnum);
							}
						} else if (trim($visitIdTemp) === "") {
							if ($this->_studyID === 18) {
								$visitId = 0;
							} else {
								$visitId = 1;
							}
						}
					}

				} else {
					// take the last number from the Visit Field
					if (preg_match_all('/\d+/', $visitIdTemp, $numbers)) {
						$lastnum = end($numbers[0]);
						if (is_numeric($lastnum)) {
							$visitId = intval($lastnum);
						}
					} else if (trim($visitIdTemp) === "") {
						if ($this->_studyID === 18) {
							$visitId = 0;
						} else {
							$visitId = 1;
						}

					}
				}
			}

			if (intval($visitId) > 100) {
				$visitId = false;
				Logger::write()->addError("=======> ERROR: WRONG VISIT FORMAT DETECTED! <=======");
			}

			if ($visitId === false &&
				($this->_studyID !== 18 && $this->_studyID !== 30)
				|| $visitId === "") {
				Logger::write()->addError("=> ERROR: Skipping: No visit ID for mandatory Study!");
				Logger::write()->addError("StudyID: " . $this->_studyID);
				Logger::write()->addError("Visit ID Field: " . $this->_mapping["visit_id"]["field_name"]);
				Logger::write()->addError("Visit ID Regex: " . $this->_mapping["visit_id"]["regex"]);
				Logger::write()->addError("Data: ", $visitData);
				continue;

			} else if ($visitId === false) {
				Logger::write()->addError("=> ERROR: Skipping: visit processing. No visit ID.");
				Logger::write()->addError("StudyID: " . $this->_studyID);
				Logger::write()->addError("Visit ID Field: " . $this->_mapping["visit_id"]["field_name"]);
				Logger::write()->addError("Visit ID Regex: " . $this->_mapping["visit_id"]["regex"]);
				Logger::write()->addError("Data: ", $visitData);
				continue;
			}

			$visit_year = null;
			if (isset($visitData["year"]) && trim($visitData["year"]) !== "") {
				$visit_year = filter_var($visitData["year"], FILTER_SANITIZE_NUMBER_INT);
				$visit_year = intval($visit_year);

				// Define a new visit!
				$visitSampleId = md5(
					$this->database["donors"]["main_data"]["donor_id"] . $visit_year . $visitId
				);

				if (!isset($donor_visits[$visitSampleId])) {
					$donor_visits[$visitSampleId] = array();
				}

				if (!isset($donor_visits[$visitSampleId]["donor_id"])) {
					$donor_visits[$visitSampleId]["donor_id"] = $this->database["donors"]["main_data"]["donor_id"];
				}
				// Just a Placeholder we will process and calculate real visit numbers later on after import
				if (!isset($donor_visits[$visitSampleId]["visit_id"])) {
					$donor_visits[$visitSampleId]["visit_id"] = 0;
				}

				if (!isset($donor_visits[$visitSampleId]["visit_internal_id"])) {
					if (isset($visitData[$this->_mapping["visit_id"]["field_name"]])) {
						$donor_visits[$visitSampleId]["visit_internal_id"] = $visitId;
					}
				}

				if ($visit_year !== null && is_numeric($visit_year)) {
					if (!isset($donor_visits[$visitSampleId]["visit_year"])) {
						$donor_visits[$visitSampleId]["visit_year"] = $visit_year;
					}
				}

				if (isset($visitData["day"])) {
					if (!isset($donor_visits[$visitSampleId]["visit_day"])) {

						if ($visitData["day"] !== "n/a") {

							$visit_day = filter_var($visitData["day"], FILTER_SANITIZE_NUMBER_INT);
							if ($visit_day === "00") {
								$visit_day = 0;
							} else {
								$visit_day = ltrim($visit_day, '0');
							}

							if (is_numeric($visit_day)) {
								if ($visit_day === null) {
									$visit_day = 0;
								}
								$donor_visits[$visitSampleId]["visit_day"] = $visit_day;
							}
						}

					}
				}

				if (isset($visitData["age"])) {
					if (!isset($donor_visits[$visitSampleId]["age"])) {
						$donor_visits[$visitSampleId]["age"] = floatval($visitData["age"]);
					}
				}

				if (isset($donor_visits[$visitSampleId]["age"]) && !isset($donor_visits[$visitSampleId]["age_round"])) {
					$donor_visits[$visitSampleId]["age_round"] = round($donor_visits[$visitSampleId]["age"]);
				}

				if (isset($visitData["analyte_specific_name"]) && $visitData["analyte_specific_name"] === "CMV Resp") {
					if (!isset($donor_visits[$visitSampleId]["cmv_status"])) {
						$donor_visits[$visitSampleId]["cmv_status"] = floatval(round($visitData["n"], 2));
					}
				}

				if (isset($visitData["analyte_specific_name"]) && $visitData["analyte_specific_name"] === "EBV Resp") {
					if (!isset($donor_visits[$visitSampleId]["ebv_status"])) {
						$donor_visits[$visitSampleId]["ebv_status"] = floatval(round($visitData["n"], 2));
					}
				}

				if (isset($visitData["bmi_data"]) && trim($visitData["bmi_data"]) !== "") {
					if (!isset($donor_visits[$visitSampleId]["bmi"])) {
						$donor_visits[$visitSampleId]["bmi"] = floatval(round($visitData["bmi_data"], 2));
					}
				}

				if (isset($visitData["vaccination_data"]) && trim($visitData["vaccination_data"]) !== "") {
					if (!isset($donor_visits[$visitSampleId]["vaccine"])) {
						$donor_visits[$visitSampleId]["vaccine"] = PHPFunc::sanitizeVaccinationData($visitData["vaccination_data"]);
					}
				}

				/**
				 * MEDICAL HISTORY DATA START
				 */
				if (!isset($medical_history[$visitSampleId])) {
					$medical_history[$visitSampleId] = array();
				}

				if (!isset($medical_history[$visitSampleId]["donor_id"])) {
					$medical_history[$visitSampleId]["donor_id"] = $this->database["donors"]["main_data"]["donor_id"];
				}

				$med_hist_columns = ["statin" => "statin_use",
					"FluVaccHx" => "flu_vaccination_history",
					"TotalFluVacc_Life" => "total_vaccines_received",
					"FluVacc_1YrPrior" => "vaccinated_1yr_prior",
					"VaccType_1YrPrior" => "vaccine_type_1yr_prior",
					"FluVacc_2YrsPrior" => "vaccinated_2yr_prior",
					"VaccType_2YrsPrior" => "vaccine_type_2yr_prior",
					"FluVacc_3YrsPrior" => "vaccinated_3yr_prior",
					"VaccType_3YrsPrior" => "vaccine_type_3yr_prior",
					"FluVacc_4YrsPrior" => "vaccinated_4yr_prior",
					"VaccType_4YrsPrior" => "vaccine_type_4yr_prior",
					"FluVacc_5YrsPrior" => "vaccinated_5yr_prior",
					"VaccType_5YrsPrior" => "vaccine_type_5yr_prior",
					"Flu_MD_dx" => "influenza_infection_history",
					"Flu_Hospital" => "influenza_hospitalization"];

				foreach ($med_hist_columns as $med_hist_item_key => $med_hist_item_value) {

					$med_hist_column = strtolower($med_hist_item_key);

					if (isset($visitData[$med_hist_column]) && trim($visitData[$med_hist_column]) !== "") {
						if (!isset($medical_history[$visitSampleId][$med_hist_item_value])) {
							$medical_history[$visitSampleId][$med_hist_item_value] = PHPFunc::sanitizeMedicalHistoryData($visitData[$med_hist_column], $med_hist_column);
						}
					}
				}
			}

			/**
			 * EXPERIMENTAL DATA START
			 */
			if (!isset($experimental_data[$visitSampleId])) {
				$experimental_data[$visitSampleId] = array();
			}

			if (isset($visitData["assay"])) {
				$assay = PHPFunc::sanitizeAssay($visitData["assay"]);

				$analyteID = "";
				if (isset($visitData["stim"]) && trim($visitData["stim"]) !== "" && trim($visitData["stim"]) !== "none") {
					$analyteID = trim($visitData["stim"]) . "_";
				}

				$studyPrefix = "";

				if ($assay === 6 || $assay === 15) {
					$studyPrefix = "L50_";
				}

				$experimental_idx = md5($visitSampleId . $assay . $studyPrefix . $analyteID . RempFunc::sanitizeExperimentalName($visitData["analyte_generic_name"]) . $visitData["analyte_specific_name"]);

				if (!isset($experimental_data[$visitSampleId][$experimental_idx])) {
					$experimental_data[$visitSampleId][$experimental_idx] = array();
				}

				if (!isset($experimental_data[$visitSampleId][$experimental_idx]["donor_visits_id"])) {
					$experimental_data[$visitSampleId][$experimental_idx]["donor_visits_id"] = false;
				}

				if (!isset($experimental_data[$visitSampleId][$experimental_idx]["donor_id"])) {
					$experimental_data[$visitSampleId][$experimental_idx]["donor_id"] = $this->database["donors"]["main_data"]["donor_id"];

					if (intval($experimental_data[$visitSampleId][$experimental_idx]["donor_id"]) === 0) {
						Logger::write()->addError("=> ERROR: NO DONOR ID DETECTED");
						Logger::write()->addError("StudyID: " . $this->_studyID);
						var_dump($this->database);
						var_dump($this->donorId);
						exit;

					}
				}

				if (!isset($experimental_data[$visitSampleId][$experimental_idx]["assay"])) {
					$experimental_data[$visitSampleId][$experimental_idx]["assay"] = $assay;
				}

				if (!isset($experimental_data[$visitSampleId][$experimental_idx]["name"])) {
					if (isset($visitData["analyte_generic_name"])) {
						$experimental_data[$visitSampleId][$experimental_idx]["name"] = $studyPrefix . $analyteID . $visitData["analyte_generic_name"];
						$experimental_data[$visitSampleId][$experimental_idx]["name_formatted"] = RempFunc::sanitizeExperimentalName($experimental_data[$visitSampleId][$experimental_idx]["name"]);
					}
				}

				if (!isset($experimental_data[$visitSampleId][$experimental_idx]["subset"])) {
					if (isset($visitData["analyte_specific_name"])) {
						$experimental_data[$visitSampleId][$experimental_idx]["subset"] = $visitData["analyte_specific_name"];
					}
				}
				if (!isset($experimental_data[$visitSampleId][$experimental_idx]["units"])) {
					if (isset($visitData["units"])) {
						$experimental_data[$visitSampleId][$experimental_idx]["units"] = $visitData["units"];
					}
				}
				if (!isset($experimental_data[$visitSampleId][$experimental_idx]["data"])) {
					if (isset($visitData["n"])) {
						$experimental_data[$visitSampleId][$experimental_idx]["data"] = floatval(round($visitData["n"], 2));
					}
				}

				// Crosscheck CMV and EBV
				if (isset($experimental_data[$visitSampleId][$experimental_idx]["name"])) {
					if ($experimental_data[$visitSampleId][$experimental_idx]["name"] === "CMV Status") {
						if (!isset($donor_visits[$visitSampleId]["cmv_status"])) {
							$donor_visits[$visitSampleId]["cmv_status"] = $experimental_data[$visitSampleId][$experimental_idx]["data"];
						}
					}
					if ($experimental_data[$visitSampleId][$experimental_idx]["name"] === "EBV Status") {
						if (!isset($donor_visits[$visitSampleId]["ebv_status"])) {
							$donor_visits[$visitSampleId]["ebv_status"] = $experimental_data[$visitSampleId][$experimental_idx]["data"];
						}
					}
				}
			}

			if ($visitSampleId === 0) {
				if (isset($donor_visits[$visitSampleId])) {
					unset($donor_visits[$visitSampleId]);
				}
				if (isset($experimental_data[$visitSampleId])) {
					unset($experimental_data[$visitSampleId]);
				}
				if (isset($medical_history[$visitSampleId])) {
					unset($medical_history[$visitSampleId]);
				}
			}

			$i++;
		}
		$this->database["donor_visits"] = $donor_visits;
		$this->database["experimental_data"] = $experimental_data;
		$this->database["medical_history"] = $medical_history;
	}

	/**
	 * Sets current Item CSV Data
	 *
	 * @param $data
	 */
	private function setDonorId($donorId) {
		$this->donorId = $donorId;
	}

	/**
	 * Sets current Item CSV Data
	 *
	 * @param $data
	 */
	private function setData($data) {
		$this->data = $data;
	}

	/**
	 * Get new Item to Insert
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}

}
