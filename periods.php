<?php

/**
 * Converts periods JSON string to a human-readable string
 *
 * @param string $json
 * @param string $separator
 * @return string
 */
function periods(string $json, string $separator = "/") : string
{
    $dates = json_decode($json);
    $daysPeriods = [];
    $output = '';

    foreach ($dates as $date) {
        $arrayDateFrom = explode($separator, $date->from);
        $arrayDateTo = explode($separator, $date->to);

        $dayFrom = (int) $arrayDateFrom[1];
        $dayTo = (int) $arrayDateTo[1];

        // Marks if one of existing periods in "daysPeriod" array was updated
        $updateMarker = false;

        // Checks periods for continuance and intersections
        foreach ($daysPeriods as &$period) {
            // Checks if current period continues any other period beginning one day after the end of any other
            // or has intersection beginning before the end of any other
            if (($dayFrom - $period['to'] <= 1) && $dayTo > $period['to']) {
                $period['to'] = $dayTo;
                $updateMarker = true;
            }

            // Checks if current period is continued by any other period ending one day before the beginning
            // of any other or has intersection ending before the beginning of any other
            if (($dayTo - $period['from'] >= -1) && $dayFrom < $period['from']) {
                $period['from'] = $dayFrom;
                $updateMarker = true;
            }
        }

        // If current period does not continue or intersect with any other, puts it into periods array
        if (! $updateMarker) {
            $daysPeriods[] = ['from' => $dayFrom, 'to' => $dayTo];
        }
    }

    if (count($dates) > 0) {
        $idx = array_rand($dates);
        $month = explode($separator, $dates[$idx]->from)[0];
        $output .= DateTime::createFromFormat('m', $month)->format('M');
    }

    $length = count($daysPeriods);
    for ($i = 0; $i < $length; $i++) {
        $from = $daysPeriods[$i]['from'];
        $to = $daysPeriods[$i]['to'];

        $output .= " $from-$to";

        if ($i < $length-1) {
            $output .= ",";
        }
    }

    return $output . "\n";
}
