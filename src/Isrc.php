<?php

namespace DanielZ\MusicCodes;

class Isrc
{
    const NO_DASH_PATTERN = '/^(ISRC[\W]*)?([A-Z]{2})([A-Z0-9]{3})([0-9]{2})([0-9]{5})([\W]*ISRC)?$/';
    const DASHED_PATTERN = '/^(ISRC[\W]*)?([A-Z]{2})-([A-Z0-9]{3})-([0-9]{2})-([0-9]{5})([\W]*ISRC)?$/';
    const MIN_ID = 1;
    const MAX_ID = 99999;

    /**
     * @var string
     */
    protected $country_code = '';

    /**
     * @var string
     */
    protected $issuer_code = '';

    /**
     * @var int
     */
    protected $year = 0;

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var bool
     */
    protected $is_valid = false;

    /**
     * Isrc constructor.
     * @param string $isrc
     */
    public function __construct($isrc = false)
    {
        if (is_string($isrc)) {
            $this->load($isrc);
        }
    }

    /**
     * @param string $isrc
     * @return Isrc
     */
    public function load($isrc)
    {
        $clean_isrc = strtoupper(trim($isrc));
        $matches = $this->getMatches($clean_isrc);

        if (empty($matches)) {
            $this->is_valid = false;
        } else {
            $this->country_code = $matches[2];
            $this->issuer_code = $matches[3];
            $this->year = (int)$matches[4];
            $this->id = (int)ltrim($matches[5], '0');

            $this->validate();
        }

        return $this;
    }

    /**
     * @param string $isrc
     * @return array
     */
    protected function getMatches($isrc)
    {
        preg_match(self::NO_DASH_PATTERN, $isrc, $matches);

        if (empty($matches)) {
            preg_match(self::DASHED_PATTERN, $isrc, $matches);
        }

        return $matches;
    }

    /**
     * @param string $country_code
     * @param string $issuer_code
     * @param string $year
     * @param string $id
     * @return Isrc
     */
    public static function fromParts($country_code, $issuer_code, $year, $id)
    {
        return new static(join('', [$country_code, $issuer_code, $year, $id]));
    }

    /**
     * @param int $max
     * @param bool $flip_year
     * @return bool
     */
    public function previous($max = self::MAX_ID, $flip_year = false)
    {
        $this->setId(min($max, $this->id - 1), $flip_year);

        return $this->is_valid;
    }

    /**
     * @param int $min
     * @param bool $flip_year
     * @return bool
     */
    public function next($min = self::MIN_ID, $flip_year = false)
    {
        $this->setId(max($min, $this->id + 1), $flip_year);

        return $this->is_valid;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getIsrc(true);
    }

    /**
     * @param bool $dashed
     * @return string
     */
    public function getIsrc($dashed = true, $prefixed = false)
    {
        $parts = [
            $this->getCountryCode(),
            $this->getIssuerCode(),
            $this->getYear(true),
            $this->getId(true),
        ];

        return ($prefixed ? 'ISRC ' : '') . join($dashed ? '-' : '', $parts);
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->country_code;
    }

    /**
     * @param string $country_code
     * @return Isrc
     */
    public function setCountryCode(string $country_code): Isrc
    {
        $this->country_code = $country_code;

        $this->validate();

        return $this;
    }

    /**
     * @return string
     */
    public function getIssuerCode(): string
    {
        return $this->issuer_code;
    }

    /**
     * @param string $issuer_code
     * @return Isrc
     */
    public function setIssuerCode(string $issuer_code): Isrc
    {
        $this->issuer_code = $issuer_code;

        $this->validate();

        return $this;
    }

    /**
     * @param bool $padded
     * @return int|string
     */
    public function getYear($padded = false)
    {
        return !$padded ? $this->year : str_pad($this->year, 2, '0', STR_PAD_LEFT);
    }

    /**
     * @param string|int $year
     * @param bool|int $reset_id_to
     * @return Isrc
     */
    public function setYear($year, $reset_id_to = false)
    {
        $int_year = (int)$year;

        if ($this->year != $int_year && is_numeric($reset_id_to)) {
            $this->setId($reset_id_to);
        }

        $this->year = $int_year;

        $this->validate();

        return $this;
    }

    /**
     * @param bool $padded
     * @return int|string
     */
    public function getId($padded = false)
    {
        return !$padded ? $this->id : str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * @param int|string $id
     * @param bool $flip_year
     * @return Isrc
     */
    public function setId($id, $flip_year = false): Isrc
    {
        $this->id = (int)$id;

        if ($flip_year) {
            if ($this->id < self::MIN_ID) {
                $this->year = (($this->year - 1) + 100) % 100;
                $this->id = self::MAX_ID;
            } elseif ($this->id > self::MAX_ID) {
                $this->year = ($this->year + 1) % 100;
                $this->id = self::MIN_ID;
            }
        }

        $this->validate();

        return $this;
    }

    /**
     *
     */
    protected function validate()
    {
        $this->is_valid = true;

        if (strlen($this->country_code) != 2) {
            $this->is_valid = false;
        }

        if (strlen($this->issuer_code) != 3) {
            $this->is_valid = false;
        }

        if ($this->year < 0 || $this->year > 99) {
            $this->is_valid = false;
        }

        if ($this->id < self::MIN_ID) {
            $this->is_valid = false;
        } elseif ($this->id > self::MAX_ID) {
            $this->is_valid = false;
        }
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->is_valid;
    }
}