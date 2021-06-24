<?php


namespace DanielZ\MusicCodes;


class LabelCode
{
    const PATTERN = '/^([A-Z0-9]*)([A-Z])-?([0-9]+)$/';

    /**
     * Label code prefix
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * Number of digits
     *
     * @var int
     */
    protected $digits = 0;

    /**
     * Maximum ID
     *
     * @var int
     */
    protected $max_id = 0;

    /**
     * Label code id
     *
     * @var int
     */
    protected $id = 0;

    /**
     * @var bool
     */
    protected $is_valid = false;

    /**
     * LabelCode constructor.
     *
     * @param bool $label_code
     */
    public function __construct($label_code = false)
    {
        if (is_string($label_code)) {
            $this->load($label_code);
        }
    }

    /**
     * @param string $label_code
     * @return LabelCode
     */
    public function load($label_code)
    {
        $clean_code = strtoupper(trim($label_code));
        $matches = $this->getMatches($clean_code);

        if (empty($matches)) {
            $this->is_valid = false;
            $this->prefix = '';
            $this->id = -1;
        } else {
            $this->prefix = $matches[1] . $matches[2];
            $this->digits = strlen($matches[3]);
            $this->max_id = pow(10, $this->digits);
            $this->id = (int)ltrim($matches[3], '0');

            $this->validate();
        }

        return $this;
    }

    /**
     * @param string $label_code
     * @return array
     */
    protected function getMatches($label_code)
    {
        preg_match(self::PATTERN, $label_code, $matches);

        return $matches;
    }

    /**
     * @param string $prefix
     * @param string $id
     * @return LabelCode
     */
    public static function fromParts($prefix, $id)
    {
        return new static(join('', [rtrim($prefix, '-'), $id]));
    }

    /**
     * @param int $max
     * @return bool
     */
    public function previous($max = 0)
    {
        if ($max <= 0) {
            $max = $this->max_id;
        }

        $this->setId(min($max, $this->id - 1));

        return $this->is_valid;
    }

    /**
     * @param int $min
     * @return bool
     */
    public function next($min = 0)
    {
        $this->setId(max($min, $this->id + 1));

        return $this->is_valid;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLabelCode(false);
    }

    /**
     * @return string
     */
    public function getLabelCode($dashed = false)
    {
        $parts = [
            $this->getPrefix(),
            $this->getId(true),
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
     * @return LabelCode
     */
    public function setPrefix(string $prefix): LabelCode
    {
        $this->prefix = strtoupper($prefix);

        $this->validate();

        return $this;
    }

    /**
     * @param bool $padded
     * @return int|string
     */
    public function getId($padded = false)
    {
        return !$padded ? $this->id : str_pad((string)$this->id, $this->digits, '0', STR_PAD_LEFT);
    }

    /**
     * @param int|string $id
     * @return LabelCode
     */
    public function setId($id): LabelCode
    {
        $this->id = (int)$id;

        $this->validate();

        return $this;
    }

    /**
     *
     */
    protected function validate()
    {
        $this->is_valid = true;

        if (strlen($this->prefix) == 0) {
            $this->is_valid = false;
        }

        if ($this->id <= 0) {
            $this->is_valid = false;
        } elseif ($this->id >= $this->max_id) {
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