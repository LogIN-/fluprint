<?php

/**
 * @Author: LogIN
 * @Date:   2017-04-02 10:58:06
 * @Last Modified by:   LogIN-
 * @Last Modified time: 2018-07-18 14:08:51
 */

namespace SysLog\Helpers\Functions;

class PHPFunctions {
	/**
	 * Return intersecting array of two given arrays.
	 * Compares keys and values. Checks recursively.
	 * Returns empty array if no intersection.
	 *
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	public static function intersectRecursive($array1, $array2) {
		$properties = [];
		foreach ($array1 as $key => $value) {
			if (isset($array2[$key])) {
				// key the same - check value
				$value1 = $value;
				$value2 = $array2[$key];
				if (is_array($value1)) {
					$intersectValue = self::intersectRecursive($array1[$key], $array2[$key]);
					if ($intersectValue) {
						$properties[$key] = $intersectValue;
					}
				} elseif ($value1 === $value2) {
					$properties[$key] = $value1;
				}
			}
		}
		return $properties;
	}

	/**
	 * Combines header array with item array
	 * fore non-existent CSV Fields that are defined in header adds array items with empty value
	 *
	 * @param array $csvHeaderItem
	 * @param array $csvLine
	 *
	 * @return array $item
	 *
	 */
	public static function array_real_combine($csvHeaderItem, $csvLine) {
		$item = array();

		foreach ($csvHeaderItem as $csvHeaderItemKey => $csvHeaderItemValue) {

			$csvHeaderItemValue = strtolower($csvHeaderItemValue);
			$csvHeaderItemValue = preg_replace('/\s+/', '_', $csvHeaderItemValue);

			if (isset($csvLine[$csvHeaderItemKey])) {
				$value = trim($csvLine[$csvHeaderItemKey]);
				if ($value === "null") {
					$value = "";
				}
				$item[$csvHeaderItemValue] = $value;
			} else {
				$item[$csvHeaderItemValue] = "";
			}
		}
		return $item;
	}

