<?php

namespace DanielZ\MusicCodes;

class Isrc
{
    const NO_DASH_PATTERN = '/^(ISRC[\W]*)?([A-Z]{2})([A-Z0-9]{3})([0-9]{2})([0-9]{5})([\W]*ISRC)?$/';
    const DASHED_PATTERN = '/^(ISRC[\W]*)?([A-Z]{2})-([A-Z0-9]{3})-([0-9]{2})-([0-9]{5})([\W]*ISRC)?$/';
    const PREFIX_PATTERN = '/^([A-Z]{2})-?([A-Z0-9]{3})$/';
    const MIN_ID = 1;
    const MAX_ID = 99999;

    protected static $ZERO_IDS_ALLOWED = false;
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
            $this->country_code = '';
            $this->issuer_code = '';
            $this->year = -1;
            $this->id = -1;
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
     * Trigger this function to allow zero ids (e.g. to count them as valid)
     *
     * @param boolean $valid
     *
     * @return void
     */
    public static function treatZeroIdsAsValid($valid)
    {
        static::$ZERO_IDS_ALLOWED = $valid;
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
        return $this->getIsrc(true, false);
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
     * @param bool $dashed
     * @return string
     */
    public function getPrefix($dashed = true): string
    {
        return join($dashed ? '-' : '', [$this->getCountryCode(), $this->getIssuerCode()]);
    }

    /**
     * @param string $prefix
     * @return Isrc
     */
    public function setPrefix($prefix): Isrc
    {
        preg_match(self::PREFIX_PATTERN, $prefix, $matches);

        if (empty($matches)) {
            $this->country_code = '';
            $this->issuer_code = '';
        } else {
            $this->country_code = $matches[1];
            $this->issuer_code = $matches[2];
        }

        $this->validate();

        return $this;
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
        $this->country_code = strtoupper($country_code);

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
        $this->issuer_code = strtoupper($issuer_code);

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
        $this->year = (int)$year;

        if (is_numeric($reset_id_to)) {
            $this->setId($reset_id_to, false);
        }

        $this->validate();

        return $this;
    }

    /**
     * @param bool $padded
     * @return int|string
     */
    public function getId($padded = false)
    {
        return !$padded ? $this->id : str_pad((string)$this->id, 5, '0', STR_PAD_LEFT);
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
                while($this->id < self::MIN_ID) {
                    $this->id += self::MAX_ID;
                    $this->year -= $flip_year ? 1 : 0;
                }
            } elseif ($this->id > self::MAX_ID) {
                while($this->id > self::MAX_ID) {
                    $this->id -= self::MAX_ID;
                    $this->year += $flip_year ? 1 : 0;
                }
            }

            $this->year = (($this->year % 100) + 100) % 100;
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

        if (strlen($this->country_code) != 2 || !ctype_alpha($this->country_code)) {
            $this->is_valid = false;
        }

        if (strlen($this->issuer_code) != 3 || !ctype_alnum($this->issuer_code)) {
            $this->is_valid = false;
        }

        if ($this->year < 0 || $this->year > 99) {
            $this->is_valid = false;
        }

        if ($this->id == 0 && static::$ZERO_IDS_ALLOWED) {
            // great - do nothing here
        } elseif ($this->id < self::MIN_ID) {
            $this->is_valid = false;
        } elseif ($this->id > self::MAX_ID) {
            $this->is_valid = false;
        }
    }

    /**
     * @param  bool  $revalidate
     *
     * @return bool
     */
    public function isValid($revalidate = false): bool
    {
        if ($revalidate) $this->validate();

        return $this->is_valid;
    }
}