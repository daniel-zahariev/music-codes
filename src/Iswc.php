<?php


namespace DanielZ\MusicCodes;


class Iswc
{
    const NO_DASH_PATTERN = '/^(T)([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{1})$/';
    const DASHED_PATTERN = '/^(T)-([0-9]{3}).([0-9]{3}).([0-9]{3})-([0-9]{1})$/';
    const MIN_ID = 1;
    const MAX_ID = 999999999;

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * @var string
     */
    protected $id = '';

    /**
     * @var string
     */
    protected $check_digit = '';

    /**
     * @var bool
     */
    protected $is_valid = false;

    /**
     * Iswc constructor.
     * @param string $iswc
     */
    public function __construct($iswc = false)
    {
        if (is_string($iswc)) {
            $this->load($iswc);
        }
    }

    /**
     * @param string $iswc
     * @return Iswc
     */
    public function load($iswc)
    {
        $clean_isrc = strtoupper(trim($iswc));
        $matches = $this->getMatches($clean_isrc);

        if (empty($matches)) {
            $this->is_valid = false;
        } else {
            $this->prefix = $matches[1];
            $this->id = "{$matches[2]}{$matches[3]}{$matches[4]}";
            $this->check_digit = $matches[5];

            $this->validate();
        }

        return $this;
    }

    /**
     * @param string $iswc
     * @return array
     */
    protected function getMatches($iswc)
    {
        preg_match(self::NO_DASH_PATTERN, $iswc, $matches);

        if (empty($matches)) {
            preg_match(self::DASHED_PATTERN, $iswc, $matches);
        }

        return $matches;
    }

    /**
     * @param string $country_code
     * @param string $issuer_code
     * @param string $year
     * @param string $id
     * @return Iswc
     */
    public static function fromId($id)
    {

        $id = str_pad(str_replace('.', '', (string)$id), 9, '0', STR_PAD_LEFT);
        return new static("T{$id}" . self::calculateCheckDigit($id));
    }

    /**
     * @param int $max
     * @return bool
     */
    public function previous($max = self::MAX_ID)
    {
        $this->setId(min($max, (int)$this->id - 1));

        return $this->is_valid;
    }

    /**
     * @param int $min
     * @return bool
     */
    public function next($min = self::MIN_ID)
    {
        $this->setId(max($min, (int)$this->id + 1));

        return $this->is_valid;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getIswc(true);
    }

    /**
     * @param bool $dashed
     * @return string
     */
    public function getIswc($dashed = true)
    {
        $parts = [
            $this->getPrefix(),
            $this->getId($dashed),
            $this->getCheckDigit(),
        ];

        return join($dashed ? '-' : '', $parts);
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     * @return Iswc
     */
    public function setPrefix(string $prefix): Iswc
    {
        $this->prefix = strtoupper($prefix);

        $this->validate();

        return $this;
    }

    /**
     * @return string
     */
    public function getCheckDigit(): string
    {
        return $this->check_digit;
    }

    /**
     * @param bool $dotted
     * @return string
     */
    public function getId($dotted = false): string
    {
        return $dotted ? join('.', str_split($this->id, 3)) : $this->id;
    }

    /**
     * @param int|string $id
     * @return Iswc
     */
    public function setId($id): Iswc
    {
        $id = (int)$id;
        if ($id < self::MIN_ID || $id > self::MAX_ID) {
            $this->id = '';
        } else {
            $this->id = str_pad((string)$id, 9, '0', STR_PAD_LEFT);
            $this->check_digit = self::calculateCheckDigit($this->id);
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

        if($this->prefix != 'T' || strlen($this->id) != 9 || strlen($this->check_digit) != 1) {
            $this->is_valid = false;
        } else {
            $this->is_valid = $this->check_digit === self::calculateCheckDigit($this->id);
        }
    }

    protected static function calculateCheckDigit($id)
    {
        $check_sum = 1;
        foreach(range(1, 9) as $idx) {
            $check_sum += $idx * $id[$idx - 1];
        }

        return (string)((10 - ($check_sum % 10)) % 10);
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->is_valid;
    }

}