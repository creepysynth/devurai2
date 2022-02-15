<?php
require_once 'exceptions.php';

class Periods {
    protected array $dates;
    private string $separator;

    /**
     * @throws InvalidJSON|InvalidDate
     */
    public function __construct(string $json, $separator="/")
    {
        $this->setDates($json, $separator);
        $this->separator = $separator;
    }

    /**
     * @throws InvalidJSON|InvalidDate
     */
    protected function setDates($json, $separator)
    {
        if (! $dates = json_decode($json)) {
            throw new InvalidJSON("Invalid JSON string!");
        }

        $this->validateDates($dates, $separator);

        $this->dates = $dates;
    }

    /**
     * @throws InvalidDate
     */
    public function validateDates($dates, $separator)
    {
        $month = null;
        $year = null;

        foreach ($dates as $date) {
            $arrayDateFrom = explode($separator, $date->from);
            $arrayDateTo = explode($separator, $date->to);

            if (count($arrayDateFrom) < 3 || count($arrayDateTo) < 3) {
                throw new InvalidDate("One or more dates have invalid separator!" );
            }

            $monthFrom = (int) $arrayDateFrom[0];
            $dayFrom = (int) $arrayDateFrom[1];
            $yearFrom = (int) $arrayDateFrom[2];

            $monthTo = (int) $arrayDateTo[0];
            $dayTo = (int) $arrayDateTo[1];
            $yearTo = (int) $arrayDateTo[2];

            if(!checkdate($monthFrom, $dayFrom, $yearFrom) || !checkdate($monthTo, $dayTo, $yearTo)) {
                throw new InvalidDate("One or more dates have invalid format!");
            }

            if (is_null($month)) {
                $month = $monthFrom;
            }

            if (is_null($year)) {
                $year = $yearFrom;
            }

            if ($monthFrom != $month || $monthTo != $month) {
                throw new InvalidDate("Dates are not within one month!");
            }

            if ($yearFrom != $year || $yearTo != $year) {
                throw new InvalidDate("Dates are not within one year!");
            }
        }
    }

    /**
     * Checks days periods if they have continuance and intersections and unites them if they do.
     * Returns a new array of checked periods.
     *
     */
    public function makePeriodsArray($dates, $separator): array
    {
        $daysPeriods = [];

        foreach ($dates as $date) {
            $arrayDateFrom = explode($separator, $date->from);
            $arrayDateTo = explode($separator, $date->to);

            $dayFrom = (int) $arrayDateFrom[1];
            $dayTo = (int) $arrayDateTo[1];

            // Marks if one of existing periods in "$daysPeriods" array was updated
            $updateMarker = false;

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

        return $daysPeriods;
    }

    public function humanReadable() {
        $daysPeriods = $this->makePeriodsArray($this->dates, $this->separator);

        $idx = array_rand($this->dates);
        $month = explode($this->separator, $this->dates[$idx]->from)[0];
        $output = DateTime::createFromFormat('m', $month)->format('M');

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
}