<?php

/**
 * Created by PhpStorm.
 * User: alexandert
 * Date: 9/15/16
 * Time: 3:22 PM
 */
class CsvSmartReader
{
    /** @var string */
    protected $fileName = '';

    /** @var array */
    protected $sheetData = [];

    /** @var array[int] */
    private $columns = [];

    /**
     * CsvSmartReader constructor.
     *
     * @param string $fileName path to csv file.
     * @throws Exception
     */
    public function __construct($fileName)
    {
        $this->fileName = $fileName;

        if (!file_exists($this->fileName)) {
            throw new \Exception("File not exists. Try full path");
        }

        $this->fileToSheet();
    }

    /**
     * Return data from cell. Name cell should be like R14.
     *
     * @param string $cellName
     * @return mixed
     */
    public function getCell($cellName)
    {
        list($columnIndex, $rowNum) = $this->parseCellName($cellName);
        return $this->sheetData[$rowNum][$this->getColumnIndex($columnIndex)];
    }


    /**
     * Get data from column. can use follow formats:
     * "R" - return all data from column R
     * "R3" - return all data from column R starting from row 3
     * "R3-15" - return all data from column R between rows 3 and 15, including 3 and 15 rows.
     *
     * @param string $column
     * @param bool $cutEmptyTail if set to true - empty values will be removed from end.
     * @return array
     */
    public function getColumnData($column, $cutEmptyTail = true)
    {
        $length = null;

        $requestedDiapason = explode("-", $column);
        list($columnName, $startFromRow) = $this->parseCellName($requestedDiapason[0]);
        $startFromRow = $startFromRow ? : 1;

        if (count($requestedDiapason) == 2) {
            $length = $requestedDiapason[1] - $startFromRow + 1;
        }

        $columnIndex = $this->getColumnIndex($columnName);
        $columnData = [];
        $startIndex = $startFromRow - 1;

        $sliced = $this->sheetData;
        if ($startIndex || $length !== null) {
            $sliced = array_slice($sliced, $startIndex, $length);
        }

        foreach ($sliced as $data) {
            $columnData[$startFromRow] = $data[$columnIndex];
            $startFromRow++;
        }

        if ($cutEmptyTail) {
            $lastNonEmptyElement = 0;
            $firstIndex = false;

            foreach ($columnData as $index => $data) {
                if(!$firstIndex) {
                    $firstIndex = $index;
                }
                if ($data) {
                    $lastNonEmptyElement = $index;
                }
            }
            $columnData = array_slice($columnData, 0, $lastNonEmptyElement-$firstIndex+1, true);
        }
        return $columnData;
    }

    /**
     * Get data from row. Can add diapason for columns.
     *
     * Allowed row format: 5, 5-14
     * @param string $row
     * @param string|A $columnStart
     * @param string|null $columnEnd
     * @return array
     */
    public function getRowData($row, $columnStart = "A", $columnEnd = null)
    {
        $rowsData = [];
        $requestedRows = explode('-', $row);

        if (count($requestedRows) == 2) {
            $rowsData = array_slice($this->sheetData, $requestedRows[0]-1, $requestedRows[1] - $requestedRows[0] +1, true);
        } else {
            $rowsData[$row] = $this->sheetData[$row];
        }

        $startIndex = $this->getColumnIndex($columnStart);
        $length = null;
        if ($columnEnd) {
            $length = $this->getColumnIndex($columnEnd) - $startIndex + 1;
        }
        foreach ($rowsData as &$rowData) {
            $rowData = array_slice($rowData, $startIndex, $length, true);
        }

        $columnsFlipped = array_flip($this->getColumnsAddresses());
        $clearColumnData = [];

        foreach ($rowsData as $rowNum => $currentRowData) {
            $clearColumnData[$rowNum] = [];
            foreach ($currentRowData as $colNum => $data) {
                $clearColumnData[$rowNum][$columnsFlipped[$colNum]] = $data;
            }
        }

        return $clearColumnData;
    }

    protected function fileToSheet()
    {
        $rowIndex = 1;
        $chars = [];
        for ($char = 'A'; $char <= 'Z'; $char++) {
            $chars[] = $char;
        }

        if (($handle = fopen($this->fileName, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $colNum = 0;
                foreach ($data as $col) {
                    if ($rowIndex == 1) {
                        $this->columns[$chars[$colNum]] = $colNum;
                    }
                    $this->sheetData[$rowIndex][$colNum] = $col;
                    $colNum++;
                }
                $rowIndex++;
            }
            fclose($handle);
        }
        unset($chars);
    }

    /**
     * @return array[int]
     */
    protected function getColumnsAddresses()
    {
        return $this->columns;
    }

    /**
     * @param string $cell
     * @return array[string, int|void]
     */
    private function parseCellName($cell)
    {
        preg_match('/([A-Z]+)(\d*)/', $cell, $matches);
        return [$matches[1], $matches[2]];
    }

    /**
     * @param string $columnName
     * @return int
     */
    private function getColumnIndex($columnName)
    {
        return $this->getColumnsAddresses()[$columnName];
    }
}