<?php

namespace JambageCom\ExcelbillTtProducts\Model;

/**
* This file is part of the TYPO3 CMS project.
*
* It is free software; you can redistribute it and/or modify it under
* the terms of the GNU General Public License, either version 2
* of the License, or any later version.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*
* The TYPO3 project - inspiring people to share!
*/


class Cell
{
    protected $cell = null;
    protected $value = '';
    protected $mergeCells = '';

    public function setCell ($value) 
    {
        $this->cell = $value;
    }

    public function getCell () 
    {
        return $this->cell;
    }

    public function setValue ($value) 
    {
        $this->value = $value;
    }

    public function getValue () 
    {
        return $this->value;
    }

    public function setMergeCells ($value) 
    {
        $this->mergeCells = $value;
    }

    public function getMergeCells () 
    {
        return $this->mergeCells;
    }
}

