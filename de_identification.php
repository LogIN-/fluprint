<?php

/**
 * Custom salt used in hashing
 * @var string
 */
$salt = "Zb2asdf45423j345sdw6bv23t";

/**
 * String os the samples you wish to de-identify in array format
 * Example: $donor_ids = ["PUD034-12", "PUD034-13", "PUD034-14"];
 * @var [type]
 */
$donor_ids = ["PUD034-12", "PUD034-13", "PUD034-14"];

$config = json_decode(file_get_contents("config/configuration.json"), true);

echo "====>>>> Removing IDs from data \r\n";

/**
 * Loop all CSV files in directory and remap subject IDs
 * @var RecursiveDirectoryIterator
 */
$di = new RecursiveDirectoryIterator('data/upload');
$iterator = new RecursiveIteratorIterator($di);

$file_counter = 1;
$filename_mappings = "";
$donor_mappings = "";
foreach ($iterator as $filename => $file) {
	if ($file->getExtension() === "csv") {

		$filePath = $file->getPathname(); // "data/upload/21/luminex_21.csv"; //

		$studyID = substr($filePath, 0, strrpos($filePath, '/'));
		$studyID = intval(substr($studyID, strrpos($studyID, '/') + 1));

		$donor_id_field_name = $config["import"]["mapping"][$studyID]["donor_id"]["field_name"];
		$visit_id_field_name = $config["import"]["mapping"][$studyID]["visit_id"]["field_name"];
		$visit_id_regex = $config["import"]["mapping"][$studyID]["visit_id"]["regex"];

		echo "Processing: " . $filePath . "\r\n";
		// Read contents from a file
		$currentFileData = file_get_contents($filePath);

		$column_person = [];
		$column_sample = [];
		$csv = array_map("str_getcsv", file($filePath));
		$header = array_shift($csv);

		foreach ($header as &$header_item) {
			$header_item = strtolower($header_item);
			$header_item = preg_replace('/\s+/', '_', $header_item);
		}

		// Find index of donor column
		$col = array_search($donor_id_field_name, $header);
		foreach ($csv as $row) {
			if (isset($row[$col])) {
				$column_person[] = $row[$col];
			}
		}

		// Find index of person column
		$col = array_search($visit_id_field_name, $header);
		foreach ($csv as $row) {
			if (isset($row[$col])) {
				$column_sample[] = $row[$col];
			}
		}

		$donors = $donor_ids;
		if (count($column_person) > 0) {
			$donors = array_unique(array_merge($donors, $column_person));
		}

		foreach ($donors as $donor_id) {
			$person_id = $donor_id;
			$sample_id = preg_replace('/[a-zA-Z]/', '', $person_id);

			$replace = md5($donor_id . $salt);

			$donor_mappings .= $person_id . " - " . $replace . "\r\n";

			// Make sure that sample ends with number
			$replaceSample = $replace . "-666";

			// PERSON
			// ,"PUD034-001",
			$currentFileData = str_replace(',"' . $person_id . '",', ',"' . $replace . '",', $currentFileData);
			// PUD034-001",
			$currentFileData = str_replace($person_id . '",', $replace . '",', $currentFileData);
			// ,"PUD034-001
			$currentFileData = str_replace(',"' . $person_id, ',"' . $replace, $currentFileData);

			// ,PUD034-001,
			$currentFileData = str_replace(',' . $person_id . ',', ',' . $replace . ',', $currentFileData);
			// PUD034-001,
			$currentFileData = str_replace($person_id . ',', $replace . ',', $currentFileData);
			// ,PUD034-001
			$currentFileData = str_replace(',' . $person_id, ',' . $replace, $currentFileData);

			// SAMPLE
			// PUD034-004-V2-2012
			// PUD034-2009-018-025V3
			// 2008-PUD034-001V1
			$currentFileData = str_replace('-' . $sample_id . '-', '-' . $replaceSample . '-', $currentFileData);
			$currentFileData = str_replace($sample_id . '-', $replaceSample . '-', $currentFileData);
			$currentFileData = str_replace('-' . $sample_id, '-' . $replaceSample, $currentFileData);

			$currentFileData = str_replace($sample_id . 'V', $replaceSample . 'V', $currentFileData);
			$currentFileData = str_replace($sample_id . ' V', $replaceSample . ' V', $currentFileData);
		}

		$filename_old = $file->getBasename('.csv');
		$filename_new = $file_counter;

		$filename_mappings .= $filename_old . " - " . $filename_new . "\r\n";

		$file_path_new = str_replace($filename_old, $filename_new, $filePath);

		// Write the contents to the new file
		file_put_contents($file_path_new, $currentFileData, LOCK_EX);
		unlink($filePath);
		$file_counter++;

	} else {
		echo "Skipping, file not CSV: " . $file->getPath() . " \r\n";
	}
}

file_put_contents("./data/upload/file_mappings", $filename_mappings);
file_put_contents("./data/upload/donor_mappings", $donor_mappings);

echo "====>>>> Removing unused columns from data \r\n";

// Now remove all columns we don't use
$keep_columns = ["person", "sample", "day", "project", "gender", "ethnicity", "visit", "year", "age", "bmi_data", "vaccination_data", "statin", "FluVaccHx", "TotalFluVacc_Life", "FluVacc_1YrPrior", "VaccType_1YrPrior", "FluVacc_2YrsPrior", "VaccType_2YrsPrior", "FluVacc_3YrsPrior", "VaccType_3YrsPrior", "FluVacc_4YrsPrior", "VaccType_4YrsPrior", "FluVacc_5YrsPrior", "VaccType_5YrsPrior", "Flu_MD_dx", "Flu_Hospital", "assay", "stim", "analyte_generic_name", "analyte_specific_name", "units", "n", "units"];
$di = new RecursiveDirectoryIterator('data/upload');
$iterator = new RecursiveIteratorIterator($di);
foreach ($iterator as $filename => $file) {
	if ($file->getExtension() === "csv") {

		$filePath = $file->getPathname(); // "data/upload/21/luminex_21.csv"; //

		$studyID = substr($filePath, 0, strrpos($filePath, '/'));
		$studyID = intval(substr($studyID, strrpos($studyID, '/') + 1));

		echo "Processing: " . $filePath . "\r\n";

		$csv = array_map("str_getcsv", file($filePath));
		$header = array_shift($csv);

		foreach ($header as &$header_item) {
			$header_item = strtolower($header_item);
			$header_item = preg_replace('/\s+/', '_', $header_item);
		}

		$keep_indexes = [];
		foreach ($keep_columns as $keep_column) {

			$keep_column = strtolower($keep_column);
			$keep_column = preg_replace('/\s+/', '_', $keep_column);

			$index = array_search($keep_column, $header);
			if ($index !== false && is_numeric($index)) {
				$keep_indexes[$keep_column] = $index;
			}
		}

		if (count($keep_indexes) > 0) {
			$keeps = array_values($keep_indexes);
			sort($keeps);

			foreach ($keeps as &$keep) {
				$keep += 1;
			}

			## https://csvkit.readthedocs.io/en/latest/scripts/csvcut.html
			$clean_cmd = "csvcut -c " . implode(",", $keeps) . " " . $filePath . " > " . $filePath . "_clean && rm " . $filePath . " && mv " . $filePath . "_clean " . $filePath;
			echo $clean_cmd . "\r\n";

			exec($clean_cmd);
		} else {
			echo "Skipping, no keep indexes found: " . $filePath . " \r\n";
		}
	} else {
		echo "Skipping, file not CSV: " . $file->getPath() . " \r\n";
	}
}
?>