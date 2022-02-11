<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace spec\BitBag\SyliusMolliePlugin\Action;

use BitBag\SyliusMolliePlugin\Action\RefundAction;
use BitBag\SyliusMolliePlugin\Client\MollieApiClient;
use BitBag\SyliusMolliePlugin\Helper\ConvertRefundDataInterface;
use BitBag\SyliusMolliePlugin\Logger\MollieLoggerActionInterface;
use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Api\Resources\Payment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Request\Refund;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface;

final class RefundActionSpec extends ObjectBehavior
{
    function let(MollieLoggerActionInterface $loggerAction, ConvertRefundDataInterface $convertOrderRefundData): void
    {
        $this->beConstructedWith(
            $loggerAction,
            $convertOrderRefundData
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(RefundAction::class);
    }

    function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    function it_implements_api_aware_interface(): void
    {
        $this->shouldHaveType(ApiAwareInterface::class);
    }

    function it_implements_gateway_aware_interface(): void
    {
        $this->shouldHaveType(GatewayAwareInterface::class);
    }

    function it_executes_and_create_refund(
        Refund $request,
        ArrayObject $arrayObject,
        MollieLoggerActionInterface $loggerAction
    ): void {
        $request->getModel()->willReturn($arrayObject);
        $arrayObject->offsetGet('created_in_mollie')->willReturn(true);

        $this->execute($request);

        $loggerAction->addLog('Received refund created in Mollie dashboard')->shouldBeCalled();
    }

    function it_executes_and_refund_action_with_payment_id(
        Refund $request,
        MollieApiClient $mollieApiClient,
        ArrayObject $arrayObject,
        MollieLoggerActionInterface $loggerAction,
        PaymentInterface $payment,
        Payment $molliePayment,
        PaymentEndpoint $paymentEndpoint,
        ConvertRefundDataInterface $convertOrderRefundData
    ): void {
        $this->setApi($mollieApiClient);
        $request->getModel()->willReturn($arrayObject);
        $request->getFirstModel()->willReturn($payment
        );
        $mollieApiClient->payments = $paymentEndpoint;
        $payment->getCurrencyCode()->willReturn('EUR');

        $arrayObject->offsetGet('payment_mollie_id')->willReturn(3);
        $arrayObject->offsetGet('metadata')->willReturn(['refund' => ['test_refund']]);
        $paymentEndpoint->get(3,[])->willReturn($molliePayment);
        $convertOrderRefundData->convert(['test_refund'],'EUR')->willReturn(['5']);


        $molliePayment->amountRemaining = 5;
        $molliePayment->canBeRefunded()->willReturn(true);
        $molliePayment->refund(['amount' => ['5']])->shouldBeCalled();
        $loggerAction->addLog('Refund action with payment id 3')->shouldBeCalled();
        $this->execute($request);
    }

    function it_supports_only_refund_request_and_array_access(
        Refund $request,
        \ArrayAccess $arrayAccess
    ): void {
        $request->getModel()->willReturn($arrayAccess);

        $this->supports($request)->shouldReturn(true);
    }
}
