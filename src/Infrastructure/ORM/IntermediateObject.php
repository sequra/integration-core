<?php

namespace SeQura\Core\Infrastructure\ORM;

/**
 * Class IntermediateObject
 *
 * @package SeQura\Core\Infrastructure\ORM
 */
class IntermediateObject
{
    /**
     * @var string
     */
    protected $index1;
    /**
     * @var string
     */
    protected $index2;
    /**
     * @var string
     */
    protected $index3;
    /**
     * @var string
     */
    protected $index4;
    /**
     * @var string
     */
    protected $index5;
    /**
     * @var string
     */
    protected $index6;
    /**
     * @var string
     */
    protected $data;
    /**
     * @var array<string,mixed>
     */
    protected $otherIndexes = array();

    /**
     * @return string|null
     */
    public function getIndex1(): ?string
    {
        return $this->index1;
    }

    /**
     * @param string $index1
     */
    public function setIndex1(string $index1): void
    {
        $this->index1 = $index1;
    }

    /**
     * @return string|null
     */
    public function getIndex2(): ?string
    {
        return $this->index2;
    }

    /**
     * @param string $index2
     */
    public function setIndex2(string $index2): void
    {
        $this->index2 = $index2;
    }

    /**
     * @return string|null
     */
    public function getIndex3(): ?string
    {
        return $this->index3;
    }

    /**
     * @param string $index3
     */
    public function setIndex3(string $index3): void
    {
        $this->index3 = $index3;
    }

    /**
     * @return string|null
     */
    public function getIndex4(): ?string
    {
        return $this->index4;
    }

    /**
     * @param string $index4
     */
    public function setIndex4(string $index4): void
    {
        $this->index4 = $index4;
    }

    /**
     * @return string|null
     */
    public function getIndex5(): ?string
    {
        return $this->index5;
    }

    /**
     * @param string $index5
     */
    public function setIndex5(string $index5): void
    {
        $this->index5 = $index5;
    }

    /**
     * @return string|null
     */
    public function getIndex6(): ?string
    {
        return $this->index6;
    }

    /**
     * @param string $index6
     */
    public function setIndex6(string $index6): void
    {
        $this->index6 = $index6;
    }

    /**
     * @return string|null
     */
    public function getData(): ?string
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData(string $data): void
    {
        $this->data = $data;
    }

    /**
     * Sets index value
     *
     * @param mixed $index
     * @param mixed $value
     */
    public function setIndexValue($index, $value): void
    {
        if (!is_int($index) || $index < 1 || !is_string($value)) {
            return;
        }

        if ($index <= 6) {
            $methodName = 'setIndex' . $index;
            $this->$methodName($value);
        } else {
            $this->otherIndexes['index_' . $index] = $value;
        }
    }

    /**
     * Returns index value
     *
     * @param mixed $index
     *
     * @return string|null
     */
    public function getIndexValue($index): ?string
    {
        $value = null;
        if (!is_int($index) || $index < 1) {
            return $value;
        }

        if ($index <= 6) {
            $methodName = 'getIndex' . $index;
            $value = $this->$methodName();
        } elseif (array_key_exists('index_' . $index, $this->otherIndexes)) {
            $value = $this->otherIndexes['index_' . $index];
        }

        return $value;
    }
}
