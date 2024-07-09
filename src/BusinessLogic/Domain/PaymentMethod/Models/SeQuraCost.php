<?php

namespace SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models;

/**
 * Class SeQuraCost
 *
 * @package SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models
 */
class SeQuraCost
{
    /**
     * @var int Setup fee to pay the order.
     */
    protected $setupFee;

    /**
     * @var int Installment fee to pay the order.
     */
    protected $instalmentFee;

    /**
     * @var int Down payment fee to pay the order.
     */
    protected $downPaymentFees;

    /**
     * @var int Total amount for each instalment to pay the order.
     */
    protected $instalmentTotal;

    /**
     * @param int $setupFee
     * @param int $instalmentFee
     * @param int $downPaymentFees
     * @param int $instalmentTotal
     */
    public function __construct(int $setupFee, int $instalmentFee, int $downPaymentFees, int $instalmentTotal)
    {
        $this->setupFee = $setupFee;
        $this->instalmentFee = $instalmentFee;
        $this->downPaymentFees = $downPaymentFees;
        $this->instalmentTotal = $instalmentTotal;
    }

    /**
     * @return int
     */
    public function getSetupFee(): int
    {
        return $this->setupFee;
    }

    /**
     * @param int $setupFee
     */
    public function setSetupFee(int $setupFee): void
    {
        $this->setupFee = $setupFee;
    }

    /**
     * @return int
     */
    public function getInstalmentFee(): int
    {
        return $this->instalmentFee;
    }

    /**
     * @param int $instalmentFee
     */
    public function setInstalmentFee(int $instalmentFee): void
    {
        $this->instalmentFee = $instalmentFee;
    }

    /**
     * @return int
     */
    public function getDownPaymentFees(): int
    {
        return $this->downPaymentFees;
    }

    /**
     * @param int $downPaymentFees
     */
    public function setDownPaymentFees(int $downPaymentFees): void
    {
        $this->downPaymentFees = $downPaymentFees;
    }

    /**
     * @return int
     */
    public function getInstalmentTotal(): int
    {
        return $this->instalmentTotal;
    }

    /**
     * @param int $instalmentTotal
     */
    public function setInstalmentTotal(int $instalmentTotal): void
    {
        $this->instalmentTotal = $instalmentTotal;
    }
}
