<?php

/**
 * Created by PhpStorm.
 * User: alexandert
 * Date: 9/16/16
 * Time: 2:56 PM
 */

require_once "../Classes/CsvSmartReader.php";

/**
 * Class CsvSmartReaderTest.
 *
 *  @covers CsvSmartReader
 */
class CsvSmartReaderTest extends PHPUnit_Framework_TestCase
{
    /** @var CsvSmartReader */
    protected $csvReader = null;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->csvReader = new CsvSmartReader("example.csv");
    }

    /**
     * Data provider for testGetCell.
     *
     * @see testGetCell
     * @return array
     */
    public static function getCellDataProvider()
    {
        return [
            'simpleCell' => ['cell' => 'C2', 'expectedValue' => 'WildAndWooly'],
            'simpleCell2' => ['cell' => 'E4', 'expectedValue' => 'WILD'],
            'combineCell' => ['cell' => 'E2', 'expectedValue' => 'Symbol List'],
            'combineCellEmpty' => ['cell' => 'D2', 'expectedValue' => ''],
            'doubleIndexColumn' => ['cell' => 'AO2', 'expectedValue' => 'Myst Symbol Base Game'],
        ];
    }

    /**
     * Should return correct cell value.
     *
     * @covers CsvSmartReader::getCell()
     * @dataProvider getCellDataProvider
     * @param string $cell
     * @param mixed $expectedValue
     */
    public function testGetCell($cell, $expectedValue)
    {
        $this->assertEquals($expectedValue, $this->csvReader->getCell($cell));
    }

    /**
     * Data provider for testGetColumnData.
     *
     * @see testGetColumnData
     * @return array
     */
    public static function getColumnDataProvider()
    {
        return [
            'fullColumnWithClearEmptyTail' => ['column' => 'P', 'cutEmptyTail' => true, 'expectedResult' => [
                1 => '','Symbol Combos','H1','H1','H1','H2','H2','H2','H3','H3','H3','M1','M1','M1','M2','M2','M2',
                'M3','M3','M3','L1','L1','L1','L2','L2','L2','L3','L3','L3','L4','L4','L4','BONUS','BONUS','BONUS']
            ],

            'fromRow3ToRow15' => ['column' => 'P3-15', 'cutEmptyTail' => true, 'expectedResult' => [
                3 => 'H1','H1','H1','H2','H2','H2','H3','H3','H3','M1','M1','M1','M2']
            ],

            'fromRow4' => ['column' => 'P4', 'cutEmptyTail' => true, 'expectedResult' => [
                4 => 'H1','H1','H2','H2','H2','H3','H3','H3','M1','M1','M1','M2','M2','M2',
                'M3','M3','M3','L1','L1','L1','L2','L2','L2','L3','L3','L3','L4','L4','L4','BONUS','BONUS','BONUS']
            ],

            'notClearEmptyTail' => ['column' => 'P', 'cutEmptyTail' => false, 'expectedResult' => [
                1 => '','Symbol Combos','H1','H1','H1','H2','H2','H2','H3','H3','H3','M1','M1','M1','M2','M2','M2',
                'M3','M3','M3','L1','L1','L1','L2','L2','L2','L3','L3','L3','L4','L4','L4','BONUS','BONUS','BONUS','',
                '','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',
                '','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',
                '','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',
                '','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',
                '','','','','','','','','','','','','','','']
            ],
        ];
    }

    /**
     * Should return values according provided rule.
     *
     * @covers CsvSmartReader::getColumnData()
     * @dataProvider getColumnDataProvider
     * @param string $column
     * @param bool $cutEmptyTail
     * @param array $expectedResult
     */
    public function testGetColumnData($column, $cutEmptyTail, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->csvReader->getColumnData($column, $cutEmptyTail));
    }

    /**
     * Data provider for testGetRow.
     *
     * @see testGetRowData
     * @return array
     */
    public static function getGetRowData()
    {
        return [
            'row9FromPtoQ' => ['row' => 9, 'columnStart' => "P", 'columnEnd' => "U", 'expectedData' => [
                    9 => ['P' => 'H3', 'Q' => 'H3', 'R' => 'H3', 'S' => 'H3', 'T' => 'H3', 'U' => 60]
                ]
            ],
            'row9-11FromPtoQ' => ['row' => '9-11', 'columnStart' => "P", 'columnEnd' => "U", 'expectedData' => [
                    9 => ['P' => 'H3', 'Q' => 'H3', 'R' => 'H3', 'S' => 'H3', 'T' => 'H3', 'U' => 60],
                    10 => ['P' => 'H3', 'Q' => 'H3', 'R' => 'H3', 'S' => 'H3', 'T' => '', 'U' => 25],
                    11 => ['P' => 'H3', 'Q' => 'H3', 'R' => 'H3', 'S' => '', 'T' => '', 'U' => 10],
                ]
            ],
        ];
    }

    /**
     * Should return values from provided rows diapason.
     *
     * @covers CsvSmartReader::getRowData
     * @dataProvider getGetRowData
     * @param string $row
     * @param string $columnStart
     * @param null|string $columnEnd
     * @param array $expectedData
     */
    public function testGetRowData($row, $columnStart, $columnEnd = null, $expectedData)
    {
        $this->assertEquals($expectedData, $this->csvReader->getRowData($row, $columnStart, $columnEnd));
    }

}
