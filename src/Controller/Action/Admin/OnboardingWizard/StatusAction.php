<?php

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Controller\Action\Admin\OnboardingWizard;

use BitBag\SyliusMolliePlugin\Context\Admin\AdminUserContextInterface;
use BitBag\SyliusMolliePlugin\Entity\OnboardingWizardStatus;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Serializer;

final class StatusAction
{
    /** @var RepositoryInterface $statusRepository */
    private $statusRepository;

    /** @var AdminUserContextInterface $adminUserContext*/
    private $adminUserContext;

    public function __construct(RepositoryInterface $statusRepository, AdminUserContextInterface $adminUserContext)
    {
        $this->statusRepository = $statusRepository;
        $this->adminUserContext = $adminUserContext;
    }

    public function __invoke(): Response
    {
        $adminUser = $this->adminUserContext->getAdminUser();
        $onboardingWizardStatus = $this->statusRepository->findOneBy(['adminUser' => $adminUser]);

        if ($onboardingWizardStatus instanceof OnboardingWizardStatus) {
            return new JsonResponse(['completed' => $onboardingWizardStatus->isCompleted()]);
        }

        return new JsonResponse(['completed' => false]);
    }
}