	/**
	 * [sanitizeRace description]
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	public static function sanitizeRace($string) {
		$data = "";
		$mapping = array(
			"Caucasian or White" => "Caucasian",
			"Caucasian or White,Asian" => "Other",
			"Caucasian or White,Other" => "Other",
			"Asian" => "Asian",
			"Asian,Other" => "Other",

			"Other" => "Other",
			"Caucasian or White,Black African American,Asian,Other" => "Other",
			"Caucasian or White,Black African American" => "Other",
			"NULL" => "Other",
			"Not Hispanic or Latino" => "Other",
			"Non-Hispanic" => "Other",
			"Decline to answer" => "Unknown",

			"Black African American" => "Black or African American",
			"Black African American,Asian" => "Other",
			"Cauc or White,Black Af Am" => "Other",

			"Caucasian or White,Pacific Islander" => "Other",
			"Caucasian or White,PacIslan" => "Other",
			"Cauc or White,Pacific Islander" => "Other",
			"Pacific Islander,Asian" => "Other",

			"American Indian/Alaska native,Caucasian or Wh" => "Other",
			"American Indian/Alaska native,Caucasian or White" => "Other",
			"American Indian/Alaska native,Black African American" => "Other",
			"Am In/Alaska native,Cauc or W" => "Other",
			"Am In/AlaskaNative,Black Af Am" => "Other",
			"American Indian/Alaska native" => "American Indian or Alaska Native",

			"Hispanic" => "Hispanic/Latino",
			"Hispanic or Latino" => "Hispanic/Latino");

		foreach ($mapping as $raceKey => $raceValue) {
			if ($raceKey === $string) {
				$data = $raceValue;
				break;
			}
		}
		if ($data === "") {
			$data = "Unknown - sanitizeRace - " . $string;
			var_dump($data);
			exit;
		}
		return $data;
	}

	/**
	 * [sanitizeVaccinationData description]
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	public static function sanitizeVaccinationData($string) {

		$data = "";
		$mapping = array(
			"FluMist IIV4 0.2 mL intranasal spray" => 1, // "Flumist",
			"FluMist Intranasal spray" => 1, // "Flumist",
			"FluMist Intranasal Spray 2009-2010" => 1, // "Flumist",
			"FluMist Intranasal Spray" => 1, // "Flumist",
			"Flumist" => 1, // "Flumist",

			"Fluzone Intradermal-IIV3" => 2, // "Fluzone Intradermal",
			"Fluzone Intradermal" => 2, // "Fluzone Intradermal",

			"GSK Fluarix IIV3 single-dose syringe" => 3, // "Fluarix",

			"Fluzone 0.5 mL IIV4 SD syringe" => 4, // "Fluzone",
			"Fluzone 0.25 mL IIV4 SD syringe" => 4, // "Fluzone",
			"Fluzone IIV3 multi-dose vial" => 4, // "Fluzone",
			"Fluzone single-dose syringe" => 4, // "Fluzone",
			"Fluzone multi-dose vial" => 4, // "Fluzone",
			"Fluzone single-dose syringe 2009-2010" => 4, // "Fluzone",
			"Fluzone high-dose syringe" => 4, // "Fluzone",
			"Fluzone 0.5 mL single-dose syringe" => 4, // "Fluzone",
			"Fluzone 0.25 mL single-dose syringe" => 4, // "Fluzone",
			"Fluzone IIV3 High-Dose SDS" => 4, // "Fluzone",
			"Fluzone IIV4 single-dose syringe" => 4, // "Fluzone",
			"Fluzone High-Dose syringe" => 4, // "Fluzone"
		);

		foreach ($mapping as $raceKey => $raceValue) {
			if ($raceKey === $string) {
				$data = $raceValue;
				break;
			}
		}
		if ($data === "") {
			$data = "Unknown - sanitizeVaccinationData - " . $string;
			var_dump($data);
			exit;
		}
		return $data;
	}

	/**
	 * [sanitizeDaycareAge description]
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	public static function sanitizeDaycareAge($string) {

		$data = "";
		$mapping = array(
			"2-3 years,3-4 years" => 2,
			"Birth  to 6month,6 months to 1 year" => 0,
			"Birth  to 6month" => 0,
			"Birth  to 6month,6 months to 1 year,1-2 years" => 0,
			"Birth  to 6month,6 months to 1 year,1-2 years,2-3 years" => 0,
			"Birth  to 6month,6 months to 1 year,1-2 years,2-3 years,3-4 years" => 0,
			"Birth  to 6month,6 months to 1 year,1-2 years,2-3 years,3-4 years,4-5 years" => 0,
			"1-2 years,2-3 years" => 1,
			"1-2 years" => 1,
			"3-4 years,4-5 years" => 3,
			"1-2 years,2-3 years,3-4 years,4-5 years" => 1,
			"2-3 years,3-4 years,4-5 years" => 2,
			"6 months to 1 year,1-2 years,2-3 years,3-4 ye" => 0.6,
			"6 months to 1 year,1-2 years,2-3 years,3-4 years,4-5 years" => 0.6,
			"2-3 years" => 2,
			"3-4 years" => 3,
			"6 months to 1 year,1-2 years,2-3 years" => 0.6,
			"4-5 years" => 4,
			"6 months to 1 year" => 0.6,
		);

		foreach ($mapping as $raceKey => $raceValue) {
			if ($raceKey === $string) {
				$data = $raceValue;
				break;
			}
		}
		if ($data === "") {
			$data = "Unknown - sanitizeDaycareAge - " . $string;
			var_dump($data);
			exit;
		}
		return $data;
	}

	/**
	 * [sanitizeBoolean description]
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	public static function sanitizeBoolean($string) {

		$data = "";
		$mapping = array(
			"Yes" => 1,
			"No" => 0,
		);

		foreach ($mapping as $raceKey => $raceValue) {
			if ($raceKey === $string) {
				$data = $raceValue;
				break;
			}
		}
		if ($data === "") {
			$data = "Unknown - sanitizeBoolean - " . $string;
			var_dump($data);
			exit;
		}
		return $data;
	}

	/**
	 * [sanitizeAssay description]
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	public static function sanitizeAssay($string) {
		$data = "";

		$mapping = array("CMV EBV" => 1,
			"Other immunoassay" => 2,
			"Human Luminex 62-63 plex" => 3,
			"CyTOF phenotyping" => 4,
			"HAI" => 5,
			"Human Luminex 51 plex" => 6,
			"Phospho-flow cytokine stim (PBMC)" => 7,
			"pCyTOF (whole blood) pheno" => 9,
			"pCyTOF (whole blood) phospho" => 10,
			"CBCD" => 11,
			"Human MSD 4 plex" => 12,
			"Lyoplate 1" => 13,
			"Human MSD 9 plex" => 14,
			"Human Luminex 50 plex" => 15,
			"Other Luminex" => 16,
			"Phenotyping-1" => 17);

		foreach ($mapping as $mappingKey => $mappingValue) {
			if ($mappingKey === $string) {
				$data = $mappingValue;
				break;
			}
		}
		if ($data === "") {
			$data = "Unknown - sanitizeAssay - " . $string;
			var_dump($data);
			exit;
		}
		return $data;
	}

	/**
	 * [calculateRealVisitValues description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public static function calculateRealVisitValues($data) {
		$donroVisitsTemp = array();
		$cmv_status = false;
		$ebv_status = false;
		$vaccine = false;

		$min_age = array(
			'year' => 10000,
			'age' => 0,
		);

		foreach ($data as $dataKey => $dataValue) {
			$visitYear = $dataValue['visit_year'];
			$visitInternalId = $dataValue['visit_internal_id'];

			if (trim($visitInternalId) === "") {
				$visitInternalId = $dataValue['visit_day'];
			}
			if (trim($visitInternalId) === "") {
				$visitInternalId = rand(50, 100);
			}

			if (!isset($donroVisitsTemp[$visitYear])) {
				$donroVisitsTemp[$visitYear] = array();
			}
			if (!isset($donroVisitsTemp[$visitYear]['data'])) {
				$donroVisitsTemp[$visitYear]['data'] = array();
				$donroVisitsTemp[$visitYear]['data']['visits'] = array();
			}

			if (!isset($donroVisitsTemp[$visitYear]['data']['visits'][$visitInternalId])) {
				$donroVisitsTemp[$visitYear]['data']['visits'][$visitInternalId] = array();
				$donroVisitsTemp[$visitYear]['data']['visits'][$visitInternalId]['keyIndex'] = $dataKey;
			}

			if (isset($dataValue['cmv_status']) && trim($dataValue['cmv_status']) !== "") {
				$cmv_status = $dataValue['cmv_status'];
			}
			if (isset($dataValue['ebv_status']) && trim($dataValue['ebv_status']) !== "") {
				$ebv_status = $dataValue['ebv_status'];
			}
			if (isset($dataValue['vaccine']) && trim($dataValue['vaccine']) !== "") {
				$vaccine = $dataValue['vaccine'];
			}
			if (isset($dataValue['age']) && trim($dataValue['age']) !== "") {
				if ($visitYear < $min_age['year']) {
					$min_age['year'] = $visitYear;
					$min_age['age'] = $dataValue['age'];
				}
			}
		}
		ksort($donroVisitsTemp);

		$counter = 1;
		foreach ($donroVisitsTemp as $tempKey => $tempValue) {
			ksort($tempValue['data']['visits']);

			$totalVisitsForYear = count($tempValue['data']['visits']);
			$visitYearlyCounter = 0;

			foreach ($tempValue['data']['visits'] as $key => $value) {
				if ($cmv_status !== false) {
					$data[$value['keyIndex']]['cmv_status'] = $cmv_status;
				}
				if ($ebv_status !== false) {
					$data[$value['keyIndex']]['ebv_status'] = $ebv_status;
				}
				if ($vaccine !== false) {
					$data[$value['keyIndex']]['vaccine'] = $vaccine;
				}
				// Check if Age is missing and calculate it if needed!!
				if (!isset($data[$value['keyIndex']]['age']) || trim($data[$value['keyIndex']]['age']) === "") {

					if (intval($min_age['age']) !== 0) {
						$vistYear = $data[$value['keyIndex']]['visit_year'];
						$yearDiff = round($vistYear - $min_age['year']);
						$visitAge = $min_age['age'] + $yearDiff;
						$data[$value['keyIndex']]['age'] = $visitAge;
					}

				}
				if (!isset($data[$value['keyIndex']]['visit_day']) || trim($data[$value['keyIndex']]['visit_day']) === "") {
					if (isset($data[$value['keyIndex']]['visit_internal_id']) && trim($data[$value['keyIndex']]['visit_internal_id']) !== "") {
						$data[$value['keyIndex']]['visit_day'] = $data[$value['keyIndex']]['visit_internal_id'];
					}
				}
				// Calculate pre / post only for HAI study
				$visitYearlyCounter++;
				if ($visitYearlyCounter === 1 && $totalVisitsForYear !== 1) {
					$visit_type_hai = "pre";
				} else if ($visitYearlyCounter === $totalVisitsForYear && $totalVisitsForYear !== 1) {
					$visit_type_hai = "post";
				} else if ($visitYearlyCounter === $totalVisitsForYear && $totalVisitsForYear === 1) {
					$visit_type_hai = "single";
				} else {
					$visit_type_hai = "other";
				}
				$data[$value['keyIndex']]['visit_type_hai'] = $visit_type_hai;

				$data[$value['keyIndex']]['visit_id'] = $counter;
				$counter++;
			}
		}

		return $data;
	}

	/**
	 * [normalizeDataNames description]
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	public static function normalizeDataNames($string) {
		$string = trim($string);
		$string = str_replace("+", " pos ", $string);
		$string = str_replace("-", " neg ", $string);
		$string = preg_replace('/\s+/', '_', $string);
		$string = str_replace("__", "_", $string);
		$string = str_replace("/", "_", $string);
		$string = str_replace("(", "", $string);
		$string = str_replace(")", "", $string);
		$string = str_replace(":", "", $string);
		$string = preg_replace("/[^A-Za-z0-9_]/", '', $string);
		$string = str_replace("__", "_", $string);
		$string = str_replace("__", "_", $string);

		return $string;
	}

	/**
	 * [replaceDataNameToVisitPhase description]
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	public static function replaceDataNameToVisitPhase($string) {
		$string = str_replace("a_tex", "1", $string);
		$string = str_replace("b_mass", "2", $string);
		$string = str_replace("brisbane", "3", $string);
		$string = str_replace("california", "4", $string);

		return $string;
	}

	/**
	 * [replaceVisitValues description]
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	public static function replaceVisitValues($string) {
		$string = strtolower($string);
		$string = str_replace("0", "pre", $string);
		$string = str_replace("1", "pre", $string);
		$string = str_replace("3", "post", $string);
		$string = str_replace("5", "post", $string);
		$string = str_replace("28", "post", $string);

		return $string;
	}

	/**
	 * [geometric_mean description]
	 * @param  [type] $a [description]
	 * @return [type]    [description]
	 */
	public static function geometric_mean($a) {
		foreach ($a as $i => $n) {
			$mul = $i == 0 ? $n : $mul * $n;
		}
		return pow($mul, 1 / count($a));
	}

