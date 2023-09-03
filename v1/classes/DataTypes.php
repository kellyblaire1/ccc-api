<?php
trait DataTypes
{

    public function varchar($size)
    {
        return "VARCHAR({$size})";
    }

    public function char($size)
    {
        return "CHAR({$size})";
    }

    public function longtext()
    {
        return "LONGTEXT";
    }

    public function text($size)
    {
        return "TEXT({$size})";
    }

    public function mediumtext()
    {
        return "MEDIUMTEXT";
    }

    public function int($size)
    {
        return "INT({$size})";
    }

    public function tinyint($size)
    {
        return "TINYINT({$size})";
    }

    public function bigint($size)
    {
        return "BIGINT({$size})";
    }
    public function double()
    {
        return "DOUBLE PRECISION";
    }
    public function decimal($size, $d)
    {
        return "DECIMAL({$size},{$d})";
    }
    public function date()
    {
        return "DATE";
    }
    public function datetime()
    {
        return "DATETIME";
    }
    public function timestamp()
    {
        return "TIMESTAMP";
    }

    public function foreignKey($column,$refTbl,$refColumn)
    {
        return "FOREIGN KEY ({$column}) REFERENCES {$refTbl}({$refColumn}) ON DELETE CASCADE";
        // return "FOREIGN KEY ({$column}) REFERENCES {$refTbl}({$refColumn})";
    }
}