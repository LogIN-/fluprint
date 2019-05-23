<?php

/**
 * @Author: LogIN
 * @Date:   2017-04-02 10:58:06
 * @Last Modified by:   LogIN
 * @Last Modified time: 2017-08-07 17:12:36
 */

namespace SysLog\Helpers\Functions;
use SysLog\Helpers\Functions\PHPFunctions as PHPFunc;

class RemapFunctions {

	/**
	 * [removeExperimentalDataFeatures description]
	 * @return [type] [description]
	 */
	public static function removeExperimentalDataFeatures($experimental_data) {
		$data = $experimental_data;

		$remove_data_mapping = array("L50_CHEX1", "L50_CHEX2", "L50_CHEX3", "L50_CHEX4",
			"CHEX1", "CHEX2", "CHEX3", "CHEX4",
			"lymphocytes", "CMV Titer", "CMV Status", "EBV Status", "EBV Titer",
			"CXCR3-FMO CD8+ T cells", "CXCR3-FMO B cells", "CXCR3-FMO CD33+ monocytes",
			"CXCR3-FMO CD4+ T cells", "CXCR3-FMO CD8+ T cells", "CXCR3-FMO NK cells",
			"Intact cells", "Intact singlets", "non-T lymphocytes", "non-TFH CD4+ T cells",
			"non-TFH CD8+ T cells", "nonB-nonT-nonmonocyte-nonbasophils", "nonbasophils",
			"Non BT: pSTAT1", "Non BT: pSTAT3", "Non BT: pSTAT5", "non-granulocytes", "viable/singlets",
			"nonmonocyte-nonbasophils", "nonNK-nonB-nonT-nonmonocyte-nonbasophils", "viable",
			"CD16- monocytes", "CD161- NK cells", "CD161- NKT cells", "CD161-CD4+ T cells", "CD161-CD45RA- Tregs",
			"CD161-CD8+ T cells", "CD20- CD3- lymphocytes", "CD27-CD8+ T cells", "CD28-CD8+ T cells",
			"CD4+CD27- T cells", "CD4+CD28- T cells", "CD57- NK cells", "CD57-CD4+ T cells", "CD57-CD8+ T cells",
			"CD85j-CD4+ T cells", "CD85j-CD8+ T cells", "CD94- NK cells", "CD94-CD4+ T cells", "CD94-CD8+ T cells",
			"HLADR- NK cells", "HLADR-CD38-CD4+ T cells", "HLADR-CD38-CD8+ T cells", "ICOS-CD4+ T cells",
			"ICOS-CD8+ T cells", "non-naive CD4+ T cells", "non-naive CD8+ T cells", "PD1-CD4+ T cells",
			"PD1-CD8+ T cells", "IFNa_non-granulocytes", "IL-10_non-granulocytes", "IL-21_non-granulocytes",
			"IL-6_non-granulocytes", "IL-7_non-granulocytes", "LPS_non-granulocytes", "PMA_Iono_non-granulocytes", "Unstim_non-granulocytes");

		foreach ($data as $experimental_key => $experimental_value) {

			// Map inserted donor_visits IDs to experimental_data
			foreach ($experimental_value as $experimental_data_key => $experimental_data_value) {
				$test_name = $experimental_data_value["name_formatted"];
				foreach ($remove_data_mapping as $remove_data_mapping_key => $remove_data_mapping_value) {
					if ($test_name === self::sanitizeExperimentalName($remove_data_mapping_value)) {
						unset($data[$experimental_key][$experimental_data_key]);
					}
				}
			}
		}

		return $data;
	}

	/**
	 * [removeExperimentalDataPostFeatures description]
	 * @return [type] [description]
	 */
	public static function removeExperimentalDataPostFeatures($experimental_data) {
		$data = $experimental_data;

		$remove_data_mapping = array("Perth", "A Victoria", "Florida", "Lee",
			"Wisconsin", "A/Solomon Island/3/06", "B Mass", "Brisbane", "H3N2",
			"B/Malaysia/2506/04", "Puerto Rico", "A/Wisconsin/67/05", "South Dakota", "California", "Uruguay", "A Tex",
			"A/California/7/09 (H1N1)", "A/California/7/2009", "A/Victoria/361/2011", "B/Wisconsin/1/2010", "A/Victoria/361/2011(H3N2)",
			"B/Massachusetts/2/12");

		foreach ($data as $experimental_key => $experimental_value) {

			// Map inserted donor_visits IDs to experimental_data
			foreach ($experimental_value as $experimental_data_key => $experimental_data_value) {
				$test_name = $experimental_data_value["name_formatted"];
				foreach ($remove_data_mapping as $remove_data_mapping_key => $remove_data_mapping_value) {
					if ($test_name === self::sanitizeExperimentalName($remove_data_mapping_value)) {
						unset($data[$experimental_key][$experimental_data_key]);
					}
				}
			}
		}

		return $data;
	}

	/**
	 * [sanitizeAssay description]
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	public static function sanitizeExperimentalName($string) {
		$data = PHPFunc::normalizeDataNames($string);
		return $data;
	}
}
