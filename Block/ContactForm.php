<?php

namespace Magepow\Customform\Block;

use Magento\Framework\View\Element\Template;

class ContactForm extends \Magento\Contact\Block\ContactForm
{

    public function __construct(
            Template\Context $context,
            \Magento\Directory\Block\Data $directoryBlock, 
            array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->directoryBlock = $directoryBlock;
    }

    public function getCountries()
    {
        $country = $this->directoryBlock->getCountryHtmlSelect();
        return $country;
    }
    public function getRegion()
    {
        $region = $this->directoryBlock->getRegionHtmlSelect();
        return $region;
    }
}