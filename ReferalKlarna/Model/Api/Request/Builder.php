<?php

namespace Luxinten\ReferalKlarna\Model\Api\Request;

use Klarna\Kp\Api\Data\RequestInterface;
use Klarna\Kp\Model\Api\RequestFactory;

class Builder extends \Klarna\Kp\Model\Api\Request\Builder
{
    protected $checkoutSession;
    private $order_amount = 0;
    private $requestFactory;

    public function __construct(
        RequestFactory $requestFactory,
        \Magento\Checkout\Model\Session $checkoutSession

    ) {
        $this->requestFactory = $requestFactory;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->requestFactory->create([
            'data' => [
                'order_amount'        => $this->order_amount

            ]
        ]);
    }


    public function validate($requiredAttributes, $type)
    {
        $missingAttributes = [];
        foreach ($requiredAttributes as $requiredAttribute) {
            if (null === $this->$requiredAttribute) {
                $missingAttributes[] = $requiredAttribute;
            }
            if (is_array($this->$requiredAttribute) && count($this->$requiredAttribute) === 0) {
                $missingAttributes[] = $requiredAttribute;
            }
        }
        if (!empty($missingAttributes)) {
            throw new KlarnaApiException(
                __(
                    'Missing required attribute(s) on %1: "%2".',
                    $type,
                    implode(', ', $missingAttributes)
                )
            );
        }
        $total = 0;

        $quote = $this->checkoutSession->getQuote();

//        foreach ($this->orderlines as $orderLine) {
//            $total += (int)$orderLine->getTotal();
//        }
        $total = $quote->getGrandTotal();
        $total = intval(str_replace('.', '', $total));
//
//        file_put_contents('/var/www/html/thedagroup/var/log/test.log', "data1: ". json_encode($total) . "\n", FILE_APPEND | LOCK_EX);
//        file_put_contents('/var/www/html/thedagroup/var/log/test.log', "data2: ". json_encode($this->order_amount) . "\n", FILE_APPEND | LOCK_EX);
        if ($total  !== $this->order_amount) {
            throw new KlarnaApiException(
                __('Order line totals do not total order_amount - %1 != %2', $total, $this->order_amount)
            );
        }

        return $this;
    }
}