	/**
	 * [calculateDonorResponseData description]
	 *
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public static function calculateDonorResponseData($database) {

		$donor_means = array();
		$d_geo_mean = array();

		foreach ($database["donor_visits"] as $donor_visits_id => $dataValue) {

			$donor_mean_id = $dataValue["donor_id"] . "_" . $dataValue["visit_year"] . "_" . $dataValue["visit_type_hai"];

			if (!isset($donor_means[$donor_mean_id])) {
				$donor_means[$donor_mean_id] = array();
				$donor_means[$donor_mean_id]["data_keys"] = array();
				$donor_means[$donor_mean_id]["visit"] = $dataValue["visit_type_hai"];
				$donor_means[$donor_mean_id]["donor_id"] = $dataValue["donor_id"];
				$donor_means[$donor_mean_id]["sum"] = array();
			}
			if (!isset($donor_means[$donor_mean_id]["data_keys"][$donor_visits_id])) {
				$donor_means[$donor_mean_id]["data_keys"][$donor_visits_id] = true;
			}
			foreach ($database["experimental_data"][$donor_visits_id] as $donor_visit_data) {
				// Process only HAI assay!!
				if ($donor_visit_data["assay"] === 5) {
					$donor_means[$donor_mean_id]["sum"][] = $donor_visit_data["data"];
				}
			}
			if (empty($donor_means[$donor_mean_id]["sum"])) {
				// unset($donor_means[$donor_mean_id]);
			}
		}

		// Calculate geo_mean values from data and pre-vaccine_resp
		$vaccine_resp = array();

		foreach ($donor_means as $donor_mean_id => $donor_means_value) {

			list($donor_id, $visit_year, $visit_type_hai) = explode('_', $donor_mean_id);
			$donor_id = intval($donor_id);
			$visit_year = intval($visit_year);

			$donor_visit_id = $donor_id . "_" . $visit_year;

			$responder = false;
			if ($donor_means_value["visit"] === "post") {
				$virusCount = count($donor_means_value["sum"]);
				$responseCount = 0;
				foreach ($donor_means_value["sum"] as $sumNumber) {
					if (intval($sumNumber) >= 40) {
						$responseCount++;
					}
				}
				if ($responseCount >= $virusCount) {
					$responder = 2;
				} else {
					$responder = 0;
				}
			}
			$geo_mean = 0;
			if (!empty($donor_means_value["sum"])) {
				$geo_mean = self::geometric_mean($donor_means_value["sum"]);
				$geo_mean = round($geo_mean, 2);
			}

			if (!isset($d_geo_mean[$donor_visit_id])) {
				$d_geo_mean[$donor_visit_id] = array();
			}

			if (!isset($d_geo_mean[$donor_visit_id][$donor_means_value["visit"]])) {
				$d_geo_mean[$donor_visit_id][$donor_means_value["visit"]] = $geo_mean;
			}

			// Add mean to each array to related donor
			foreach ($database["donor_visits"] as $donor_visits_id => $dataValue) {
				if ($dataValue["donor_id"] === $donor_id && $dataValue["visit_year"] === $visit_year && $dataValue["visit_type_hai"] === $visit_type_hai) {
					$database["donor_visits"][$donor_visits_id]["geo_mean"] = $geo_mean;
				}
			}

			if ($responder !== false) {
				if (!isset($vaccine_resp[$donor_visit_id])) {
					$vaccine_resp[$donor_visit_id] = $responder;
				}
			}
		}

		// Calculate d_geo_mean values from data
		foreach ($d_geo_mean as $donor_data_key => $donor_data_key_value) {
			list($donor_id, $visit_year) = explode('_', $donor_data_key);
			$donor_id = intval($donor_id);
			$visit_year = intval($visit_year);

			$d_geo_mean = "NA";

			if (isset($donor_data_key_value["post"]) && isset($donor_data_key_value["pre"])) {
				if ($donor_data_key_value["post"] > 0) {

					if ($donor_data_key_value["pre"] > 0) {
						$d_geo_mean = round($donor_data_key_value["post"] / $donor_data_key_value["pre"]);
					} else {
						$d_geo_mean = round($donor_data_key_value["post"]);
					}

				} else {
					echo ("INFO: geo_mean is 0. Skipping... \r\n");
				}
			} else {
				echo ("INFO: missing pre/post values to calculate d_geo_mean... \r\n");
			}

			foreach ($database["donor_visits"] as $dataKey => $dataValue) {
				if ($dataValue["donor_id"] === $donor_id && $dataValue["visit_year"] === $visit_year) {
					if ($d_geo_mean !== "NA") {
						$database["donor_visits"][$dataKey]["d_geo_mean"] = $d_geo_mean;
					}
				}
				if (isset($vaccine_resp[$donor_data_key])) {
					$database["donor_visits"][$dataKey]["vaccine_resp"] = $vaccine_resp[$donor_data_key];
				}
			}
		}

		$database = self::getDonorVirusResponse($database);

		return $database;
	}

	/**
	 * [getDonorVirusResponse description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public static function getDonorVirusResponse($database) {
		$donor_visits = array();
		$vaccine_resp = array();

		foreach ($database["donor_visits"] as $donor_visits_id => $donor_visits_value) {
			$donor_visit_id = $donor_visits_value["donor_id"] . "_" . $donor_visits_value["visit_year"];

			if (!isset($donor_visits[$donor_visit_id])) {
				$donor_visits[$donor_visit_id] = array();
			}

			foreach ($database["experimental_data"][$donor_visits_id] as $donor_visit_data) {
				// Process only HAI assay!!
				if ($donor_visit_data["assay"] === 5) {
					if (!isset($donor_visits[$donor_visit_id][$donor_visit_data["name"]])) {
						$donor_visits[$donor_visit_id][$donor_visit_data["name"]] = array();
					}
					if (!isset($donor_visits[$donor_visit_id][$donor_visit_data["name"]][$donor_visits_value["visit_type_hai"]])) {
						$donor_visits[$donor_visit_id][$donor_visit_data["name"]][$donor_visits_value["visit_type_hai"]] = $donor_visit_data["data"];
					}
				}
			}
			if (empty($donor_visits[$donor_visit_id])) {
				unset($donor_visits[$donor_visit_id]);
			}
		}

		foreach ($database["donor_visits"] as $donor_visits_id => $dataValue) {
			$donor_visit_id = $dataValue["donor_id"] . "_" . $dataValue["visit_year"];

			foreach ($database["experimental_data"][$donor_visits_id] as $donor_visit_data) {
				// Process only HAI assay!!
				if (isset($donor_visits[$donor_visit_id][$donor_visit_data["name"]])) {
					$main_values = $donor_visits[$donor_visit_id][$donor_visit_data["name"]];

					if (isset($main_values["post"]) && isset($main_values["pre"])) {
						$delta_single = round($main_values["post"] / $main_values["pre"]);
					} else {
						$delta_single = "NA";
						// print_r("===> ERROR: Cannot find pre and post values! Visit ID: ".$donor_visit_id." \r\n");
						// print_r(json_encode($donor_visits[$donor_visit_id], JSON_PRETTY_PRINT));
						// print_r(json_encode($database["donor_visits"][$donor_visits_id], JSON_PRETTY_PRINT));
						// print_r(json_encode($donor_visit_data["name"], JSON_PRETTY_PRINT));
						// exit;
					}
					if ($delta_single !== "NA") {
						$database["donor_visits"][$donor_visits_id]["delta_single"] = $delta_single;

						if (isset($database["donor_visits"][$donor_visits_id]["vaccine_resp"]) &&
							$database["donor_visits"][$donor_visits_id]["vaccine_resp"] === 2) {

							if (!isset($vaccine_resp[$donor_visit_id][$donor_visit_data["name"]])) {
								$vaccine_resp[$donor_visit_id][$donor_visit_data["name"]] = $delta_single;
							}
						}
					} else {
						if (isset($database["donor_visits"][$donor_visits_id]["vaccine_resp"])) {
							unset($database["donor_visits"][$donor_visits_id]["vaccine_resp"]);
						}
					}
				}

			}
		}

		foreach ($vaccine_resp as $vaccine_resp_key => $vaccine_resp_value) {

			list($donor_id, $visit_year) = explode('_', $vaccine_resp_key);
			$donor_id = intval($donor_id);
			$visit_year = intval($visit_year);

			$virusCount = count($vaccine_resp_value);
			$increaseCount = 0;
			$response = false;

			foreach ($vaccine_resp_value as $sumNumber) {
				if ($sumNumber !== "NA" && intval($sumNumber) >= 4) {
					$increaseCount++;
				}
			}

			if ($virusCount === 4) {
				if ($increaseCount >= 3) {
					$responder = 1;
				} else {
					$responder = 0;
				}
			} else if ($virusCount == 3) {
				if ($increaseCount >= 2) {
					$responder = 1;
				} else {
					$responder = 0;
				}
			} else {
				$responder = 3;
			}
			// Save modified values
			foreach ($database["donor_visits"] as $donor_visits_id => $dataValue) {
				if ($dataValue["donor_id"] === $donor_id && $dataValue["visit_year"] === $visit_year) {
					$database["donor_visits"][$donor_visits_id]["vaccine_resp"] = $responder;
				}
			}
		}

		// Remove dummy values
		foreach ($database["donor_visits"] as $donor_visits_id => $dataValue) {
			if (isset($dataValue["vaccine_resp"])) {
				if ($dataValue["vaccine_resp"] === 2 || $dataValue["vaccine_resp"] === 3 || trim($dataValue["vaccine_resp"]) === "") {
					unset($database["donor_visits"][$donor_visits_id]["vaccine_resp"]);
				}
			}
		}
		return $database;
	}
}
