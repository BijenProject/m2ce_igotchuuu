<?php
/**
 * This file is part of the Klarna Kp module
 *
 * (c) Klarna AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Plugin\Payment\Helper;

use Klarna\Kp\Api\PaymentMethodListInterface;
use Klarna\Kp\Model\Payment\Kp;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class DataPlugin
 *
 * @package Klarna\Kp\Plugin\Payment\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataPlugin
{
    /**
     * @var CartInterface
     */
    private $quote;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CartRepositoryInterface
     */
    private $mageQuoteRepository;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var PaymentMethodListInterface
     */
    private $paymentMethodList;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * DataPlugin constructor.
     *
     * @param RequestInterface           $request
     * @param OrderRepositoryInterface   $orderRepository
     * @param CartRepositoryInterface    $mageQuoteRepository
     * @param Session                    $session
     * @param ScopeConfigInterface       $config
     * @param PaymentMethodListInterface $paymentMethodList
     */
    public function __construct(
        RequestInterface $request,
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $mageQuoteRepository,
        Session $session,
        ScopeConfigInterface $config,
        PaymentMethodListInterface $paymentMethodList
    ) {
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        $this->mageQuoteRepository = $mageQuoteRepository;
        $this->session = $session;
        $this->config = $config;
        $this->paymentMethodList = $paymentMethodList;
    }

    /**
     * Modify results of getPaymentMethods() call to add in Klarna methods returned by API
     *
     * @param \Magento\Payment\Helper\Data $subject
     * @param                              $result
     * @return array
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function afterGetPaymentMethods(\Magento\Payment\Helper\Data $subject, $result)
    {
        if (!$this->config->isSetFlag('payment/' . Kp::METHOD_CODE . '/active')) {
            return $result;
        }
        $methods = $this->paymentMethodList->getKlarnaMethodCodes($this->getQuote());
        if (empty($methods)) {
            return $result;
        }
        foreach ($methods as $method) {
            $result[$method] = $result['klarna_kp'];
            $result[$method]['title'] = $this->paymentMethodList->getPaymentMethod($method)->getTitle();
        }
        return $result;
    }

    /**
     * @return CartInterface|\Magento\Quote\Model\Quote|null
     */
    private function getQuote()
    {
        if ($this->quote) {
            return $this->quote;
        }
        try {
            if ($order = $this->getOrder()) {
                $this->quote = $this->mageQuoteRepository->get($order->getQuoteId());
                return $this->quote;
            }
            $this->quote = $this->session->getQuote();
        } catch (NoSuchEntityException $e) {
            return null;
        }
        return $this->quote;
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface|bool
     */
    private function getOrder()
    {
        $id = $this->request->getParam('order_id');
        if (!$id) {
            return false;
        }
        try {
            return $this->orderRepository->get($id);
        } catch (LocalizedException $e) {
            return false;
        }
    }

    /**
     * Modify results of getMethodInstance() call to add in details about Klarna payment methods
     *
     * @param \Magento\Payment\Helper\Data $subject
     * @param callable                     $proceed
     * @param string                       $code
     * @return MethodInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function aroundGetMethodInstance(\Magento\Payment\Helper\Data $subject, callable $proceed, $code)
    {
        if (false === strpos($code, 'klarna_')) {
            return $proceed($code);
        }
        $methods = $this->paymentMethodList->getKlarnaMethodCodes();
        if (!in_array($code, $methods)) {
            return $proceed($code);
        }
        return $this->paymentMethodList->getPaymentMethod($code);
    }
}
