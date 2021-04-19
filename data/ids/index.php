<?php
// This code make node IDs available by project in json format.
// Data is based on the content of the sensorsets.json file in the root of this project.
//
// Usage:
//   /data/ids/                  returns all nodes IDs of all projects
//   /data/ids/?project=all      same as above
//   /data/ids/?project=utrecht  returns all nodes IDs of the Utrecht project
//
// Remarks:
// - Also the project name, description and number of nodes is provided in the results.
// - All IDs are reported individually (not in ranges as in the sensorsets.json file).
// - Misspelled project names, in the URL query, will return no results.


$sensorsetsFile = '../../sensorsets.json';
$resultSet = array();

if (file_exists($sensorsetsFile)) {
    $sensor_sets = json_decode(file_get_contents($sensorsetsFile), true);
    $projects = array_keys($sensor_sets);
    if (isset($_GET['project']) && $_GET['project'] != 'all') {
        if (in_array($_GET['project'], $projects)) {
            $projects = [$_GET['project']];
        } else {
            $projects = [];
        }
    }

    foreach ($projects as $project) {
        $projectData = array();
        $projectData['name'] = $project;
        $projectData['description'] = $sensor_sets[$project]['description'];
        $projectData['ids'] = getNodesFromString($sensor_sets[$project]['ids']);
        $projectData['amount'] = sizeof($projectData['ids']);

        $resultSet['project'][$project] = $projectData;
    }
}

header('Content-type: application/json');
echo json_encode($resultSet);


/**
 * Convert string of IDs/ID-ranges into an array containing all node IDs
 *
 * retruns array of IDs (integers)
 */
function getNodesFromString($idsString, $unique = true)
{
    $idList = array();
    $ids = explode(',', $idsString);
    foreach ($ids as $key => $id) {
        $id = trim($id);
        if (!empty($id)) {
            if (ctype_digit($id)) { // checks if all of the characters are numerical
                $idList[] = intval($id);
            } else {
                $pattern = '/^(\d+)-(\d+)$/';
                if (preg_match($pattern, $id, $matches)) {
                    $rmin = intval($matches[1]);
                    $rmax = intval($matches[2]);
                    if ($rmin <= $rmax) {
                        for ($id = $rmin; $id <= $rmax; $id++) {
                            $idList[] = intval($id);
                        }
                    }
                }
            }
        }
    }

    return $unique ? array_values(array_unique($idList, SORT_NUMERIC)) : $idList;
}
