<?php

/**
 * @Author: LogIN
 * @Date:   2017-04-02 10:48:16
 * @Last Modified by:   LogIN
 * @Last Modified time: 2017-07-31 12:26:28
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_time_limit(0);
ini_set('memory_limit', '25000M');
ini_set("auto_detect_line_endings", true);
mb_internal_encoding('UTF-8');

const ROOT_DIR = __DIR__ . '/../';
chdir(ROOT_DIR);

const DATA_DIR = ROOT_DIR . 'data/upload/';

// Import Composer
require_once ROOT_DIR . 'vendor/autoload.php';

$cmd = new \Commando\Command();

$cmd->option('t')
	->aka('update-type')
	->require(false);

$cmd->option('f')
	->aka('stats-feature')
	->describedAs('Statistic feature/outcome we want to calculate stats. vaccine_resp')
	->require(false);

$cmd->option('d')
	->aka('detail-feature')
	->describedAs('Statistic features we want to limit. Comma delimited: IFNa_CD8_pos_CD45RA_neg__pSTAT5,IL_neg_6_Mono_pSTAT3')
	->require(false);

$cmd->option('q')
	->aka('quiet')
	->describedAs('No info messages!')
	->require(false)
	->boolean()
	->default(false);

$cmd->option('s')
	->aka('stats')
	->aka('display-stats')
	->describedAs('Specify if you want to display simple import timing stats')
	->boolean()
	->default(true);

if ($cmd['display-stats'] === true) {
	$timeImportStart = microtime(true);
}
$actionType = $cmd['update-type'];
$quiet = $cmd['quiet'];

$import = new SysLog\Importer($quiet);

if ($actionType === "import") {

	$filenames = array();
	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DATA_DIR));

	foreach ($iterator as $file) {
		if ($file->isDir()) {
			continue;
		}

		$filePath = realpath($file->getPathname());
		$ext = pathinfo($filePath, PATHINFO_EXTENSION);

		if (strpos($filePath, 'ignore') !== false || strpos($filePath, 'finished') !== false) {
			echo "SKIPPING: ignore param detected! " . $filePath . "\r\n";
			continue;
		}

		if (isset($ext) && ($ext !== "" && $ext === "csv")) {
			$filenames[] = array(
				'filePath' => $filePath,
				'extension' => $ext);
		} else {
			echo "SKIPPING: Unknown file extension! " . $filePath . "\r\n";
		}
	}

	$import->clearDatabase();

	foreach ($filenames as $filename) {

		/**
		 * Initialize basic configuration variable
		 * needed for Importer class
		 */
		$studyID = substr($filename['filePath'], 0, strrpos($filename['filePath'], '/'));
		$studyID = substr($studyID, strrpos($studyID, '/') + 1);

		$config = array(
			"importFile" => $filename['filePath'],
			"fileType" => $filename['extension'],
			"studyID" => intval($studyID),
			"actionType" => $actionType,
		);

		$import->setConfiguration($config);

		if ($cmd['update-type'] === "import" && $filename['extension'] === "csv") {

			$import->mainDataImport();

			if (count($import->errors) > 0) {
				foreach ($import->errors as $errors) {
					echo "ERROR: " . $errors . "\r\n";
				}
				exit;
			}
		}
	}

	$import->processVisitInformation();

}

if ($cmd['display-stats'] === true && $quiet !== true) {

	$timeImportEnd = microtime(true);
	$totalImportTime = round(($timeImportEnd - $timeImportStart) / 60, 4);

	echo "\r\nTotal Processing time: " . $totalImportTime . " (min)\r\n";
}
