<?php
namespace CypryRadu\AdvancedFilter\ValueObject;

class DateVO
{
    const INVALID_DATE_MSG = 'Invalid date';

    private $dateObj;

    public function __construct($date = null, $fromFormat = 'Y-m-d')
    {
        if (is_null($date)) {
            $this->dateObj = new \Datetime();
            return;
        } else if (is_array($date)) {
            $strDate = $this->setDateFromArray($date);
        } else if (is_string($date)) {
            $strDate = $date;
            $this->dateObj = \DateTime::createFromFormat($fromFormat, $date);
        }

        // throw an exception if the date is invalid
        if (!$this->dateObj || ($this->dateObj->format($fromFormat) != $strDate)) {
            throw new \InvalidArgumentException(static::INVALID_DATE_MSG);
        }
    }

    public function format($format = 'Y-m-d')
    {
        return $this->dateObj->format($format);
    }

    public function diff(DateVO $dateToDiff, $unit = 'y')
    {
        $dateToDiff = new \Datetime($dateToDiff->format('Y-m-d'));
        $interval = $this->dateObj->diff($dateToDiff);

        return $interval->$unit;
    }

    public function addInterval($intervalStr)
    {
        $interval = new \DateInterval($intervalStr);
        $newDateObj = clone $this->dateObj;
        $newDateObj->add($interval);

        $newDateVO = new DateVO($newDateObj->format('Y-m-d'));

        return $newDateVO;
    }

    protected function setDateFromArray(array $date)
    {
        $strDate = implode('-', array($date['yyyy'], $date['mm'], $date['dd']));
        $this->dateObj = \DateTime::createFromFormat(
            'Y-m-d',
            $strDate
        );

        return $strDate;
    }

}
