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
    $projects = getProjects(array_keys($sensor_sets));

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
 * Get requested project(s) based on the "project" parameter in the URL
 * and the available projects
 */
function getProjects($availableProjects)
{
    $projects = $availableProjects;

    if (isset($_GET['project']) && strtolower($_GET['project']) != 'all') {
        $projects = array();
        $project = strtolower($_GET['project']);
        if (in_array($project, $availableProjects)) {
            $projects[] = $project;
        }
    }

    return $projects;
}


/**
 * Convert string of IDs/ID-ranges into an array containing all node IDs
 * 
 * retruns array of IDs (integers)
 */
function getNodesFromString($idsString, $unique = true)
{
    $idList = array();
    $ids = explode(',', $idsString);
    $ranges = array();
    foreach ($ids as $key => $id) {
        $id = trim($id);
        if (!empty($id)) {
            if (ctype_digit($id)) { // checks if all of the characters are numerical
                $idList[] = intval($id);
            } else {
                $ranges[] = $id;
            }
        }
    }
    foreach ($ranges as $rangeString) {
        $pattern = '/^(\d+)-(\d+)$/';
        if (preg_match($pattern, $rangeString, $matches)) {
            $rmin = intval($matches[1]);
            $rmax = intval($matches[2]);
            if ($rmin <= $rmax) {
                for ($id = $rmin; $id <= $rmax; $id++) {
                    $idList[] = intval($id);
                }
            }
        }
    }

    sort($idList, SORT_NUMERIC);
    return $unique ? array_values(array_unique($idList, SORT_NUMERIC)) : $idList;
}
