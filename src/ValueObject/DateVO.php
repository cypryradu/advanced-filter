<?php

namespace CypryRadu\AdvancedFilter\ValueObject;

/**
 * Encapsulates a date and performing some operations.
 *
 * This class is immutable
 *
 * @author Ciprian Radu <cypryradu@gmail.com>
 */
class DateVO
{
    const INVALID_DATE_MSG = 'Invalid date';

    private $dateObj;

    /**
     * Constructor.
     *
     * @param mixed  $date
     * @param string $fromFormat
     */
    public function __construct($date = null, $fromFormat = 'Y-m-d')
    {
        if (is_null($date)) {
            $this->dateObj = new \Datetime();

            return;
        } elseif (is_array($date)) {
            $strDate = $this->setDateFromArray($date);
        } elseif (is_string($date)) {
            $strDate = $date;
            $this->dateObj = \DateTime::createFromFormat($fromFormat, $date);
        }

        // throw an exception if the date is invalid
        if (!$this->dateObj || ($this->dateObj->format($fromFormat) != $strDate)) {
            throw new \InvalidArgumentException(static::INVALID_DATE_MSG);
        }
    }

    /**
     * formats the internal date, based on the given format.
     *
     * @param string $format
     *
     * @return string
     */
    public function format($format = 'Y-m-d')
    {
        return $this->dateObj->format($format);
    }

    /**
     * Calculates the difference from a given DateVO object.
     *
     * @param DateVO $dateToDiff
     * @param string $unit
     *
     * @return int
     */
    public function diff(DateVO $dateToDiff, $unit = 'y')
    {
        $dateToDiff = new \Datetime($dateToDiff->format('Y-m-d'));
        $interval = $this->dateObj->diff($dateToDiff);

        return $interval->$unit;
    }

    /**
     * Adds an string interval (see DateTime valid string intervals).
     *
     * @param mixed $intervalStr
     *
     * @return DateVO
     */
    public function addInterval($intervalStr)
    {
        $interval = new \DateInterval($intervalStr);
        $newDateObj = clone $this->dateObj;
        $newDateObj->add($interval);

        $newDateVO = new self($newDateObj->format('Y-m-d'));

        return $newDateVO;
    }

    /**
     * sets the internal DateTime object from an array.
     *
     * @param array $date
     *
     * @return string ISO Date
     */
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
