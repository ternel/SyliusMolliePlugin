<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Validator\Constraints;

use BitBag\SyliusMolliePlugin\Factory\MollieSubscriptionGatewayFactory;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class CurrencyValidator extends ConstraintValidator
{
    public function validate($paymentMethod, Constraint $constraint): void
    {
        Assert::isInstanceOf($paymentMethod, PaymentMethodInterface::class);

        Assert::isInstanceOf($constraint, Currency::class);

        $gatewayConfig = $paymentMethod->getGatewayConfig();

        if (
            null === $gatewayConfig ||
            (
                $gatewayConfig->getFactoryName() !== MollieSubscriptionGatewayFactory::FACTORY_NAME
            )
        ) {
            return;
        }

        /** @var ChannelInterface $channel */
        foreach ($paymentMethod->getChannels() as $channel) {
            if (
                null === $channel->getBaseCurrency() ||
                false === in_array(strtoupper($channel->getBaseCurrency()->getCode()), MollieSubscriptionGatewayFactory::CURRENCIES_AVAILABLE)
            ) {
                $message = $constraint->message ?? null;

                $this->context->buildViolation($message, [
                    '{{ currencies }}' => implode(', ', MollieSubscriptionGatewayFactory::CURRENCIES_AVAILABLE),
                ])->atPath('channels')->addViolation();

                return;
            }
        }
    }
}
