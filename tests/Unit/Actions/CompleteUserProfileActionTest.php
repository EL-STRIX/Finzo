<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use App\Application\Actions\CompleteUserProfileAction;
use App\Application\DTOs\KycRequestDTO;
use App\Domain\Contracts\Services\UserServiceInterface;
use App\Domain\Contracts\Security\SecurityServiceInterface;
use App\Shared\Core\Exceptions\ApiException;

class CompleteUserProfileActionTest extends TestCase
{
    private $userServiceMock;
    private $securityMock;
    private CompleteUserProfileAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userServiceMock = $this->createMock(UserServiceInterface::class);
        $this->securityMock = $this->createMock(SecurityServiceInterface::class);
        $this->action = new CompleteUserProfileAction($this->userServiceMock, $this->securityMock);
    }

    public function testValidationFailureThrowsApiException(): void
    {
        $this->userServiceMock->method('getCustomerId')->willReturn('CUST123456');
        $this->userServiceMock->method('getUserFiles')->willReturn([
            'profile_picture' => null,
            'id_front_path' => null,
            'id_back_path' => null
        ]);
        $this->userServiceMock->method('validateKyc')->willReturn(['dob' => 'Date of birth is required']);

        $this->securityMock->method('cleanInput')->willReturnArgument(0);

        $dto = new KycRequestDTO(1, ['dob' => ''], []);

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(422);

        $this->action->execute($dto);
    }
}
