<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models;

/**
 * Class SeQuraForm
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models
 */
class SeQuraForm
{
    /**
     * @var string HTML form from SeQura.
     */
    protected $form;

    /**
     * @param string $form
     */
    public function __construct(string $form)
    {
        $this->form = $form;
    }

    /**
     * @return string
     */
    public function getForm(): string
    {
        return $this->form;
    }

    /**
     * @param string $form
     */
    public function setForm(string $form): void
    {
        $this->form = $form;
    }
}
