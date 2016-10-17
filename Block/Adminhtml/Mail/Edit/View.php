<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Shockwavemk\Mail\Base\Block\Adminhtml\Mail\Edit;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\Address\Mapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Adminhtml customer view personal information sales block.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View extends \Magento\Backend\Block\Widget\Form\Container
{
    /** @var \Shockwavemk\Mail\Base\Model\Mail _mail */
    protected $_mail;

    /**
     * @param \Magento\Backend\Block\Template\Context|\Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $manager
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\ObjectManagerInterface $manager,
        array $data = []
    ) {
        $this->_blockGroup = 'Shockwavemk_Mail_Base';
        $this->_controller = 'adminhtml_mail';
        $this->_mode = 'edit';
        $this->_request = $context->getRequest();
        $mailId = $this->_request->getParam('id');
        $this->_mail = $manager->get('\Shockwavemk\Mail\Base\Model\Mail');
        $this->_mail->load($mailId);

        parent::__construct($context, $data);
    }

    /**
     * Prepare the layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->buttonList->remove('save');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');

        $this->buttonList->add(
            'send',
            [
                'label' => __('Send mail ...'),
                'onclick' => "setLocation('{$this->getUrl('*/*/send', 
                    ['id' => $this->_mail->getId()]
                    )}')",
                'class' => 'task'
            ]
        );

        $this->buttonList->add(
            'send_post',
            [
                'label' => __('Resend mail'),
                'onclick' => "setLocation('{$this->getUrl('*/*/sendPost', 
                [
                    'id' => $this->_mail->getId()
                ])}')",
                'class' => 'task'
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        if(!empty($this->_mail->getCustomerId())) {
            return $this->getUrl('customer/index/edit', array('id' => $this->_mail->getCustomerId()));
        }

        return $this->getUrl('index/index');
    }

    public function getMail()
    {
        return $this->_mail;
    }

    public function getUnstructuredData()
    {
        return
            "<h3>Variables</h3>{$this->getVarsOutput()}" .
            "<h3>Recipient variables</h3>{$this->getRecipientVariablesOutput()}" .
            "<h3>Options</h3>{$this->getOptionsOutput()}";
    }

    /**
     * TODO
     *
     * @param $key
     * @param $value
     * @return array
     */
    public function getCustomerRepresentation($key, $value)
    {
        /** @var \Magento\Customer\Model\Customer $value */
        $url = $this->getUrl('customer/index/edit', array('id' => $value->getId()));
        return "{$key} (customer): <a href='{$url}' target='_blank'>{$value->getFirstname()} {$value->getLastname()}</a>";
    }

    /**
     * TODO
     *
     * @param $key
     * @param $value
     * @return array
     */
    public function getOrderRepresentation($key, $value)
    {
        /** @var \Magento\Customer\Model\Customer $value */
        $url = $this->getUrl('sales/order/view', array('order_id' => $value->getId()));
        return "{$key} (order): <a href='{$url}' target='_blank'>{$value->getIncrementId()}</a>";
    }

    /**
     * @param $key
     * @param $value
     * @return string
     */
    public function getLinkRepresentation($key, $value)
    {
        return "{$key} (link): <a href='{$value}' target='_blank'>{$value}</a>";
    }

    /**
     * @param $key
     * @param $value
     * @return string
     */
    public function getStoreRepresentation($key, $value)
    {
        /** @var \Magento\Store\Model\Store $value */
        return "{$key} (store): {$value->getName()}";
    }

    /**
     * TODO
     *
     * @param $key
     * @param $value
     * @return array
     */
    public function getShipmentRepresentation($key, $value)
    {
        /** @var \Magento\Customer\Model\Customer $value */
        $url = $this->getUrl('sales/shipment/view', array('shipment_id' => $value->getId()));
        return "{$key} (shipment): <a href='{$url}' target='_blank'>{$value->getIncrementId()}</a>";
    }

    /**
     * TODO
     *
     * @param $key
     * @param $value
     * @return array
     */
    public function getInvoiceRepresentation($key, $value)
    {
        /** @var \Magento\Customer\Model\Customer $value */
        $url = $this->getUrl('sales/invoice/view', array('invoice_id' => $value->getId()));
        return "{$key} (invoice): <a href='{$url}' target='_blank'>{$value->getIncrementId()}</a>";
    }

    /**
     * @param $key
     * @param $variable
     * @return string
     */
    public function getVarOutput($key, $variable)
    {
        try
        {
            /** @var \Magento\Framework\Model\AbstractModel $variable */
            if (is_subclass_of($variable, '\Magento\Framework\Model\AbstractModel') && $variable->getEntityType() == 'customer') {
                return $this->getCustomerRepresentation($key, $variable);

            } elseif (is_subclass_of($variable, '\Magento\Framework\Model\AbstractModel') && $variable->getEntityType() == 'order') {
                return $this->getOrderRepresentation($key, $variable);

            } elseif (is_subclass_of($variable, '\Magento\Framework\Model\AbstractModel') && $variable->getEntityType() == 'shipment') {
                return $this->getShipmentRepresentation($key, $variable);

            } elseif (is_subclass_of($variable, '\Magento\Framework\Model\AbstractModel') && $variable->getEntityType() == 'invoice') {
                return $this->getInvoiceRepresentation($key, $variable);

            } elseif (is_subclass_of($variable, 'Magento\Framework\Model\AbstractModel')) {
                return $key . ' : ' . json_encode($variable->getData(), JSON_PRETTY_PRINT);

            } elseif (is_subclass_of($variable, '\Magento\Eav\Model\Entity\Collection\AbstractCollection')) {
                /** @var \Magento\Eav\Model\Entity\Collection\AbstractCollection $variable*/
                return $key . ' : ' . json_encode($variable->getData(), JSON_PRETTY_PRINT);

            } elseif (!filter_var($variable, FILTER_VALIDATE_URL) === false) {
                return $this->getLinkRepresentation($key, $variable);

            } elseif (is_string($variable)) {
                return $key . ' : ' . $variable;
            }
            else
            {
                return $key . ' : ' . json_encode($variable, JSON_PRETTY_PRINT);
            }
        }
        catch (\Exception $e)
        {
            $variable = 'variable can not be displayed';
        }

        return $key . ' : ' . $variable;
    }

    /**
     * @return string
     */
    public function getVarsOutput()
    {
        $output = [];
        $vars = $this->_mail->getVars();
        if (!empty($vars)) {
            foreach ($vars as $key => $value) {
                $output[] = $this->getVarOutput($key, $value);
            }
        }
        return implode(',<br>', $output);
    }

    /**
     * @return string
     */
    public function getRecipientVariablesOutput()
    {
        $output = [];
        $recipientVariables = $this->_mail->getRecipientVariables();
        if (!empty($recipientVariables)) {
            $output[] = $recipientVariables;
        }
        return implode(',<br>', $output);
    }

    /**
     * @return string
     */
    public function getOptionsOutput()
    {
        $output = [];
        $options = $this->_mail->getOptions();
        if (!empty($options)) {
            foreach ($options as $key => $value) {
                $output[] = $this->getVarOutput($key, $value);
            }
        }
        return implode(',<br>', $output);
    }
}
